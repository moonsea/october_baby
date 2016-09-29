<?php
/**
 *GO众筹
 */
defined('IN_IA') or exit('Access Denied');

class water_babyModuleProcessor extends WeModuleProcessor {
	public $themetable =  'water_baby_theme';
	public function respond() {
		global $_W, $_GPC;
		$content = trim($this->message ['content']);
		$reply = pdo_fetch("SELECT * FROM ".tablename($this->themetable)." WHERE uniacid = :uniacid and keyword = :keyword ", array(':uniacid' => $_W['uniacid'],':keyword' => $content));
		if(empty($reply)){
			return $this->respText ( '对不起，找不到活动记录，该活动已经结束或者被关闭，请联系管理员。');
		}else{
			$response = array (
					'title' => $reply ['tname'],
					'description' => $reply ['zcxcy'],
					'picurl' => $reply ['hlogo'],
					'url' => $this->buildSiteUrl ( $this->createMobileUrl ( 'KeyAdd', array ('themeid' => $reply [id]) ) )
			);
		}
		return $this->respNews($response);
	}
}