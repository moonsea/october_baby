<?php
/**
 * 
 *
 * @author dongyue
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class water_babyModule extends WeModule {
	
	/**
	 * 粉丝表
	 * @var unknown
	 */
	public $fanstable= 'water_baby_fans';
	
	public function settingsDisplay($system) {
		global $_W, $_GPC;
		load()->func('tpl');
		$founder = $_W['isfounder'];
		
		if(checksubmit()) {    
			
			

			load()->func('file');
			$_W['uploadsetting'] = array();
			$_W['uploadsetting']['image']['folder'] = 'images/' . $_W['uniacid'];
			$_W['setting']['upload']['image']['extentions'] = array_merge($_W['setting']['upload']['image']['extentions'],array("pem"));
			$_W['uploadsetting']['image']['limit'] = $_W['config']['upload']['image']['limit'];
				
			if (!empty($_FILES['apiclient_cert_file']['name'])) {
				$file = file_upload($_FILES['apiclient_cert_file']);
				if (is_error($file)) {
					message('apiclient_cert证书保存失败, 请保证目录可写'. $file['message']);
				} else {
					$_GPC['apiclient_cert']= empty($file['path'])? trim($_GPC['apiclient_cert']):ATTACHMENT_ROOT.$file['path'];
				}
			}
			
			if (!empty($_FILES['apiclient_key_file']['name'])) {
				$file = file_upload($_FILES['apiclient_key_file']);
				if (is_error($file)) {
					message('apiclient_key证书保存失败, 请保证目录可写'. $file['message']);
				} else {
					$_GPC['apiclient_key']= empty($file['path'])? trim($_GPC['apiclient_key']):ATTACHMENT_ROOT.$file['path'];
				}
			}
			
			if (!empty($_FILES['rootca_file']['name'])) {
				$file = file_upload($_FILES['rootca_file']);
				if (is_error($file)) {
					message('rootca证书保存失败, 请保证目录可写'. $file['message']);
				} else {
					$_GPC['rootca']= empty($file['path'])? trim($_GPC['rootca']):ATTACHMENT_ROOT.$file['path'];
				}
			}
   
            $input =array();
            $input['sysname'] = trim($_GPC['sysname']);
            $input['sysdesc'] = trim($_GPC['sysdesc']);
            
            $input['sysimg'] = trim($_GPC['sysimg']);
            $input['auth'] = intval(trim($_GPC['auth']));
            
            if($input['auth'] == 1){
	            $input['appid'] = trim($_GPC['appid']);
	            $input['secret'] = trim($_GPC['secret']);
	            $input['acid'] = trim($_GPC['acid']);
	            $input['mchid'] = trim($_GPC['mchid']);
	            $input['apikey'] = trim($_GPC['apikey']);
            	if(empty($input['appid']) || empty($input['secret'])|| empty($input['acid'])){
            		message('启用借权后，appid/secret/acid不可为空');
            	}
            	if( $input['isreward'] == 1 && (empty($input['mchid']) || empty( $input['apikey']))){
            		message('启用借权下的打赏时，借权的mchid/apikey不可为空');
            	}
            }else{
            	$appid = $_W ['account'] ['key'];
            	$secret = $_W ['account'] ['secret'];
            	$input['appid'] = $appid;
            	$input['secret'] = $secret;
            	$payment = pdo_fetch("SELECT payment FROM ".tablename('uni_settings')." WHERE uniacid= '{$_W['uniacid']}'");
            	$payment = unserialize($payment['payment']);
            	$input['mchid'] = $payment['wechat']['mchid'];
            	$input['apikey'] = $payment['wechat']['apikey'];
            }
            
            $input['badword'] = intval(trim($_GPC['badword']));
            $input['timespan'] = intval(trim($_GPC['timespan']));
            
            $input['isad'] = intval(trim($_GPC['isad']));
            $input['adlefttitle'] = trim($_GPC['adlefttitle']);
            $input['adrighttitle'] = trim($_GPC['adrighttitle']);
            $input['adleftimg'] = trim($_GPC['adleftimg']);
            $input['adlefturl'] = trim($_GPC['adlefturl']);
            $input['adrightimg'] = trim($_GPC['adrightimg']);
            $input['adrighturl'] = trim($_GPC['adrighturl']);
            if($input['isad'] == 1){
            	if(empty($input['adleftimg']) || empty($input['adrightimg'])){
	            	message('启用广告，左右两侧广告图不可为空');
            	}
            }
            
            $input['noteadd'] = intval(trim($_GPC['noteadd']));
            $input['issign'] = intval(trim($_GPC['issign']));
            $input['syssign'] = intval(trim($_GPC['syssign']));
            if($input['issign'] == 1 && $input['syssign'] == 0){
            	message('启用签到，赠送积分不可为0');
            }
            
            $input['isreward'] = intval(trim($_GPC['isreward']));
            $input['reward0'] = floatval(trim($_GPC['reward0']));
            $input['reward1'] = floatval(trim($_GPC['reward1']));
            $input['reward2'] = floatval(trim($_GPC['reward2']));
            $input['reward3'] = floatval(trim($_GPC['reward3']));
            $input['reward4'] = floatval(trim($_GPC['reward4']));
            $input['reward5'] = floatval(trim($_GPC['reward5']));
            
            $input['adminper'] = intval(trim($_GPC['adminper']));
            $input['rewardper'] = intval(trim($_GPC['rewardper']));
            $input['fansper'] = intval(trim($_GPC['fansper']));
            $sum = $input['adminper']+$input['rewardper']+$input['fansper'] ;
            if($sum != 100){
            	message('打赏的三部分的抽成之和必须为100');
            }
            
            $input['foundername'] = trim($_GPC['foundername']);
            if($input['foundername']){
            	$fans = pdo_fetch ( "SELECT id,openid,nickname,headimgurl FROM ".tablename ( $this->fanstable ).
            			" WHERE uniacid = '{$_W['uniacid']}' and nickname = '{$input['foundername']}'" );
            	if($fans){
            		$input['founderid'] = $fans['id'];
            		$input['founderopenid'] = $fans['openid'];
            		$input['foundername'] = $fans['nickname'];
            		$input['founderimg'] = $fans['headimgurl'];
            	}else{
            		message('找不到此昵称的用户信息，请通过微信进入模块之后再进行保存');
            	}
            }
            
            
            
            
            $input['apiclient_cert'] = trim($_GPC['apiclient_cert']);
            $input['apiclient_key'] = trim($_GPC['apiclient_key']);
            $input['rootca'] = trim($_GPC['rootca']);
            $input['ip'] = trim($_GPC['ip']);
            
            $input['noticeopen'] = trim($_GPC['noticeopen']);
            $input['notice'] = trim($_GPC['notice']);
            $input['noticetixian'] = trim($_GPC['noticetixian']);
            
            if($this->saveSettings($input)) {
                message('保存参数成功', 'refresh');
            }
        }

        if(empty($system['ip'])) {
        	$system['ip'] = $_SERVER['SERVER_ADDR'];
        }
        include $this->template('system');
	}


}