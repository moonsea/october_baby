<?php
session_start();
/**
 * 十月宝宝
 * @author DONGYUE
 * 2016年05月12日08:32:25
 * QQ491024175
 * @url http://bbs.we7.cc/
 */
defined ( 'IN_IA' ) or exit ( 'Access Denied' );
class water_babyModuleSite extends WeModuleSite {

	/**
	 * 粉丝表
	 * @var unknown
	 */
	public $fanstable= 'water_obaby_fans';

	/**
	 * 文章分类表
	 */
	public $articletypetable = 'water_obaby_articletype';

	/**
	 * 文章表
	 */
	public $articletable = 'water_obaby_article';

	/**
	 *支付订单表
	 */
	public $ordertable = 'water_obaby_order';

	/**
	 *支付订单表
	 */
	public $logtable = 'water_obaby_log';

	/**
	 * 首页
	 * @return [type] [description]
	 */
	public function doMobileindex(){
		global $_W,$_GPC;

		/* 今日推荐文章列表 limit 4 */
		$tjsql="SELECT * FROM ".tablename($this->articletable)."
		WHERE uniacid = '{$_W['uniacid']}' and status = 2 ORDER BY indexno LIMIT 4";
		$tjlist = pdo_fetchall($tjsql);

		$articletypesql ="SELECT * FROM ".tablename($this->articletypetable)." WHERE uniacid = '{$_W['uniacid']}' and state = 2  ORDER BY indexno ";
		$articletypelist = pdo_fetchall($articletypesql);

		include $this->template('index');
	}

	/**
	 * 文章列表
	 * @return [type] [description]
	 */
	public function doMobileArticlelist(){
		global $_W,$_GPC;
		$condition = " and 1=1 ";
		$status = $_GPC['status'];
		if(!empty($status)){
			$condition.= " and status = 2";
		}
		$articletypeid = intval($_GPC['articletypeid']);
		if($articletypeid > 0){
			$condition.= " and articletypeid = '{$articletypeid}'";
		}
		$sql="SELECT * FROM ".tablename($this->articletable)."
					WHERE uniacid = '{$_W['uniacid']}' {$condition} ORDER BY indexno LIMIT 100";
		$list = pdo_fetchall($sql);
		include $this->template('article-list');
	}

	/**
	 * 文章内容
	 * @return [type] [description]
	 */
	public function doMobileArticle(){
		global $_W,$_GPC;

		$id = intval($_GPC['id']);
		if($id <= 0){
			message('id is null');
		}

		$auth_type = $_GPC['auth_type'];
		if(!is_null($auth_type))
		{
			checkauth();
		}
		// checkauth();

		/* 文章详情 */
		$article = pdo_fetch("SELECT * FROM ".tablename($this->articletable)." WHERE id= '{$id}'");
		$article['addtime'] = date('Y-m-d',$article['addtime']);

		/* 收藏总数 */
		$fav_user_id = pdo_fetchall("SELECT user_id from ".tablename('water_baby_article_user')." WHERE article_id = '{$id}'");
		$fav_count = count($fav_user_id);
		$fav_status = in_array($_W['member']['uid'], $fav_user_id['user_id'])? '1':'0';

		include $this->template('article');
	}

	/**
	 * 收藏文章
	 * @return [type] [description]
	 */
	public function doMobileFavArticle()
	{
		global $_W,$_GPC;

		// checkauth();

		$user_id = $_W['member']['uid'];
		$article_id = $_GPC['article_id'];
		$url = $this->createMobileUrl('article', array('id' => $article_id,'auth_type' => '1'));
		if(is_null($user_id))
		{
			message('更新成功！', $url, 'success');
		}

		// $article_id = $_GPC['article_id'];
		$fav_status = $_GPC['fav_status'];

		$result = '';
		if($fav_status == '1')
		{
			$result = pdo_delete('water_baby_article_user', array('article_id' => $article_id, `user_id` => $user_id));
		}
		else {
			$result = pdo_insert('water_baby_article_user', array('article_id' => $article_id, `user_id` => $user_id));
		}

		if (!empty($result)) {
			message('更新成功！', referer(), 'success');
		}
		else {
			message('更新失败');
		}

	}

