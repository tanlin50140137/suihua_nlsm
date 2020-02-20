<?php
namespace app\nlsm\controller;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 朱岁华 <13719391518@163.com>
// +----------------------------------------------------------------------

class Database extends Common
{
	//显示数据库管理页面
    public function index() {
        //验证用户权限
        Common::checkpower(6);
    	$type = input('get.type','export');
        $unit = array('B', 'KB', 'MB', 'GB', 'TB');
		switch ($type) {
			case 'export':
        		//数据库备份
                $list  = \think\Db::query('SHOW TABLE STATUS');
                $list  = array_map('array_change_key_case', $list);
                foreach ($list as $key => $value) {
                    for ($i = 0; $value['data_length'] >= 1024 && $i < 5; $i++) $value['data_length'] /= 1024;
                    $value['data_length'] =  round($value['data_length'], 2) . $unit[$i];
                    $list[$key] = $value;
                }
				break;
			case 'import':
				//列出备份文件列表
                $size = 0;
		    	$pattern = "*.sql";
		    	$filelist = glob($_SERVER['DOCUMENT_ROOT'].'/public/sqldata/'.$pattern);
		    	$list = array();
		    	foreach ($filelist as $key => $value) {
		    		//只读取文件
		    		if (is_file($value)) {
		    			$size = filesize($value);
		    			$name = basename($value);
		    			$part = substr($name, 0, strrpos($name, '_'));
		    			$number = str_replace(array($part. '_', '.sql'), array('', ''), $name);
                        for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
                        $size =  round($size, 2) . $unit[$i];
                        
		    			$list[] = array(
		    				'name' => $name,
		    				'part' => $part,
		    				'time' => date('Y-m-d H:i:s',filemtime($value)),
		    				'size' => $size,
		    				'number' => $number,
		    			);
		    		}
		    	}
		    	krsort($list); //按备份时间倒序排列    	
				break;
		}
		//渲染模板
        return view($type,array('list'=>$list));
	}

	//优化表
    public function optimize(){
        //验证用户权限
        Common::checkpower(6);
        //获取优化的表
        $tables = input('post.tables/a','');
    	set_time_limit(0);
        if($tables) {
            if(is_array($tables)){
                $tables = implode('`,`', $tables);
                $list = \think\Db::query("OPTIMIZE TABLE `{$tables}`");
                if($list){
                	//记录系统日志
                    admin_log("优化 {$tables} 数据表！");
                    return json(array('success'=>true,'info'=>'数据表优化完成！'));
                } else {
                    return json(array('success'=>false,'info'=>'数据表优化出错请重试！'));
                }
            } else {
                $list = \think\Db::query("OPTIMIZE TABLE `{$tables}`");
                if($list){
                	//记录系统日志
                    admin_log("优化 {$tables} 数据表！");
                    return json(array('success'=>true,'info'=>"数据表'{$tables}'优化完成！"));
                } else {
                    return json(array('success'=>false,'info'=>"数据表'{$tables}'优化出错请重试！"));
                }
            }
        } else {
            return json(array('success'=>false,'info'=>'请指定要优化的表！'));
        }
    }

    //修复表
    public function repair(){
        //验证用户权限
        Common::checkpower(6);
        //获取优化的表
        $tables = input('post.tables/a','');
        if($tables) {
            if(is_array($tables)){
                $tables = implode('`,`', $tables);
                $list = \think\Db::query("REPAIR TABLE `{$tables}`");
                if($list){
                	//记录系统日志
                    admin_log("修复 {$tables} 数据表！");
                	return json(array('success'=>true,'info'=>'数据表修复完成！'));
                } else {
                    return json(array('success'=>false,'info'=>'数据表修复出错请重试！'));
                }
            } else {
                $list = \think\Db::query("REPAIR TABLE `{$tables}`");
                if($list){
                	//记录系统日志
                    admin_log("修复 {$tables} 数据表！");
                	return json(array('success'=>true,'info'=>"数据表'{$tables}'修复完成！"));
                } else {
                    return json(array('success'=>false,'info'=>"数据表'{$tables}'修复出错请重试！"));
                }
            }
        } else {
            return json(array('success'=>false,'info'=>'请指定要修复的表！'));
        }
    }

    // 删除备份文件
    public function delete(){
        //验证用户权限
        Common::checkpower(6);
        //获取要删除的文件
        $name = input('post.name','');
        if($name){
            $path  = $_SERVER['DOCUMENT_ROOT'].'/public/sqldata/'.DIRECTORY_SEPARATOR . $name;
            array_map("unlink", glob($path));
            if(count(glob($path))){
                return json(array('success'=>false,'info'=>"备份文件删除失败，请检查权限！"));
            } else {
            	//记录系统日志
        		admin_log("删除备份数据库备份文件：{$name} ");
            	return json(array('success'=>true,'info'=>"备份文件删除成功！"));
            }
        }else {
            return json(array('success'=>false,'info'=>'参数错误！'));
        }
    }

