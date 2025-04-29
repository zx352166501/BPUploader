<?php

namespace ZxComponent\BPUploader;

class BPUploaderException extends \Exception
{

    // 定义错误码常量
    public const file_identifier_is_missing = 1001;
    public const file_invalid_identifier = 1002;
    public const target_file_not_exist = 1003;
    public const failed_to_create_the_folder = 1004;
    public const temporary_directory_not_exist = 1005;
    public const parameters_illegal = 1006;
    public const file_upload_error = 1007;
    public const file_missing = 1008;
    public const  file_failed_to_open = 1009;

    // 定义默认错误信息
    protected array $errorMessages = [
        self::file_identifier_is_missing => '缺少文件标识符',
        self::file_invalid_identifier => '无效文件标识符',
        self::target_file_not_exist => '目标文件不存在',
        self::failed_to_create_the_folder => '创建文件夹失败',
        self::temporary_directory_not_exist => '临时块目录不存在，无法上传',
        self::parameters_illegal => '参数非法',
        self::file_upload_error => '文件上传错误',
        self::file_missing => '文件缺失',
        self::file_failed_to_open => '文件打开失败',
    ];

    /**
     * 构造函数
     *
     * @param int $code 错误码
     * @param string|null $customMessage 自定义错误信息（可选）
     */
    public function __construct($code, $customMessage = null)
    {
        $message = $customMessage ?? $this->errorMessages[$code] ?? 'Unknown error.';
        parent::__construct($message, $code);
    }

}