	/**
	 * 聊天
	 * @return [type] [description]
	 */
	public function doMobileChat(){
		global $_W,$_GPC;

		checkauth();

		$user_id = $_W['member']['uid'];
		$doctor_id = $_SESSION['doctor_id'];

		/* 医生信息 */
		$mem_doctor = pdo_get('mc_members',array('uid' => $doctor_id));

		/* 聊天信息 */
		$chat_info = pdo_get('mc_doctor_user',array('user_id' => $user_id,'doctor_id' => $doctor_id));

		/* 会话列表 */

		include $this->template('chat');
	}

	/**
	 * 保存用户会话roomId
	 * @return [type] [description]
	 */
	public function doMobileSaveRoomId(){
		global $_W,$_GPC;

		checkauth();

		$id = $_GPC['id'];
		$room_id = $_GPC['room_id'];

		$result = pdo_update('mc_doctor_user', array('room_id' => $room_id),array('id' => $id));

		if (!empty($result)) {
			message('会话保存成功！', '', 'success');
		}
		else {
			message('会话保存失败');
		}
	}

	/**
	 * 咨询
	 * @return [type] [description]
	 */
	public function doMobileconsult(){
		global $_W,$_GPC;

		checkauth();

		$uid = $_W['member']['uid'];

		/* 咨询关系信息 */
		$consult = pdo_get('mc_doctor_user',array('user_id' => $uid));

		/* 医生信息 */
		$mem_doctor = pdo_get('mc_members',array('uid' => $consult['doctor_id']));

		$_SESSION['doctor_id'] = $consult['doctor_id']; /* 仅用作测试 */

		include $this->template('consult');
	}

	/**
	 * 咨询列表
	 * @return [type] [description]
	 */
	public function doMobileexplain(){
		global $_W,$_GPC;

		checkauth();

		$user_id = $_W['member']['uid'];

		/* 获取咨询医生会话列表 */
		$sql = "SELECT * FROM ".tablename('user_doctor_conv')." WHERE user_id = '{$user_id}' ORDER BY addtime DESC";
		$conv_list = pdo_fetchall($sql);

		for ($i=0; $i < count($conv_list); $i++) {
			$conv_list[$i]['addtime'] = date('Y-m-d H:i', $conv_list[$i]['addtime']);
			$conv_list[$i]['doctor_info'] = $this->getMemInfo($conv_list[$i]['doctor_id']);
		}

		include $this->template('explain');
	}

	/**
	 * 用户医生交流详细信息
	 * @return [type] [description]
	 */
	public function doMobileShowConvDetail()
	{
		global $_W, $_GPC;

		checkauth();

		$conv_id = $_GPC['conv_id'];

		include $this->template('conv_detail');
	}

	/**
	 * 获取用户信息
	 * @param  [type] $user_id [description]
	 * @return [type]          [description]
	 */
	public function getMemInfo($user_id)
	{
		$sql = "SELECT * FROM ".tablename('mc_members')." WHERE uid = '{$user_id}'";
		$mem_info = pdo_fetch($sql);
		return $mem_info;
	}

	/**
	 * 收藏列表
	 * @return [type] [description]
	 */
	public function doMobilefavorite(){
		global $_W,$_GPC;

		checkauth();

		$uid = $_W['member']['uid'];

		/* 文章id列表 */
		$sql = "SELECT * FROM ".tablename('water_baby_article_user')." WHERE user_id = '{$uid}'";
		$article_list = pdo_fetchall($sql);

		/* 文章内容 */
		for ($i=0; $i < count($article_list); $i++) {

			/* 文章详情 */
			$article_list[$i]['article'] = $this->getArticleDetail($article_list[$i]['article_id']);

			/* 收藏总数 */
			$article_list[$i]['fav_count'] = $this->getArticleFavCount($article_list[$i]['article_id']);

		}

		include $this->template('favorite');
	}

	/**
	 * 获取文章内容
	 * @param  [type] $article_id [description]
	 * @return [type]             [description]
	 */
	public function getArticleDetail($article_id)
	{
		/* 文章详情 */
		$article = pdo_fetch("SELECT * FROM ".tablename($this->articletable)." WHERE id= '{$article_id}'");
		$article['addtime'] = date('Y-m-d',$article['addtime']);
		return $article;
	}

