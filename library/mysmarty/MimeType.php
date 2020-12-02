<?php

namespace library\mysmarty;

/**
 * 文件类型
 * @package library\mysmarty
 */
class MimeType
{
    /**
     * 文件类型映射
     * @var array
     */
    public static array $data = [
        "text/html" => "html",
        "text/css" => "css",
        "text/xml" => "xml",
        "image/gif" => "gif",
        "image/jpeg" => "jpeg",
        "application/javascript" => "js",
        "application/atom+xml" => "atom",
        "application/rss+xml" => "rss",
        "text/mathml" => "mml",
        "text/plain" => "txt",
        "text/vnd.sun.j2me.app-descriptor" => "jad",
        "text/vnd.wap.wml" => "wml",
        "text/x-component" => "htc",
        "image/png" => "png",
        "image/svg+xml" => "svg",
        "image/tiff" => "tif",
        "image/vnd.wap.wbmp" => "wbmp",
        "image/webp" => "webp",
        "image/x-icon" => "ico",
        "image/x-jng" => "jng",
        "image/x-ms-bmp" => "bmp",
        "font/woff" => "woff",
        "font/woff2" => "woff2",
        "application/java-archive" => "jar",
        "application/json" => "json",
        "application/mac-binhex40" => "hqx",
        "application/msword" => "doc",
        "application/pdf" => "pdf",
        "application/postscript" => "ps",
        "application/rtf" => "rtf",
        "application/vnd.apple.mpegurl" => "m3u8",
        "application/vnd.google-earth.kml+xml" => "kml",
        "application/vnd.google-earth.kmz" => "kmz",
        "application/vnd.ms-excel" => "xls",
        "application/vnd.ms-fontobject" => "eot",
        "application/vnd.ms-powerpoint" => "ppt",
        "application/vnd.oasis.opendocument.graphics" => "odg",
        "application/vnd.oasis.opendocument.presentation" => "odp",
        "application/vnd.oasis.opendocument.spreadsheet" => "ods",
        "application/vnd.oasis.opendocument.text" => "odt",
        "application/vnd.openxmlformats-officedocument.presentationml.presentation" => "pptx",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => "xlsx",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document" => "docx",
        "application/vnd.wap.wmlc" => "wmlc",
        "application/x-7z-compressed" => "7z",
        "application/x-cocoa" => "cco",
        "application/x-java-archive-diff" => "jardiff",
        "application/x-java-jnlp-file" => "jnlp",
        "application/x-makeself" => "run",
        "application/x-perl" => "pl",
        "application/x-pilot" => "prc",
        "application/x-rar-compressed" => "rar",
        "application/x-redhat-package-manager" => "rpm",
        "application/x-sea" => "sea",
        "application/x-shockwave-flash" => "swf",
        "application/x-stuffit" => "sit",
        "application/x-tcl" => "tcl",
        "application/x-x509-ca-cert" => "der",
        "application/x-xpinstall" => "xpi",
        "application/xhtml+xml" => "xhtml",
        "application/xspf+xml" => "xspf",
        "application/zip" => "zip",
        "audio/midi" => "mid",
        "audio/mpeg" => "mp3",
        "audio/ogg" => "ogg",
        "audio/x-m4a" => "m4a",
        "audio/x-realaudio" => "ra",
        "video/3gpp" => "3gpp",
        "video/mp2t" => "ts",
        "video/mp4" => "mp4",
        "video/mpeg" => "mpeg",
        "video/quicktime" => "mov",
        "video/webm" => "webm",
        "video/x-flv" => "flv",
        "video/x-m4v" => "m4v",
        "video/x-mng" => "mng",
        "video/x-ms-asf" => "asx",
        "video/x-ms-wmv" => "wmv",
        "video/x-msvideo" => "avi"
    ];

    /**
     * 根据文件类型获取文件后缀
     * @param string $fileType 文件类型
     * @param string $defValue 默认值
     * @return string
     */
    public static function getExt(string $fileType, string $defValue = ''): string
    {
        $fileType = strtolower($fileType);
        if (isset(self::$data[$fileType])) {
            return self::$data[$fileType];
        }
        return $defValue;
    }
}