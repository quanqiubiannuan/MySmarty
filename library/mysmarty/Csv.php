<?php

namespace library\mysmarty;
/**
 * csv数据导出
 * @package library\mysmarty
 */
class  Csv
{
    private mixed $fp = null;

    /**
     * 开始导出数据
     * @param string $filename 导出的excel文件名称
     * @param array $header excel表格第一行设置，一维数组
     */
    public function startCsv(string $filename, array $header): void
    {
        header_remove();
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
        header('Cache-Control: no-store');
        if ($this->fp === null) {
            $this->fp = fopen('php://output', 'ab');
            fwrite($this->fp, "\xEF\xBB\xBF");
        }
        if (!empty($header) && is_array($header)) {
            fputcsv($this->fp, $header);
        }
    }

    /**
     * 输出错误信息
     * @param string $msg 错误信息
     */
    private function echoError(string $msg): void
    {
        header_remove();
        header('content-type:text/html;charset=utf-8');
        exit($msg);
    }

    /**
     * 添加数据置表格
     * @param array $data 一维或二维数组格式数据
     */
    public function putCsv(array $data): void
    {
        if (!empty($data)) {
            if (!is_array($data)) {
                $this->echoError('数据格式不为数组：当前数据格式为' . gettype($data));
            }
            if (count($data) === count($data, 1)) {
                // 一维数组，转为二维数组
                $data = [$data];
            }
            foreach ($data as $v) {
                $v = array_values($v);
                foreach ($v as &$v2) {
                    $v2 .= "\t";
                }
                unset($v2);
                fputcsv($this->fp, $v);
            }
            ob_flush();
            flush();
        }
    }

    /**
     * 结束导出
     */
    public function endCsv(): void
    {
        if ($this->fp !== null) {
            fclose($this->fp);
        }
        exit();
    }
}