	/**
	 * 获取文章关注总数
	 * @param  [type] $article_id [description]
	 * @return [type]             [description]
	 */
	public function getArticleFavCount($article_id)
	{
		$fav_user_id = pdo_fetchall("SELECT user_id from ".tablename('water_baby_article_user')." WHERE article_id = '{$article_id}'");
		$fav_count = count($fav_user_id);
		return $fav_count;
	}

	/**
	 * 我的
	 * @return [type] [description]
	 */
	public function doMobilemy(){
		global $_W,$_GPC;

		/*校验登录*/
		checkauth();

		$uid = $_W['member']['uid'];

		/* 获取个人信息 */
		$mem_info = pdo_get('mc_members',array('uid' => $uid));

		/* 获取医生相关信息 */
		$mem_info['hosp_name'] = pdo_get('mc_hospital',array('hosp_id' => $mem_info['hosp_id']))['hosp_name'];
		$mem_info['dep_name'] = pdo_get('mc_hosp_dep',array('dep_id' => $mem_info['dep_id']))['dep_name'];
		$mem_info['title_name'] = pdo_get('mc_hosp_title',array('title_id' => $mem_info['title_id']))['title_name'];

		/* 孕妇相关信息 */
		$mem_info['baby_birthday'] = $mem_info['baby_birthday'] == null? date('Y/m/d',0):date('Y/m/d',$mem_info['baby_birthday']);

		include $this->template('mine-docotor');
	}

	/**
	 * 更新baby_birthday
	 * @return [type] [description]
	 */
	public function doMobileBabyBirthUpdate(){
		global $_W,$_GPC;

		checkauth();

		$uid = $_W['member']['uid'];

		$baby_birthday = $_GPC['baby_birthday'];
		$baby_birthday = strtotime($baby_birthday);

		$result = pdo_update('mc_members', array('baby_birthday' => $baby_birthday),array('uid' => $uid));

		if (!empty($result)) {
			message('更新成功！', referer(), 'success');
		}
		else {
			message('更新失败');
		}

	}

	/**
	 * 注册身份选择
	 * @return [type] [description]
	 */
	public function doMobileRegistType(){
		global $_W,$_GPC;
		include $this->template('regist-type');
	}

	/**
	 * 注册身份更新
	 * @return [type] [description]
	 */
	public function doMobileRegTypeUpdate(){
		global $_W,$_GPC;

		checkauth();

		$uid = $_W['member']['uid'];
		$user_type = $_GPC['reg_type'];

		$result = pdo_update('mc_members', array('groupid' => $user_type),array('uid' => $uid));

		$url = '';
		if($user_type == '5')
		{
			$url = $this->createMobileUrl('DoctorStep1');
		}
		else {
			$url = $this->createMobileUrl('PregType');
		}

		if (!empty($result)) {
			message('身份信息更新成功！', $url, 'success');
		}
		else {
			message('身份更新失败');
		}

	}

	/**
	 * 上传孕检结果照片
	 * @return [type] [description]
	 */
	public function doMobileupload(){
		global $_W,$_GPC;

		checkauth();

		$uid = $_W['member']['uid'];

		/* 获取个人信息 */
		$mem_info = pdo_get('mc_members',array('uid' => $uid));

		/* 获取个人状态列表 */
		$sql = "SELECT * FROM ".tablename('mc_moment')."
					WHERE uid = '".$uid."' ORDER BY upload_time DESC";
		$moment_list = pdo_fetchall($sql);

		/* 解析时间 */
		for($i=0; $i < count($moment_list); $i++)
		{
			$moment_list[$i]['day'] = date("d",$moment_list[$i]['upload_time']);
			$moment_list[$i]['month'] = date("m",$moment_list[$i]['upload_time']);
			$moment_list[$i]['year'] = date("Y",$moment_list[$i]['upload_time']);
			$moment_list[$i]['pic_list'] = $this->getMomPic($moment_list[$i]['id']);
		}

		include $this->template('upload');
	}

	/**
	 * 获取图片列表
	 * @param  [type] $moment_id [description]
	 * @return [type]            [description]
	 */
	public function getMomPic($moment_id)
	{
		/* 获取每条状态的图片列表 */
		$sql = "SELECT * FROM ".tablename('mc_mom_pic')."
					WHERE moment_id = '".$moment_id."' ORDER BY pic_id";
		$pic_list = pdo_fetchall($sql);

		return $pic_list;
	}