    //备份数据库
    public function export(){
        //验证用户权限
        Common::checkpower(6);
    	//获取要备份的表
        $tables = input('post.tables/a','');
        //防止备份数据过程超时
        set_time_limit(0);
        $tables = input('tables/a', array());
        if (empty($tables)) {
            return json(array('success'=>false,'info'=>'请选择要备份的数据表！'));exit;
        }
        $time = time();//开始时间
        $name = strlen(implode(',',$tables)) > 80 ? substr(implode(',',$tables), 0, 80).'...' : implode(',',$tables);
        $path = $_SERVER['DOCUMENT_ROOT'].'/public/sqldata/'.$name;

        $pre = "# -----------------------------------------------------------\n";
        //取得表结构信息
        //1，表示表名和字段名会用``包着的,0 则不用``
     
        \think\Db::execute("SET SQL_QUOTE_SHOW_CREATE = 1"); 
        $outstr = '';
        foreach ($tables as $table) {
            $outstr.="# 表的结构 {$table} \n";
            $outstr .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $tmp = \think\Db::query("SHOW CREATE TABLE {$table}");
            $outstr .= $tmp[0]['Create Table'] . " ;\n\n";
        }
        
        $sqlTable = $outstr;
        $outstr = "";
        $file_n = 1;
        $backedTable = array();
        //表中的数据
        foreach ($tables as $table) {
            $backedTable[] = $table;
            $outstr.="\n\n# 转存表中的数据：{$table} \n";
            $tableInfo = \think\Db::query("SHOW TABLE STATUS LIKE '{$table}'");
            $page = ceil($tableInfo[0]['Rows'] / 10000) - 1;
            for ($i = 0; $i <= $page; $i++) {
                $query = \think\Db::query("SELECT * FROM {$table} LIMIT " . ($i * 10000) . ", 10000");
                foreach ($query as $val) {
                    $temSql = "";
                    $tn = 0;
                    $temSql = '';
                    foreach ($val as $v) {
                        $temSql.=$tn == 0 ? "" : ",";
                        $temSql.=$v == '' ? "''" : "'{$v}'";
                        $tn++;
                    }
                    $temSql = "INSERT INTO `{$table}` VALUES ({$temSql});\n";

                    $sqlNo = "\n# Time: " . date("Y-m-d H:i:s") . "\n" .
                            "# -----------------------------------------------------------\n" .
                            "# SQLFile Label：#{$file_n}\n# -----------------------------------------------------------\n\n\n";
                       if ($file_n == 1) {
                        $sqlNo = "# Description:备份的数据表[结构]：" . implode(",", $tables) . "\n".
                                 "# Description:备份的数据表[数据]：" . implode(",", $backedTable) . $sqlNo;
                    } else {
                        $sqlNo = "# Description:备份的数据表[数据]：" . implode(",", $backedTable) . $sqlNo;
                    }

                    if (strlen($pre) + strlen($sqlNo) + strlen($sqlTable) + strlen($outstr) + strlen($temSql) > 5242880) {
                        $file = $path . "_" . $file_n . ".sql";
                        $outstr = $file_n == 1 ? $pre . $sqlNo . $sqlTable . $outstr : $pre . $sqlNo . $outstr;
                       
                        if (!file_put_contents($file, $outstr, FILE_APPEND)) {
                            return json(array('success'=>false,'info'=>'备份文件写入失败！'));exit;
                        }
    
                        $sqlTable = $outstr = "";
                        $backedTable = array();
                        $backedTable[] = $table;
                        $file_n++;
                        dump($file_n);
                        exit;
                    }
                    $outstr.=$temSql;
                }
            }
        }
        if (strlen($sqlTable . $outstr) > 0) {
            $sqlNo = "\n# Time: " . date("Y-m-d H:i:s") . "\n" .
                    "# -----------------------------------------------------------\n" .
                    "# SQLFile Label：#{$file_n}\n# -----------------------------------------------------------\n\n\n";
            if ($file_n == 1) {
                $sqlNo = "# Description:备份的数据表[结构] " . implode(",", $tables) . "\n".
                         "# Description:备份的数据表[数据] " . implode(",", $backedTable) . $sqlNo;
            } else {
                $sqlNo = "# Description:备份的数据表[数据] " . implode(",", $backedTable) . $sqlNo;
            }
            $file = $path . "_" . $file_n . ".sql";
            $outstr = $file_n == 1 ? $pre . $sqlNo . $sqlTable . $outstr : $pre . $sqlNo . $outstr;
//			exit($file);
            if (!file_put_contents($file, $outstr, FILE_APPEND)) {
                return json(array('success'=>false,'info'=>'备份文件写入失败！'));exit;
            }
            $file_n++;
        }
        //记录系统日志
        admin_log("备份数据表：{$file} ");
        $time = time() - $time;
        return json(array('success'=>'true','info'=>"成功备份数据表，本次备份共生成了" . ($file_n-1) . "个SQL文件。耗时：{$time} 秒！"));
    }

