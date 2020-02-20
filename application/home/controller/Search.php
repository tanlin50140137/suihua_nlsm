<?php
namespace app\home\controller;
use think\Controller;

class Search {

	public function search(){
		  $goods = \think\Db::name('goods')->select();
		  $is_xiang = \think\Db::name('goods')->where(array('is_xiang'=>1))->limit(0,3)->select();

		  // dump($is_xiang);

		  $param = array();

        	$param['goods'] = $goods;
        	$param['is_xiang'] = $is_xiang;
		 return view('search',$param);
	}

	public function goods(){

		$id=input('id');
		$list = get_goods($id);
  //       if($list){
  //           $list['goods_logo'] = 'http://'.$_SERVER['SERVER_NAME'].$list['goods_logo'];
  //           foreach ($list['goods_image'] as $key => $value) {
  //               $value = 'http://'.$_SERVER['SERVER_NAME'].$value;
  //               $list['goods_image'][$key] = $value;
  //           }
       
		// }
		// dump($list);
		$param = array();
       	$param['list'] = $list;
		return view('goods',$param);
	}
	// return $this->fetch('member/read');
}