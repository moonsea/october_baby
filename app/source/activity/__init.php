<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
checkauth();
load()->model('activity');
load()->model('mc');
load()->classs('coupon');
$creditnames = array();
$unisettings = uni_setting($uniacid, array('creditnames', 'coupon_type'));
if (!empty($unisettings) && !empty($unisettings['creditnames'])) {
	foreach ($unisettings['creditnames'] as $key=>$credit) {
		$creditnames[$key] = $credit['title'];
	}
}

$cardstatus = pdo_get('mc_card', array('uniacid' => $_W['uniacid']), array('status'));
$coupon_api = new coupon($_W['acid']);
$is_coupon_supported = $coupon_api -> isCouponSupported();
if (!empty($is_coupon_supported)) {
	define('COUPON_TYPE', WECHAT_COUPON);
} else {
	define('COUPON_TYPE', SYSTEM_COUPON);
}
$type_names = activity_coupon_type_label();