	/**
	 * 显示检查结果详情
	 * @return [type] [description]
	 */
	public function doMobileShowMomDetail()
	{
		global $_W,$_GPC;

		checkauth();

		$uid = $_W['member']['uid'];
		$moment_id = $_GPC['moment_id'];

		/* 获取个人信息 */
		$mem_info = pdo_get('mc_members',array('uid' => $uid));

		/* 获取状态信息 */
		$mom_info = pdo_get('mc_moment',array('uid' => $uid, 'id' => $moment_id));

		/* 状态图片列表 */
		$pic_list = $this->getMomPic($moment_id);

		include $this->template('moment_detail');

	}

	/**
	 * 拍照上传
	 * @return [type] [description]
	 */
	public function doMobileDoUpload()
	{
		global $_W,$_GPC;

		checkauth();

		include $this->template('do_upload');
	}
	/*
	**图片上传
	*/
	public function doMobileSingleUplod()
	{
		global $_W,$_GPC;
		
		$img_desc = $this->reArrayFiles($img);

			    foreach($img_desc as $val)
			    {
			        $newname = date('YmdHis',time()).mt_rand().'.jpg';
					if (!move_uploaded_file($val['tmp_name'],'../attachment/images/upload/'.$newname)) {
						$status = 0;
			$message = 'Upload failed';
					} else {
						$pic_path = "/images/upload/".$newname;
						//$pic_path = "/images/upload/".$newname;
						//$result = pdo_insert('mc_mom_pic', array('pic_url' => $pic_path, 'moment_id' => $mom_id));
					}
			    }
		
		
		//$val = $_FILES['file'];
		//$newname = date('YmdHis',time()).mt_rand().'.jpg';
		//$status = 1;
		//$message = 'OK';
		//$pic_path = '';
		//if (!move_uploaded_file($val['tmp_name'],'../attachment/images/upload/'.$newname)) {
		//	$status = 0;
		//	$message = 'Upload failed';
		//} else {
		//	$pic_path = "/images/upload/".$newname;
		//}
		$ret = array();
		$ret['status'] = $status;
		$ret['message'] = $message;
		$ret['pic_path'] = $pic_path;
		echo json_encode($ret);
		exit(0);
	}

	/**
	 * 多文件上传
	 * @return [type] [description]
	 */
	public function doMobileMultiUpload()
	{
		global $_W,$_GPC;

		checkauth();

		var_dump($_FILES['check_result']);
		exit(0);


		$mom_con = $_GPC['info'];

		if ($mom_con != '') {

			/* 插入检查结果 */
			$mom_data = array(
				'uid' => $_W['member']['uid'],
				'content' => $_GPC['info'],
				'upload_time' => time()
			);
			$result = pdo_insert('mc_moment', $mom_data);

			$mom_id = '';
			if (!empty($result)) {
				$mom_id = pdo_insertid();
			}

			$img = $_FILES['check_result'];
			if(!empty($img) && $mom_id != '')
			{
				// echo 'test';
			    $img_desc = $this->reArrayFiles($img);

			    foreach($img_desc as $val)
			    {
			        $newname = date('YmdHis',time()).mt_rand().'.jpg';
					if (!move_uploaded_file($val['tmp_name'],'../attachment/images/upload/'.$newname)) {
						message('图片上传失败');
						continue;
					} else {
						$pic_path = "/images/upload/".$newname;
						$result = pdo_insert('mc_mom_pic', array('pic_url' => $pic_path, 'moment_id' => $mom_id));
					}
			    }
			}

			$_GPC['info'] = '';

		}

		message('',$this->createMobileUrl('upload'), 'success');

	}

	function reArrayFiles($file)
	{
		// var_dump($file);
	    $file_ary = array();
	    $file_count = count($file['name']);
	    $file_key = array_keys($file);

	    for($i=0;$i<$file_count;$i++)
	    {
	        foreach($file_key as $val)
	        {
	            $file_ary[$i][$val] = $file[$val][$i];
	        }
	    }
	    return $file_ary;
	}

