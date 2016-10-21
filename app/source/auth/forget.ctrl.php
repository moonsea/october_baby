<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
include_once 'aliyun-php-sdk-core/Config.php';
use Sms\Request\V20160927 as Sms;

$openid = $_W['openid'];
$dos = array('reset', 'forget', 'verifycode');
$setting = uni_setting($_W['uniacid'], array('uc'));
$uc_setting = $setting['uc'] ? $setting['uc'] : array();
// $forward = url('mc');
$forward = url('entry', array('do' => 'my','m' => 'water_baby'));

if(!empty($_GPC['forward'])) {
	$forward = './index.php?' . base64_decode($_GPC['forward']) . '#wechat_redirect';
}
if(!empty($_W['member']) && (!empty($_W['member']['mobile']) || !empty($_W['member']['email']))) {
	header('location: ' . $forward);
	exit;
}
if ($do == 'verifycode') {
	if($_W['ispost'] && $_W['isajax']) {
		$username = trim($_GPC['username']);
		$code = trim($_GPC['code']);
		load()->model('utility');
		if(!code_verify($_W['uniacid'], $username, $code)) {
			message('验证码错误', referer(), 'error');
		} else {
			pdo_delete('uni_verifycode', array('receiver' => $username));
			message('验证码正确', referer(), 'success');
		}
	}
}
if($do == 'reset') {
	if($_W['ispost'] && $_W['isajax']) {
		$username = trim($_GPC['username']);
		$password = trim($_GPC['password']);
		$repassword = trim($_GPC['repassword']);
		if ($repassword != $password) {
			message('密码输入不一致', referer(), 'error');
		}
		$sql = 'SELECT `uid`,`salt` FROM ' . tablename('mc_members') . ' WHERE `uniacid`=:uniacid';
		$pars = array();
		$pars[':uniacid'] = $_W['uniacid'];
		if(preg_match('/^\d{11}$/', $username)) {
			$type = 'mobile';
			$sql .= ' AND `mobile`=:mobile';
			$pars[':mobile'] = $username;
		} elseif(preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/", $username)) {
			$type = 'email';
			$sql .= ' AND `email`=:email';
			$pars[':email'] = $username;
		} else {
			message('用户名格式不正确', referer(), 'error');
		}
		$user = pdo_fetch($sql, $pars);
		if(empty($user)) {
			message('用户不存在', referer(), 'error');
		} else {
			$password = md5($password . $user['salt'] . $_W['config']['setting']['authkey']);
			pdo_update('mc_members', array('password' => $password), array('uniacid' => $_W['uniacid'], $type => $username));
		}
		message('找回成功', referer(), 'success');
	}
}
template('auth/forget');
exit;
