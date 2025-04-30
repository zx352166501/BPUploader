# 断点续传

## 上传前检查
```// 检查唯一标识
    $breakPointObj = new BPUploaderLocal();
    $breakPointObj ->chunkDir = __DIR__. '/../upload';
    $breakPointObj ->check($identifier);
```

## 上传文件
```
    // 文件准备
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
    $breakPointObj ->upload($identifier,$chunkNumber,$testFile);
```

## 文件合并
```
    // 检查唯一标识
    $breakPointObj = new BPUploaderLocal();
    $breakPointObj ->chunkDir = __DIR__. '/../upload';
    $breakPointObj ->finalFileDir = __DIR__. '/../newUpload';
    $breakPointObj ->merge($identifier,$newFileName,$totalChunks);
```

## 备注
- identifier 文件32位唯一标识
- chunkNumber 当前文件块序号
- chunkDir 为临时切块存储路径
- finalFileDir 为最终目录存储地址
- check 为检查方法，返回已经存储的块序号烈豪
- upload 为上传方法，返回成功块编号
- merge 为合并方法，返回最终文件的绝对路径