	/**
	 * 微信支付
	 * @return [type] [description]
	 */
	public function doMobilePay() {
		global $_W,$_GPC;
		//var_dump($_W);
		checkauth();

		$uid = $_W['member']['uid'];
		$doctor_id = $_GPC['doctor_id'];

	    //获取用户要充值的金额数
		$money = pdo_get('mc_doctor_user',array('user_id' => $uid,'doctor_id' => $doctor_id));
	    $fee = floatval($money['price_per']);

	    if($fee <= 0) {
	        message('支付错误, 金额小于0');
	    }

		/*随机生成订单号*/
		srand((double)microtime()*1000000);
		$ordersn = rand();

	    //构造支付请求中的参数
	    $params = array(
			'title' => '咨询费',          //收银台中显示的标题
			'ordersn' => $ordersn,//time(),//date('Ymdhis'), //$chargerecord['tid'],  //收银台中显示的订单号
			'tid' => date('Ymdhis'),//$chargerecord['tid'],      //充值模块中的订单号，此号码用于业务模块中区分订单，交易的识别码
	        'fee' => $fee,      //收银台中显示需要支付的金额,只能大于 0
	        'user' => $_W['member']['uid'],     //付款用户, 付款的用户名(选填项)
	    );

		$_SESSION['doctor_id'] = $doctor_id;
		// exit(0);
	    //调用pay方法
	    $this->pay($params);
	}

	/**
	 * 支付结果
	 */
	//该代码片断在/framework/builtin/recharge/site.php中
	public function payResult($params) {
		// message('支付成功！', url('entry', array('do' => 'chat','m' => 'water_baby')), 'success');
	    //一些业务代码
		//根据参数params中的result来判断支付是否成功
		if ($params['result'] == 'success' && $params['from'] == 'notify') {
			if ($params['result'] == 'success') {
		   message('支付成功！', url('entry', array('do' => 'chat','m' => 'water_baby')), 'success');
		   } else {
			   message('支付失败！', url('entry', array('do' => 'consult','m' => 'water_baby')), 'error');
		   }
		}
		//因为支付完成通知有两种方式 notify，return,notify为后台通知,return为前台通知，需要给用户展示提示信息
		//return做为通知是不稳定的，用户很可能直接关闭页面，所以状态变更以notify为准
		//如果消息是用户直接返回（非通知），则提示一个付款成功
		if ($params['from'] == 'return') {
			if ($params['result'] == 'success') {
		   message('支付成功！', url('entry', array('do' => 'chat','m' => 'water_baby')), 'success');
		   } else {
			   message('支付失败！', url('entry', array('do' => 'consult','m' => 'water_baby')), 'error');
		   }
		}

	}

	/**
	 * 医院选择
	 */
	 public function doMobileDoctorStep1(){
		 global $_W,$_GPC;

		 checkauth();

		 /* 医院列表 */
		 $sql="SELECT * FROM ".tablename('mc_hospital')." ORDER BY hosp_id";
		 $hosp_list = pdo_fetchall($sql);

		 /* 用户选择 */
		 $mem = pdo_get('mc_members',array('uid' =>$_W['member']['uid']),array('hosp_id'));

		 include $this->template('doctor-step1');
	 }

	 /**
	  * 更新医生所属医院
	  * @return [type] [description]
	  */
	 public function doMobileDoctorHospUpdate(){
		global $_W,$_GPC;

		$url = $this->createMobileUrl('DoctorStep2');

		$hosp_id = $_GPC['hosp_id'];
		$old_hosp_id = $_GPC['old_hosp_id'];

		if ($hosp_id == $old_hosp_id) {
			message('更新成功！', $url, 'success');
		}

		$uid = $_W['member']['uid'];

		$result = pdo_update('mc_members', array('hosp_id' => $hosp_id),array('uid' => $uid));

		if (!empty($result)) {
			message('更新成功！', $url, 'success');
		}
		else {
			message('更新失败');
		}

	}

	 /**
	  * 科室选择
	  * @return [type] [description]
	  */
	 public function doMobileDoctorStep2(){
		 global $_W,$_GPC;

		 checkauth();

		 /* 科室列表 */
		 $sql="SELECT * FROM ".tablename('mc_hosp_dep')." ORDER BY dep_id";
		 $dep_list = pdo_fetchall($sql);

		 /* 用户选择 */
		 $mem = pdo_get('mc_members',array('uid' =>$_W['member']['uid']),array('dep_id'));

		 include $this->template('doctor-step2');
	 }

