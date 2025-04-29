<?php

/**
 * 断点上传本地存储
 */
namespace ZxComponent\BPUploader;

class BPToLocal
{

    /**
     * 格式化chunkDir
     * @param $chunkDir
     * @return string
     * @throws BPUploaderException
     */
    public function formatDir($dir)
    {
        if(!$dir)
            throw new BPUploaderException(BPUploaderException::parameters_illegal,"未设置储临时目录");
        return $this -> ensureTrailingSlash($dir);
    }


    /**
     * 路径末尾补斜杠
     * @param $path
     * @return string
     */
    private function ensureTrailingSlash($path) {

        if (empty($path)) {
            // 如果路径为空，根据需要返回根目录或其他默认值
            return DIRECTORY_SEPARATOR;
        }

        // 移除路径末尾的所有斜杠或反斜杠
        $path = rtrim($path, '\\/');

        // 添加正确的目录分隔符
        $path .= DIRECTORY_SEPARATOR;

        return $path;
    }


    /**
     * 检查唯一标识
     * @param $identifier
     * @return void
     * @throws BPUploaderException
     */
    public function checkIdentifier($identifier)
    {

        if (!$identifier){
            throw new BPUploaderException(BPUploaderException::file_identifier_is_missing);
        }

        // 判断标识符
        if (!preg_match('/^[a-f0-9]{32}$/i', $identifier)){
            throw new BPUploaderException(BPUploaderException::file_invalid_identifier);
        }

    }


    /**
     * 创建文件路径
     * @param $dir
     * @return bool
     */
    public function mkDirPath($dir)
    {

        $list = explode(DIRECTORY_SEPARATOR,$dir);

        // 循环创建目录
        $path = '';
        foreach($list as $folder)
        {
            if(!$folder) continue;
            $path .= DIRECTORY_SEPARATOR.$folder;

            // 判断目录是否存在
            if(!is_dir($path))
            {
                if(!mkdir($path))
                {
                    throw new BPUploaderException(BPUploaderException::failed_to_create_the_folder);
                }
            }
        }

        return true;
    }


    /**
     * 合并成功后，删除整个临时目录
     * 注意：这是一个递归删除函数，请谨慎使用
     * @param $dirPath
     * @return void
     */
    public function deleteDir($dirPath) {
        if (!is_dir($dirPath)) {
            return;
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this -> deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }


}