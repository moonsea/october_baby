<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
load()->model('app');
$dos = array('display', 'login_out');
$do = in_array($do, $dos) ? $do : 'display';
load()->func('tpl');
$card_setting = pdo_get('mc_card', array('uniacid' => $_W['uniacid']));
$uni_setting = pdo_get('uni_settings', array('uniacid' => $_W['uniacid']), array('exchange_enable'));
if($do == 'login_out') {
	$_SESSION = null;
	unset($_SESSION);
	//3、清楚客户端sessionid
	// if(isset($_COOKIE[session_name()]))
	// {
	//   setCookie(session_name());
	// //   setcookie($_W['config']['cookie']['pre'] . $key, '', $expire, $_W['config']['cookie']['path'], $_W['config']['cookie']['domain'], $secure, $httponly);
	// }
	// setCookie();
	//4、彻底销毁session
	session_destroy();
	// message($_COOKIE,'','error');
	// exit();
	// session_destroy();
	isetcookie('logout', 1, -315360000);
	$logoutjs = "<script language=\"javascript\" type=\"text/javascript\">window.location.href=\"" . url('entry',array('do'=>'my','m'=>'water_baby')) . "\";</script>";
	exit($logoutjs);

	// template('entry',array('a'=>'my','m'=>'water_baby'));
}

if ($do == 'display') {
	$navs = app_navs('profile');
	$modules = uni_modules();
	$groups = $others = array();
	if(!empty($navs)) {
		foreach($navs as $row) {
			if(!empty($row['module'])) {
				$groups[$row['module']][] = $row;
			} else {
				$others[] = $row;
			}
		}
	}

	$profile = mc_fetch($_W['member']['uid'], array('nickname', 'avatar', 'mobile', 'groupid'));
	$mcgroups = mc_groups();
	$profile['group'] = $mcgroups[$profile['groupid']];
	if(isset($card_setting['uc']['status']) && $card_setting['uc']['status'] == '1') {
		$uc = $card_setting['uc'];
		$sql = 'SELECT * FROM ' . tablename('mc_mapping_ucenter') . ' WHERE `uniacid`=:uniacid AND `uid`=:uid';
		$pars = array();
		$pars[':uniacid'] = $_W['uniacid'];
		$pars[':uid'] = $_W['member']['uid'];
		$mapping = pdo_fetch($sql, $pars);
		if(empty($mapping)) {

		} else {
			mc_init_uc();
			$u = uc_get_user($mapping['centeruid'], true);
			$ucUser = array(
				'uid' => $u[0],
				'username' => $u[1],
				'email' => $u[2]
			);
		}
	}
	if (empty($card_setting['passport']['focusreg'])) {
		$reregister = false;
		if ($_W['member']['email'] == md5($_W['openid']).'@we7.cc') {
			$reregister = true;
		}
	}
}

template('mc/home');