	 /**
	  * 更新医生科室选择
	  * @return [type] [description]
	  */
	 public function doMobileDoctorDepUpdate(){
	   global $_W,$_GPC;

	   $url = $this->createMobileUrl('DoctorStep3');

	   $dep_id = $_GPC['dep_id'];
	   $old_dep_id = $_GPC['old_dep_id'];

	   if ($dep_id == $old_dep_id) {
		   message('更新成功！', $url, 'success');
	   }

	   $uid = $_W['member']['uid'];

	   $result = pdo_update('mc_members', array('dep_id' => $dep_id),array('uid' => $uid));

	   if (!empty($result)) {
		   message('更新成功！', $url, 'success');
	   }
	   else {
		   message('更新失败');
	   }

   }

	 /**
	  * 职称选择
	  * @return [type] [description]
	  */
	 public function doMobileDoctorStep3(){
		 global $_W,$_GPC;

		 checkauth();

		 /* 职称列表 */
		$sql="SELECT * FROM ".tablename('mc_hosp_title')." ORDER BY title_id";
		$title_list = pdo_fetchall($sql);

		/* 用户选择 */
		$mem = pdo_get('mc_members',array('uid' =>$_W['member']['uid']),array('title_id'));

		 include $this->template('doctor-step3');
	 }

	 /**
	  * 更新医生职称
	  * @return [type] [description]
	  */
	 public function doMobileDoctorTitleUpdate(){
	  global $_W,$_GPC;

	  $url = $this->createMobileUrl('my');

	  $title_id = $_GPC['title_id'];
	  $old_title_id = $_GPC['old_title_id'];

	  if ($title_id == $old_title_id) {
		  message('更新成功！', $url, 'success');
	  }

	  $uid = $_W['member']['uid'];

	  $result = pdo_update('mc_members', array('title_id' => $title_id),array('uid' => $uid));

	  if (!empty($result)) {
		  message('更新成功！', $url, 'success');
	  }
	  else {
		  message('更新失败');
	  }

   }

	 /**
	  * 孕妇状态
	  * @return [type] [description]
	  */
	 public function doMobilePregType(){
		 global $_W,$_GPC;

		 checkauth();

		 include $this->template('pregnant-type');
	 }

	 /**
	  * 更改孕妇状态
	  * @return [type] [description]
	  */
	 public function doMobilePregTypeUpdate(){
 		global $_W,$_GPC;

 		$uid = $_W['member']['uid'];
 		$preg_type = $_GPC['preg_type'];

 		$result = pdo_update('mc_members', array('preg_type' => $preg_type),array('uid' => $uid));

 		$url = '';
 		if($preg_type == '0' || $preg_type == '1')
 		{
 			$url = $this->createMobileUrl('prepare');
 		}
 		else {
 			$url = $this->createMobileUrl('my');
 		}

 		if (!empty($result)) {
 			message('身份更新成功！', $url, 'success');
 		}
 		else {
 			message('身份更新失败');
 		}

 	}

	 /**
	  * 许愿
	  */
	 public function doMobilePrepare(){
		 global $_W,$_GPC;

		 checkauth();

		 include $this->template('prepare');
	 }

	 /**
	  * 更新宝宝性别
	  * @return [type] [description]
	  */
	 public function doMobileBabySexUpdate(){
		global $_W,$_GPC;

		$uid = $_W['member']['uid'];
		$baby_sex = $_GPC['sex_type'];

		$result = pdo_update('mc_members', array('baby_sex' => $baby_sex),array('uid' => $uid));

		$url = $this->createMobileUrl('my');

		if (!empty($result)) {
			message('许愿成功！', $url, 'success');
		}
		else {
			message('许愿失败');
		}

	}

	/**
	 * update self discription
	 * @return [type] [description]
	 */
	public function doMobileBioUpdate(){
	   global $_W,$_GPC;

	   checkauth();

	   $uid = $_W['member']['uid'];
	   $bio = $_GPC['bio'];

	   $result = pdo_update('mc_members', array('bio' => $bio),array('uid' => $uid));

	   if (!empty($result)) {
		   message('更新成功！', referer(), 'success');
	   }
	   else {
		   message('更新失败');
	   }

   }

