<?php

class output{

    public function excute(){

        set_time_limit(0);
        ini_set('memory_limit', '128M');

        $fileName = date('YmdHis', time());
        header('Content-Encoding: UTF-8');
        header("Content-type:application/vnd.ms-excel;charset=UTF-8");
        header('Content-Disposition: attachment;filename="' . $fileName . '.csv"');
//注意，数据量在大的情况下。比如导出几十万到几百万，会出现504 Gateway Time-out,请修改php.ini的max_execution_time参数
//打开php标准输出流以写入追加的方式打开
        $fp = fopen('php://output', 'a') or die('can‘t open php://output');


//用fputcsv从数据库中导出1百万的数据,比如我们每次取1万条数据，分100步来执行
//一次性读取1万条数据，也可以把$nums调小，$step相应增大。
        $step  = 100;
        $nums  = 10000;
        $where = "where 1=1"; //筛选条件,可自行添加

//设置标题
        $title = array('id', 'name', 'content'); //注意这里是小写id,否则ID命名打开会提示Excel 已经检测到"xxx.xsl"是SYLK文件,但是不能将其加载: CSV 文或者XLS文件的前两个字符是大写字母"I"，"D"时，会发生此问题。
        foreach ($title as $key => $item)
            $title[$key] = iconv("UTF-8", "GB2312//IGNORE", $item);

        fputcsv($fp, $title);
        for ($s = 1; $s <= $step; $s++) {
            $start  = ($s - 1) * $nums;
            $result = DB::table('tests')->select(['id', 'name', 'content'])->orderBy('id')->offset($start)->limit($nums)->get()->map(function ($value) {

                return (array)$value;

            })->toArray();
            if ($result) {

                foreach ($result as $key => $item) {
                    foreach ($item as $t) {
                        $result[$key] = iconv("UTF-8", "GBK", $t); //这里必须转码，不然会乱码
                    }
                    fputcsv($fp, $item);
                }

//                        $result[$key] = iconv("UTF-8", "GBK", $item); //这里必须转码，不然会乱码


                ob_flush();  //每1万条数据就刷新缓冲区
                flush();
            }
        }
        fclose($fp) or die('can‘t close php://output');

        return 1;

    }

}
