<?php

use PHPUnit\Framework\TestCase;
use ZxComponent\BPUploader\BPUploaderLocal;

class BreakPointsTest extends TestCase
{

    /**
     * 检查块数据
     * @return void
     * @throws \ZxComponent\BPUploader\BPUploaderException
     */
    public function testCheckChunk()
    {

        // 检查唯一标识
        $breakPointObj = new BPUploaderLocal();
        $breakPointObj ->chunkDir = __DIR__. '/../upload';
        $chunks = $breakPointObj ->check(md5("asd"));
        $this->assertIsArray($chunks);
        $this->assertArrayHasKey('uploadedChunks', $chunks);

    }


    /**
     * 上传
     * @return void
     * @throws \ZxComponent\BPUploader\BPUploaderException
     */
    public function testUpload()
    {

        $testFile = [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => __DIR__ . '/test.txt',
            'error' => UPLOAD_ERR_OK,
            'size' => 100,
        ];

        // 创建临时文件
        file_put_contents($testFile['tmp_name'], 'This is a test file.');

        // 检查唯一标识
        $breakPointObj = new BPUploaderLocal();
        $breakPointObj ->chunkDir = __DIR__. '/../upload';
        $result = $breakPointObj ->upload(md5("asd"),1,$testFile);
        $this->assertNotFalse($result);

    }


    /**
     * 合并
     * @return void
     * @throws \ZxComponent\BPUploader\BPUploaderException
     */
    public function testMerge()
    {

        // 检查唯一标识
        $breakPointObj = new BPUploaderLocal();
        $breakPointObj ->chunkDir = __DIR__. '/../upload';
        $breakPointObj ->finalFileDir = __DIR__. '/../newUpload';
        $result = $breakPointObj ->merge(md5("asd"),"newtest.txt",1);
        $this->assertNotFalse($result);

    }



}