	 /**
	  * 关于十月
	  */
	public function doMobileAbout()
	{
		global $_W,$_GPC;
		include $this->template('about');
	}



	/************* Web *******************************/

	/**
	 * 文章分类管理
	 */
	public function dowebArticletype(){
		global $_W,$_GPC;
		$pageNumber = max(1, intval($_GPC['page']));
		$pageSize = 20;
		$sql="SELECT * FROM ".tablename($this->articletypetable)." WHERE uniacid = '{$_W['uniacid']}'  ORDER BY indexno LIMIT ".($pageNumber - 1) * $pageSize.','.$pageSize;
		$list = pdo_fetchall($sql);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' .tablename($this->articletypetable)." WHERE uniacid = '{$_W['uniacid']}'  ORDER BY indexno");
		$pager = pagination($total, $pageNumber, $pageSize);
		include $this->template('articletype');
	}

	/**
	 * 增加分类
	 */
	public function dowebaddArticletype(){
		global $_W,$_GPC;
		load()->func('tpl');
		$articletypeid = intval($_GPC['articletypeid']);
		if($articletypeid > 0){
			$articletype = pdo_fetch("SELECT * FROM ".tablename($this->articletypetable)." WHERE id= '{$articletypeid}'");
			if (!$articletype) {
				message ( '抱歉，信息不存在或是已经删除！', '', 'error' );
			}
			//$topicurl = $_W['siteroot'].'app/'.$this->createMobileUrl ( 'topic',array('topicid'=>$topicid));
		}

		if($_GPC['op']=='delete'){
			$articletype = pdo_fetch ( "SELECT id FROM " . tablename ($this->articletypetable) . " WHERE id = '{$articletypeid}'" );
			if (empty ( $articletype['id'] )) {
				message ( '抱歉，信息不存在或是已经被删除！' );
			}
			pdo_delete ($this->articletable, array ('articletypeid' => $topicid) );
			pdo_delete ($this->articletypetable, array ('id' => $articletypeid) );
			message ( '删除成功！', referer (), 'success' );
		}

		if (checksubmit()) {

			$data = array(
					'title' => $_GPC ['title'],
					'desc' => $_GPC ['desc'],
					'img' => $_GPC ['img'],
					'indexno' => intval($_GPC ['indexno']),
					'state' => intval($_GPC ['state']),
			);

			if (!empty($articletypeid)) {
				pdo_update($this->articletypetable, $data, array('id' => $articletypeid));
			}else {
				$data['uniacid'] = $_W['uniacid'];
				pdo_insert($this->articletypetable, $data);
				$articletype = pdo_insertid();
			}
			$articletype = pdo_fetch ( "SELECT * FROM " . tablename ($this->articletypetable) . " WHERE id = '{$articletypeid}'" );
			message('更新成功！', referer(), 'success');
		}
		include $this->template('addarticletype');
	}


	/**
	 * 文章管理
	 */
	public function dowebarticle(){
		global $_W,$_GPC;
		$pageNumber = max(1, intval($_GPC['page']));
		$pageSize = 200;
		$articletypeid = intval($_GPC['articletypeid']);
		$condition = " and 1=1 ";
		if($articletypeid > 0){
			$condition .= "and articletypeid ='{$articletypeid}'";
		}
		$sql="SELECT * FROM ".tablename($this->articletable)."
					WHERE uniacid = '{$_W['uniacid']}' {$condition} ORDER BY indexno LIMIT ".($pageNumber - 1) * $pageSize.','.$pageSize;
		$list = pdo_fetchall($sql);
		for ($i=0; $i < count($list); $i++) {
			$list[$i]['addtime'] = date('Y-m-d',$list[$i]['addtime']);
		}
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' .tablename($this->articletable)."
					WHERE uniacid = '{$_W['uniacid']}' {$condition} ORDER BY indexno");
		$pager = pagination($total, $pageNumber, $pageSize);
		include $this->template('article');
	}

