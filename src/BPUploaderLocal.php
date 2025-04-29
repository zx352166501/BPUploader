<?php

namespace ZxComponent\BPUploader;

class BPUploaderLocal
{

    // 块存储临时目录
    public string $chunkDir;

    // 文件存储目录
    public string $finalFileDir;


    /**
     * 文件检查
     * @param $identifier
     * @return array[]
     * @throws BPUploaderException
     */
    public function check($identifier)
    {

        // 检查唯一标识
        $BPToLocalObj = new BPToLocal();
        // 校验文件id
        $BPToLocalObj ->checkIdentifier($identifier);
        // 设置块临时存储目录
        $tempChunkDir = $BPToLocalObj->formatDir($this -> chunkDir);
        $BPToLocalObj ->mkDirPath($tempChunkDir);

        $uploadedChunks = [];
        if (is_dir($tempChunkDir)) {
            // 扫描目录，获取所有块文件（文件名即块编号）
            $files = scandir($tempChunkDir);
            foreach ($files as $file) {
                // 确保是数字文件名且大于0
                if (is_numeric($file) && intval($file) > 0) {
                    $uploadedChunks[] = intval($file);
                }
            }
            sort($uploadedChunks); // 排序
        }

        return ['uploadedChunks' => $uploadedChunks];

    }


    /**
     * 文件上传
     * @param $identifier
     * @param $chunkNumber
     * @param $file
     * @return array
     * @throws BPUploaderException
     */
    public function upload($identifier, $chunkNumber, $uploadedFile)
    {

        if($chunkNumber <= 0)
            throw new BPUploaderException(BPUploaderException::parameters_illegal,'参数非法，文件块编号异常');

        // 存储至本地
        $BPToLocalObj = new BPToLocal();
        // 校验文件id
        $BPToLocalObj ->checkIdentifier($identifier);

        // 多个upload上传时候，如果是共享盘，因网络等影响，mkdir会报错，因此放入检查check中创建。
        $tempChunkDir = $BPToLocalObj->formatDir($this -> chunkDir);
        if (!is_dir($tempChunkDir))
            throw new BPUploaderException(BPUploaderException::temporary_directory_not_exist);

        // --- 块文件路径 ---
        $chunkFilePath = $tempChunkDir.$chunkNumber; // 块文件直接用编号命名

        // --- 移动上传的块文件 ---
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK)
            throw new BPUploaderException(BPUploaderException::file_upload_error,
                '文件上传错误:'. $uploadedFile['error']);

        // 由于是抽离的逻辑，move_uploaded_file只能用于通过HTTP POST上传的文件，因此改为rename
        if (!rename($uploadedFile['tmp_name'], $chunkFilePath)) {
            throw new BPUploaderException(BPUploaderException::file_upload_error,
                '文件上传错误: 请检查权限或路径，无法移动块{'.$chunkNumber.'}');
        }

        return ['chunkNumber'=> $chunkNumber];

    }


    /**
     * 文件合并
     * @param $identifier
     * @param $newFileName
     * @param $totalChunks
     * @return string
     * @throws BPUploaderException
     */
    public function merge($identifier,$newFileName,$totalChunks)
    {

        if(!$newFileName)
            throw new BPUploaderException(BPUploaderException::parameters_illegal,'参数非法，文件名不能为空');
        if($totalChunks <= 0)
            throw new BPUploaderException(BPUploaderException::parameters_illegal,'参数非法，文件总块数非法');

        // 存储至本地
        $BPToLocalObj = new BPToLocal();
        // 校验文件id
        $BPToLocalObj ->checkIdentifier($identifier);

        // 多个upload上传时候，如果是共享盘，因网络等影响，mkdir会报错，因此放入检查check中创建。
        $tempChunkDir = $BPToLocalObj->formatDir($this -> chunkDir);
        if (!is_dir($tempChunkDir))
            throw new BPUploaderException(BPUploaderException::temporary_directory_not_exist);

        // --- 检查所有块是否存在 ---
        $allChunksExist = true;
        for ($i = 1; $i <= $totalChunks; $i++) {
            if (!file_exists($tempChunkDir.$i)) {
                $allChunksExist = false;
                break;
            }
        }

        if (!$allChunksExist)
            throw new BPUploaderException(BPUploaderException::file_missing,
                '块文件不完整，无法合并 (缺少块 {'.$i.'})');

        // 最终文件存储目录
        $finalFileDir = $BPToLocalObj->formatDir($this -> finalFileDir);

        // 创建文件夹
        $BPToLocalObj ->mkDirPath($finalFileDir);

        // 最终路径
        $finalFilePath = $finalFileDir . $newFileName;

        // --- 合并文件 ---
        $finalFile = @fopen($finalFilePath, 'wb'); // 以二进制写入模式打开目标文件
        if (!$finalFile)
            throw new BPUploaderException(BPUploaderException::file_failed_to_open,
            '无法创建或打开最终文件');

        $mergeSuccess = true;
        for ($i = 1; $i <= $totalChunks; $i++) {
            $chunkFilePath = $tempChunkDir.$i;
            $chunkFile = @fopen($chunkFilePath, 'rb'); // 以二进制读取模式打开块文件
            if (!$chunkFile) {
                $mergeSuccess = false;
                break;
            }
            // 将块内容写入最终文件
            while ($buffer = fread($chunkFile, 4096)) { // 每次读取 4KB
                fwrite($finalFile, $buffer);
            }
            fclose($chunkFile);

        }

        fclose($finalFile);

        // --- 清理临时目录 ---
        if (!$mergeSuccess)
            throw new BPUploaderException(BPUploaderException::file_failed_to_open,
                '合并失败，无法读取块 {'.$i.'}');

        // 删除临时目录
        $BPToLocalObj -> deleteDir($tempChunkDir);

        return $finalFilePath;

    }


}