    //还原数据库
    public function import(){
        //验证用户权限
        Common::checkpower(6);
    	//防止还原数据过程超时
    	set_time_limit(0); 
    	//取得需要导入的sql文件
    	if (!isset($_SESSION['cacheRestore']['files'])) {
    		$_SESSION['cacheRestore']['starttime'] = time();
    		//获取要还原的数据库备份文件
    		$sqlfilepre = input('post.name','');
	    	if (empty($sqlfilepre)) {
	    		return json(array('success'=>'false','info'=>"请选择要还原的数据文件！"));exit;
	    	}
	    	$sqlFiles = glob($_SERVER['DOCUMENT_ROOT'].'/public/sqldata/'.$sqlfilepre);
	    	if (empty($sqlFiles)) {
	    		return json(array('success'=>'false','info'=>"不存在对应的SQL文件！"));exit;
	    	}
	    	//将要还原的sql文件按顺序组成数组，防止先导入不带表结构的sql文件
	    	$files = array();
	    	foreach ($sqlFiles as $sqlFile) {
	    		$sqlFile = basename($sqlFile);
	    		$k = str_replace(".sql", "", str_replace($sqlfilepre . "_", "", $sqlFile));
	    		$files[$k] = $sqlFile;
	    	}
	    	unset($sqlFiles, $sqlfilepre);
	    	ksort($files);
    		$_SESSION['cacheRestore']['files'] = $files;
    	}

    	$files = $_SESSION['cacheRestore']['files'];
    	if (empty($files)) {
    		unset($_SESSION['cacheRestore']);
    		return json(array('success'=>'false','info'=>"不存在对应的SQL文件！"));exit;
    	}
    
    	//取得上次文件导入到sql的句柄位置
    	$position = isset($_SESSION['cacheRestore']['position']) ? $_SESSION['cacheRestore']['position'] : 0;
    	$execute = 0;
    	foreach ($files as $fileKey => $sqlFile) {
    		$file = $_SERVER['DOCUMENT_ROOT'].'/public/sqldata/'. $sqlFile;
    		if (!file_exists($file))
    			continue;
    		$file = fopen($file, "r");
    		$sql = "";
    		fseek($file, $position); //将文件指针指向上次位置
    		while (!feof($file)) {
    			$tem = trim(fgets($file));
    			//过滤,去掉空行、注释行(#,--)
    			if (empty($tem) || $tem[0] == '#' || ($tem[0] == '-' && $tem[1] == '-'))
    				continue;
    			//统计一行字符串的长度
    			$end = (int) (strlen($tem) - 1);
    			//检测一行字符串最后有个字符是否是分号，是分号则一条sql语句结束，否则sql还有一部分在下一行中  
	    	   if ($tem[$end] == ";") {
	    	   $sql .= $tem;
	    	   \think\Db::execute($sql);
	    	   $sql = "";
	    	   $execute++;
	    	   		if ($execute > 500) {
			    		$_SESSION['cacheRestore']['position'] = ftell($file);
			    		$imported = isset($_SESSION['cacheRestore']['imported']) ? $_SESSION['cacheRestore']['imported'] : 0;
			    		$imported += $execute;
			    		$_SESSION['cacheRestore']['imported'] = $imported;
			    		return json(array('success'=>'true','info'=>'如果SQL文件卷较大(多),则可能需要几分钟甚至更久,<br/>请耐心等待完成，<font color="red">请勿刷新本页</font>，<br/>当前导入进度：<font color="red">已经导入' . $imported . '条Sql</font>！'));exit;
			    		exit();
					}
	    		} else {
	    			$sql .= $tem;
	    		}
    		}
    		//错误位置结束
    		fclose($file);
    		unset($_SESSION['cacheRestore']['files'][$fileKey]);
    		$position = 0;
    	}
    	$time = time() - $_SESSION['cacheRestore']['starttime'];
    	unset($_SESSION['cacheRestore']);
    	return json(array('success'=>'true','info'=>"导入成功，耗时：{$time} 秒钟！"));
    }
}