	/**
	 * 编辑帖子
	 */
	public function dowebaddarticle(){
		global $_W,$_GPC;
		load()->func('tpl');
		$articleid = intval($_GPC['articleid']);
		$articletypeid = intval($_GPC['articletypeid']);
		$articletypesql ="SELECT * FROM ".tablename($this->articletypetable)." WHERE uniacid = '{$_W['uniacid']}' and state = 2  ORDER BY indexno ";
		$articletypelist = pdo_fetchall($articletypesql);
		if($articleid > 0){
			$article = pdo_fetch("SELECT * FROM ".tablename($this->articletable)." WHERE id= '{$articleid}'");
			if (!$article) {
				message ( '抱歉，信息不存在或是已经删除！', '', 'error' );
			}
		}

		if($_GPC['op']=='delete'){
			$article = pdo_fetch ( "SELECT id FROM " . tablename ($this->articletable) . " WHERE id = '{$articleid}'" );
			if (empty ( $article['id'] )) {
				message ( '抱歉，信息不存在或是已经被删除！' );
			}
			pdo_delete ($this->articletable, array ('id' => $articleid) );
			message ( '删除成功！', referer (), 'success' );
		}

		if (checksubmit()) {
			$imgs = serialize($_GPC ['imgs']);
			$settop = intval($_GPC ['settop']);
			$articletypeid = intval($_GPC ['articletypeid']);
			if($articletypeid <= 0){
				message('articletypeid id is null');
			}
			$data = array(
					'title'   =>  $_GPC ['title'],
					'desc'   =>  $_GPC ['desc'],
					'img' 	  => $_GPC ['img'],
					'content' => htmlspecialchars_decode($_GPC ['content']),
					'articletypeid' => $articletypeid,
					'indexno'   => intval($_GPC ['indexno']),
					'state'   => intval($_GPC ['state']),
					'status'   => intval($_GPC ['status']),
					);
			if (!empty($articleid)) {
				pdo_update($this->articletable, $data, array('id' => $articleid));
			}else {

				$data['addtime'] = time();//date("Y-m-d H:i:s");
				$data['uniacid'] = $_W['uniacid'];
				pdo_insert($this->articletable, $data);
				$topicid = pdo_insertid();
			}
			$article = pdo_fetch ( "SELECT * FROM " . tablename ($this->articletable) . " WHERE id = '{$articleid}'" );
			$imgs = unserialize($article['imgs']);
			message('更新成功！', referer(), 'success');
		}
		include $this->template('addarticle');
	}



	public function getArticleListByTid($typeid,$limit){
		global $_W;
		$sql="SELECT * FROM ".tablename($this->articletable)."
					WHERE uniacid = '{$_W['uniacid']}' and articletypeid = '{$typeid}' ORDER BY indexno LIMIT ".$limit;
		$list = pdo_fetchall($sql);
		return $list;
	}

	public function getarticleSumByTid($typeid){
		global $_W;
		$result = pdo_fetch ( "SELECT count(*) as cnt FROM " . tablename ( $this->articletable ) . " WHERE articletypeid = '{$typeid}' and uniacid = '{$_W['uniacid']}'" );
		return $result ['cnt'] <= 0 ? 0 : $result ['cnt'];
	}


	/**
	 * 获取毫秒数
	 * @return number
	 */
	function getMillisecond() {
		list($t1, $t2) = explode(' ', microtime());
		$basecode =  (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
		return date("YmdHis").substr($basecode,2,10).mt_rand(1000,9999);
	}

	/**
	 * 获取Token值
	 * @return unknown
	 */
	public function getToken(){
		global $_W;
		load()->classs('weixin.account');
		$id = $_W['acid'];
		if(empty($id)){
			$id = $_W['uniacid'];
		}
		$accObj= WeixinAccount::create($id);
		$access_token = $accObj->fetch_token();
		return $access_token;
	}


	/**
	 * 发送模板消息
	 * $data为消息模板数组
	 */
	public function sendMBXX($access_token,$data){
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
		ihttp_post($url, json_encode($data));

	}



	/**
	 * 查询数据库获取粉丝信息
	 * @param unknown $openid
	 * @return unknown
	 */
	public function getFansDBInfo($openid){
		global $_W;
		$fans = pdo_fetch ( "SELECT * FROM " . tablename ( $this->fanstable ) . " WHERE uniacid = '{$_W['uniacid']}' and openid ='{$openid}'" );
		return $fans;
	}



}
