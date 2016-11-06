<?php

session_start();
/*
 * 十月宝宝
 * @author moonsea
 * 2016年05月12日08:32:25
 * QQ491024175
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');
class water_babyModuleSite extends WeModuleSite
{
    /**
     * 粉丝表.
     *
     */
    public $fanstable = 'water_obaby_fans';

    /**
     * 文章分类表.
     */
    public $articletypetable = 'water_obaby_articletype';

    /**
     * 文章表.
     */
    public $articletable = 'water_obaby_article';

    /**
     *支付订单表.
     */
    public $ordertable = 'water_obaby_order';

    /**
     *支付订单表.
     */
    public $logtable = 'water_obaby_log';

    /**
     * 律师id
     */
    public $LawerId = '91';

    /**
     * 首页.
     *
     * @return [type] [description]
     */
    public function doMobileindex()
    {
        global $_W,$_GPC;

        $groupid = $_W['member']['groupid'];

        $state = $groupid == '5'? '1':'2';

        /* 首页Banner */
        $sql = "SELECT * FROM ".tablename('water_baby_banner')." WHERE state = '$state' ORDER BY indexno LIMIT 4";
        $banner_list = pdo_fetchall($sql);

        /* 今日推荐文章列表 limit 4 */
        $tjsql = 'SELECT * FROM '.tablename($this->articletable)."
		WHERE uniacid = '{$_W['uniacid']}' and status = 2 and state = '$state' ORDER BY addtime desc LIMIT 4";
        $tjlist = pdo_fetchall($tjsql);

        $articletypesql = 'SELECT * FROM '.tablename($this->articletypetable)." WHERE uniacid = '{$_W['uniacid']}' and state = '$state'  ORDER BY indexno ";
        $articletypelist = pdo_fetchall($articletypesql);

        // load()->classs('weixin.account');
        // $id = $_W['acid'];
        // if (empty($id)) {
        //     $id = $_W['uniacid'];
        // }
        // $accObj = WeixinAccount::create($id);
        // $access_token = $accObj->fetch_token();
        $access_token = $this->getToken();
        if($_COOKIE['jsapi_ticket'])
    		{
    			$jsapi_ticket = $_COOKIE['jsapi_ticket'];
    		}
    		else
    		{
    			$tmp = json_decode(file_get_contents("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi"));
    			$jsapi_ticket = $tmp->ticket;
    			setcookie('jsapi_ticket',$jsapi_ticket,7000);
    			$_COOKIE['jsapi_ticket'] == $jsapi_ticket;
    		}
        $noncestr = 'Wm3WZYTPz0wzccnW';
        $timestamp = time();
        // $url = 'http://shiyue.october-baby.com/baby/app/index.php?i=4&c=entry&do=index&m=water_baby';
        $url = 'http://shiyue.october-baby.com/baby/app/index.php?'.$_SERVER['QUERY_STRING'];
        $string = "jsapi_ticket=$jsapi_ticket&noncestr=$noncestr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);

        include $this->template('index');
    }

    /**
     * 文章列表.
     *
     * @return [type] [description]
     */
    public function doMobileArticlelist()
    {
        global $_W,$_GPC;
        $condition = ' and 1=1 ';
        $status = $_GPC['status'];
        if (!empty($status)) {
            $condition .= ' and status = 2';
        }
        $articletypeid = intval($_GPC['articletypeid']);
        if ($articletypeid > 0) {
            $condition .= " and articletypeid = '{$articletypeid}'";
        }

        $groupid = $_W['member']['groupid'];
        $state = $groupid == '5'? '1':'2';

        $sql = 'SELECT * FROM '.tablename($this->articletable)."
					WHERE state = '$state' and uniacid = '{$_W['uniacid']}' {$condition} ORDER BY indexno LIMIT 100";
        $list = pdo_fetchall($sql);
        include $this->template('article-list');
    }

    /**
     * 文章内容.
     *
     * @return [type] [description]
     */
    public function doMobileArticle()
    {
        global $_W,$_GPC;

        $id = intval($_GPC['id']);
        if ($id <= 0) {
            message('id is null');
        }

        $auth_type = $_GPC['auth_type'];
        if (!is_null($auth_type)) {
            checkauth();
        }
        // checkauth();

        /* 文章详情 */
        $article = pdo_fetch('SELECT * FROM '.tablename($this->articletable)." WHERE id= '{$id}'");
        $article['addtime'] = date('Y-m-d', $article['addtime']);

        /* 收藏总数 */
        $fav_user_id = pdo_fetchall('SELECT user_id from '.tablename('water_baby_article_user')." WHERE article_id = '{$id}'");
        $fav_count = count($fav_user_id);
        $fav_status = '0';

        foreach ($fav_user_id as $item) {
            if ($item['user_id'] == $_W['member'][uid]) {
                $fav_status = '1';
                break;
            }
        }

        $access_token = $this->getToken();
        if($_COOKIE['jsapi_ticket'])
    		{
    			$jsapi_ticket = $_COOKIE['jsapi_ticket'];
    		}
    		else
    		{
    			$tmp = json_decode(file_get_contents("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi"));
    			$jsapi_ticket = $tmp->ticket;
    			setcookie('jsapi_ticket',$jsapi_ticket,7000);
    			$_COOKIE['jsapi_ticket'] == $jsapi_ticket;
    		}
        $noncestr = 'Wm3WZYTPz0wzccnW';
        $timestamp = time();
        // $url = 'http://shiyue.october-baby.com/baby/app/index.php?i=4&c=entry&do=index&m=water_baby';
        $url = 'http://shiyue.october-baby.com/baby/app/index.php?'.$_SERVER['QUERY_STRING'];
        $string = "jsapi_ticket=$jsapi_ticket&noncestr=$noncestr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);


        include $this->template('article');
    }

    /**
     * 收藏文章.
     *
     * @return [type] [description]
     */
    public function doMobileFavArticle()
    {
        global $_W,$_GPC;

        // checkauth();

        $user_id = $_W['member']['uid'];
        $article_id = $_GPC['article_id'];
        $url = $this->createMobileUrl('article', array('id' => $article_id, 'auth_type' => '1'));
        if (is_null($user_id)) {
            message('更新成功！', $url, 'success');
        }

        // $article_id = $_GPC['article_id'];
        $fav_status = $_GPC['fav_status'];

        $result = '';

        if ($fav_status == '1') {
            $result = pdo_delete('water_baby_article_user', array('article_id' => $article_id, 'user_id' => $user_id));
        } else {
            $result = pdo_insert('water_baby_article_user', array('article_id' => $article_id, 'user_id' => $user_id, 'fav_time' => time()));
        }
        if (!empty($result)) {
            message('更新成功！', referer(), 'success');
        } else {
            message('更新失败');
        }
    }

    /**
     * 聊天.
     * auth_type: 0: 不校验会话, 1:校验会话
     * @return [type] [description]
     */
    public function doMobileChat()
    {
        global $_W,$_GPC;

        checkauth();

        $auth_type = empty($_GPC['auth_type'])? '0':$_GPC['auth_type'];

        $user_id = $_W['member']['uid'];
        $doctor_id = empty($_SESSION['doctor_id']) ? $_GPC['doctor_id'] : $_SESSION['doctor_id'];
        unset($_SESSION['doctor_id']); /* 清除session */

        $type = $_GPC['type'];

        $table = '';
        $mem = '';
        $conv_table = '';
        if ($type == '0') {
            $table = 'mc_doctor_user';
            $mem = 'doctor_id';
            $conv_table = 'user_doctor_conv';
        } elseif ($type == '1') {
            $table = 'water_baby_law_user';
            $mem = 'lawer_id';
            $conv_table = 'water_baby_law_user_conv';
        }

        /* 医生信息 */
        $doctor_info = $this->getMemInfo($doctor_id);

        /* 用户信息 */
        $mem_info = $this->getMemInfo($user_id);

        /* 聊天信息 */
        $chat_info = pdo_get($table, array('user_id' => $user_id, $mem => $doctor_id));
        $conv_id = '';
        /* 医生聊天信息为空返回咨询界面 */
        if (empty($chat_info)) {
            if ($type == '1') {
                $conv_id = date('YmdHis', time()).mt_rand();
                pdo_insert($table, array('user_id' => $user_id, 'lawer_id' => $doctor_id, 'conv_id' => $conv_id));
            } else {
                include $this->template('consult');
                exit(0);
            }
        } else {
            $conv_id = $chat_info['conv_id'];
        }

        /* 会话列表 */
        $chat_list = pdo_fetchall('SELECT * FROM '.tablename($conv_table)." WHERE conv_id = '{$conv_id}' ORDER BY addtime DESC");

        $room_id = $_GPC['room_id'];
        if (empty($room_id)) {
            /* 创建room_id */
            $room_id = time();
            $conv_data = array('conv_id' => $conv_id, 'room_id' => $room_id, 'addtime' => time(),'expire' => '1');
			// $conv_data = array('conv_id' => $conv_id, 'room_id' => $room_id, 'addtime' => time());
            pdo_insert($conv_table, $conv_data);
        }

        /* 校验会话有效性 */
        if ($type == '0' && $auth_type == '1') {
            $info = pdo_get($conv_table, array('conv_id' => $conv_id, 'room_id' => $room_id));
            $expire = $info['expire'];
            if ($expire == '1') {
                $replytime = intval($info['replytime']);
                if (($replytime != 0) && (time() - $replytime > 21600)) {
                    pdo_update('user_doctor_conv', array('expire' => '0'), array('id' => $info['id']));
                    message('当前会话已经结束，请重新咨询！',$this->createMobileUrl('consult'),'success');
                }
            }
            else {
                message('当前会话已经结束，请重新咨询！',$this->createMobileUrl('consult'),'success');
            }
        }

        $access_token = $this->getToken();
        if($_COOKIE['jsapi_ticket'])
    		{
    			$jsapi_ticket = $_COOKIE['jsapi_ticket'];
    		}
    		else
    		{
    			$tmp = json_decode(file_get_contents("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi"));
    			$jsapi_ticket = $tmp->ticket;
    			setcookie('jsapi_ticket',$jsapi_ticket,7000);
    			$_COOKIE['jsapi_ticket'] == $jsapi_ticket;
    		}
        $noncestr = 'Wm3WZYTPz0wzccnW';
        $timestamp = time();
        // $url = "http://shiyue.october-baby.com/baby/app/index.php?i=4&c=entry&type=".$_GET['type']."&doctor_id=".$_GET['doctor_id']."&room_id=".$_GET['room_id']."&do=chat&m=water_baby";
        $url = "http://shiyue.october-baby.com/baby/app/index.php?".$_SERVER['QUERY_STRING'];
        $string = "jsapi_ticket=$jsapi_ticket&noncestr=$noncestr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        include $this->template('chat');
    }

    /**
     * 咨询会话列表.
     *
     * type:0:医生,1:律师
     *
     * @return [type] [description]
     */
    public function dotorChatList($type = '0')
    {
        global $_W,$_GPC;

        checkauth();

        $uid = $_W['member']['uid'];

        /* 获取会话列表 */
        $table = $type == '0' ? 'mc_doctor_user' : ($type == '1' ? 'water_baby_law_user' : '');
        $mem = $type == '0' ? 'doctor_id' : ($type == '1' ? 'lawer_id' : '');

        $conv_list = pdo_fetchall('SELECT * FROM '.tablename($table)." WHERE {$mem} = '{$uid}'");
        /* 获取医生/律师信息 */
        $user_info = $this->getMemInfo($uid);

        /* 获取孕妇信息 */
        for ($i = 0; $i < count($conv_list); ++$i) {
            $conv_list[$i]['mem_info'] = $this->getMemInfo($conv_list[$i]['user_id']);
        }

        include $this->template('chat_list');
    }

    /**
     * 医生/律师聊天.
     * 0:医生
     * 1:律师
     * @return [type] [description]
     */
    public function doMobileDoctorChat()
    {
        global $_W,$_GPC;

        checkauth();

        $conv_id = $_GPC['conv_id'];
        $type = $_GPC['type'];

        $table = '';
        $mem = '';
        $conv_table = '';
        if ($type == '0') {
            $table = 'mc_doctor_user';
            $mem = 'doctor_id';
            $conv_table = 'user_doctor_conv';
        } elseif ($type == '1') {
            $table = 'water_baby_law_user';
            $mem = 'lawer_id';
            $conv_table = 'water_baby_law_user_conv';
        }

        /* 聊天信息 */
        $conv_info = pdo_get($table, array('conv_id' => $conv_id));

        /* 获取医生/律师信息 */
        $user_info = $this->getMemInfo($conv_info[$mem]);

        /* 孕妇信息 */
        $mem_info = $this->getMemInfo($conv_info['user_id']);

        /* 会话列表 */
        $chat_list = pdo_fetchall('SELECT * FROM '.tablename($conv_table)." WHERE conv_id = '{$conv_id}' ORDER BY addtime DESC");

        $chat_info = '';
        $room_id = '';
        if (empty($_GPC['room_id'])) {
            /* 最新会话信息 */
            $chat_info = $chat_list[0];
            $room_id = $chat_info['room_id'];
        }
        else {
            $room_id = $_GPC['room_id'];
            $chat_info = pdo_get($conv_table, array('conv_id' => $conv_id, 'room_id' => $room_id));
        }
        $replytime = intval($chat_info['replytime']);

        $access_token = $this->getToken();
        if($_COOKIE['jsapi_ticket'])
    		{
    			$jsapi_ticket = $_COOKIE['jsapi_ticket'];
    		}
    		else
    		{
    			$tmp = json_decode(file_get_contents("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi"));
    			$jsapi_ticket = $tmp->ticket;
    			setcookie('jsapi_ticket',$jsapi_ticket,7000);
    			$_COOKIE['jsapi_ticket'] == $jsapi_ticket;
    		}
        $noncestr = 'Wm3WZYTPz0wzccnW';
        $timestamp = time();
        // $url = "http://shiyue.october-baby.com/baby/app/index.php?i=4&c=entry&conv_id=".$_GET['conv_id']."&type=".$_GET['type']."&do=DoctorChat&m=water_baby";
        $url = "http://shiyue.october-baby.com/baby/app/index.php?".$_SERVER['QUERY_STRING'];
        $string = "jsapi_ticket=$jsapi_ticket&noncestr=$noncestr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);

        include $this->template('doctor_chat');
    }

    /**
     * 更新医生回复时间
     * @return [type] [description]
     */
    public function doMobileupdateReplyTime()
    {
        global $_W,$_GPC;

        checkauth();


        $id = $_GPC['id'];
        $replytime = time();

        $result = pdo_update('user_doctor_conv', array('replytime' => $replytime), array('id' => $id));

        if (!empty($result)) {
            message($replytime, '', 'success');
        } else {
            message('更新失败');
        }
    }

    /**
     * 医生结束会话
     * @return [type] [description]
     */
    public function doMobileEndChat()
    {
        global $_W,$_GPC;

        checkauth();

        $user_id = $_GPC['user_id'];
        $uid = $_W['member']['uid'];

        $conv_info = pdo_get('mc_doctor_user', array('user_id' => $user_id, 'doctor_id' => $doctor_id));
        $conv_id = $conv_info['conv_id'];

        $chat_info = pdo_fetch("SELECT * FROM ".tablename('user_doctor_conv'." WHERE conv_id = '$conv_id' ORDER BY addtime DESC LIMIT 1"));

        $result = pdo_query("UPDATE ".tablename('user_doctor_conv')." SET expire = '1' WHERE id = :id", array(':id' => $chat_info['id']));

        if (!empty($result)) {
            message("更新成功", '', 'success');
        } else {
            message('更新失败');
        }
    }

    /**
     * 咨询.
     *
     * @return [type] [description]
     */
    public function doMobileconsult()
    {
        global $_W,$_GPC;

        checkauth();

        $groupid = $_W['member']['groupid'];
        /*  医生咨询列表 */
        if ($groupid == '5') {
            $this->dotorChatList('0');
            exit();
        }
        /* 律师咨询列表 */
        if ($groupid == '7') {
            $this->dotorChatList('1');
            exit();
        }

        $uid = $_W['member']['uid'];

        /* 咨询关系信息 */
        $consult = pdo_get('mc_doctor_user', array('user_id' => $uid));

        /* 医生信息 */
        $mem_doctor = $this->getMemInfo($consult['doctor_id']);

        /* 律师信息 */
        $lawer_id = $this->LawerId; /* 固定 */
        $lawer_info = $this->getMemInfo($lawer_id);

        // $_SESSION['doctor_id'] = $consult['doctor_id']; /* 仅用作测试 */

        include $this->template('consult');
    }

    /**
     * 咨询列表.
     *
     * @return [type] [description]
     */
    public function doMobileexplain()
    {
        global $_W,$_GPC;

        checkauth();

        $user_id = $_W['member']['uid'];
        $groupid = $_W['member']['groupid'];

        /* 医生列/律师列表 */
        if ($groupid == '5' || $groupid == '7') {
            $this->explainList();
            exit(0);
        }

        /* 孕妇列表 */

        /* 医生会话信息 */
        $conv_doctor = pdo_get('mc_doctor_user', array('user_id' => $user_id));

        /* 获取医生信息 */
        $doctor_info = $this->getMemInfo($conv_doctor['doctor_id']);

        /* 律师会话信息 */
        $conv_lawer = pdo_get('water_baby_law_user', array('user_id' => $user_id));

        /* 获取医生信息 */
        $lawer_info = $this->getMemInfo($conv_lawer['lawer_id']);

        $show_type = 'conv_type';

        include $this->template('explain_type');
    }

    /**
     * 查看会话列表
     * type: 0:查看医生,1:查看律师, 2:查看孕妇.
     *
     * @return [type] [description]
     */
    public function doMobileExplainList()
    {
        global $_W, $_GPC;

        checkauth();

        $type = empty($_GPC['type']) ? '0' : $_GPC['type'];

        $table = '';
        $conv_table = '';
        $mem = '';
        $mem_id = '';
        if ($type == '0') {
            $table = 'mc_doctor_user';
            $conv_table = 'user_doctor_conv';
            $mem = 'doctor_id';
            $mem_id = $_GPC['doctor_id'];
        } elseif ($type == '1') {
            $table = 'water_baby_law_user';
            $conv_table = 'water_baby_law_user_conv';
            $mem = 'lawer_id';
            $mem_id = $_GPC['lawer_id'];
        } elseif ($type == '2') {
            $groupid = $_W['member']['groupid'];
            $mem = 'user_id';
            $mem_id = $_GPC['user_id'];

            if ($groupid == '5') {
                $table = 'mc_doctor_user';
                $conv_table = 'user_doctor_conv';
            } elseif ($groupid == '7') {
                $table = 'water_baby_law_user';
                $conv_table = 'water_baby_law_user_conv';
            }
        }

        $conv_id = $_GPC['conv_id'];
        $price = empty($_GPC['price']) ? '0' : $_GPC['price'];

        /* 获取医生/律师/孕妇信息 */
        $mem_info = $this->getMemInfo($mem_id);

        /* 获取会话列表 */
        $chat_list = pdo_fetchall('SELECT * FROM '.tablename($conv_table)." WHERE conv_id = '{$conv_id}'");

        include $this->template('explain_list');
    }

    /**
     * 医生/律师查看与孕妇会话列表.
     *
     * @param string $type [description]
     *
     * @return [type] [description]
     */
    public function explainList()
    {
        global $_W, $_GPC;

        $mem_id = $_W['member']['uid'];
        $groupid = $_W['member']['groupid'];

        $table = '';
        $conv_table = '';
        $mem = '';
        if ($groupid == '5') {
            $table = 'mc_doctor_user';
            $conv_table = 'user_doctor_conv';
            $mem = 'doctor_id';
        } elseif ($groupid == '7') {
            $table = 'water_baby_law_user';
            $conv_table = 'water_baby_law_user_conv';
            $mem = 'lawer_id';
        }

        /* 获取会话列表 */
        $conv_list = pdo_fetchall('SELECT * FROM '.tablename($table)."WHERE {$mem} = '{$mem_id}'");

        /* 获取孕妇信息 */
        for ($i = 0; $i < count($conv_list); ++$i) {
            $conv_list[$i]['user_info'] = $this->getMemInfo($conv_list[$i]['user_id']);
        }

        /* 获取用户信息 */
        $user_info = $this->getMemInfo($_W['member']['uid']);

        $show_type = 'conv_list';

        include $this->template('explain_type');
    }

    /**
     * 会话详细信息.
     * type: 0:医生, 1:律师.
     *
     * @return [type] [description]
     */
    public function doMobileShowConvDetail()
    {
        global $_W, $_GPC;

        checkauth();

        $mem_id = $_GPC['mem_id'];

        /* 获取医生/律师信息 */
        $mem_info = $this->getMemInfo($mem_id);

        /* 获取用户信息 */
        $user_info = $this->getMemInfo($_W['member']['uid']);

        $conv_id = $_GPC['conv_id'];
        $room_id = $_GPC['room_id'];

        include $this->template('conv_detail');
    }

    /**
     * 获取用户信息.
     *
     * @param [type] $user_id [description]
     *
     * @return [type] [description]
     */
    public function getMemInfo($user_id)
    {
        $sql = 'SELECT * FROM '.tablename('mc_members')." WHERE uid = '{$user_id}'";
        $mem_info = pdo_fetch($sql);

        return $mem_info;
    }

    /**
     * 收藏列表.
     *
     * @return [type] [description]
     */
    public function doMobilefavorite()
    {
        global $_W,$_GPC;

        checkauth();

        $uid = $_W['member']['uid'];

        /* 文章id列表 */
        $sql = 'SELECT * FROM '.tablename('water_baby_article_user')." WHERE user_id = '{$uid}'";
        $article_list = pdo_fetchall($sql);

        /* 文章内容 */
        for ($i = 0; $i < count($article_list); ++$i) {

            /* 文章详情 */
            $article_list[$i]['article'] = $this->getArticleDetail($article_list[$i]['article_id']);

            /* 收藏总数 */
            $article_list[$i]['fav_count'] = $this->getArticleFavCount($article_list[$i]['article_id']);
        }

        include $this->template('favorite');
    }

    /**
     * 获取文章内容.
     *
     * @param [type] $article_id [description]
     *
     * @return [type] [description]
     */
    public function getArticleDetail($article_id)
    {
        /* 文章详情 */
        $article = pdo_fetch('SELECT * FROM '.tablename($this->articletable)." WHERE id= '{$article_id}'");
        $article['addtime'] = date('Y-m-d', $article['addtime']);

        return $article;
    }

    /**
     * 获取文章关注总数.
     *
     * @param [type] $article_id [description]
     *
     * @return [type] [description]
     */
    public function getArticleFavCount($article_id)
    {
        $fav_user_id = pdo_fetchall('SELECT user_id from '.tablename('water_baby_article_user')." WHERE article_id = '{$article_id}'");
        $fav_count = count($fav_user_id);

        return $fav_count;
    }

    /**
     * 我的.
     *
     * @return [type] [description]
     */
    public function doMobilemy()
    {
        global $_W,$_GPC;

        /*校验登录*/
        checkauth();

        $uid = $_W['member']['uid'];

        /* 获取个人信息 */
        $mem_info = pdo_get('mc_members', array('uid' => $uid));

        /* 获取医生相关信息 */
        $mem_info['hosp_name'] = pdo_get('mc_hospital', array('hosp_id' => $mem_info['hosp_id']))['hosp_name'];
        $mem_info['dep_name'] = pdo_get('mc_hosp_dep', array('dep_id' => $mem_info['dep_id']))['dep_name'];
        $mem_info['title_name'] = pdo_get('mc_hosp_title', array('title_id' => $mem_info['title_id']))['title_name'];

        /* 孕妇相关信息 */
        $mem_info['baby_birthday'] = $mem_info['baby_birthday'] == null ? date('Y/m/d', 0) : date('Y/m/d', $mem_info['baby_birthday']);

        include $this->template('mine-docotor');
    }

    /**
     * 更新baby_birthday.
     *
     * @return [type] [description]
     */
    public function doMobileBabyBirthUpdate()
    {
        global $_W,$_GPC;

        checkauth();

        $uid = $_W['member']['uid'];

        $baby_birthday = $_GPC['baby_birthday'];
        $baby_birthday = strtotime($baby_birthday);

        $result = pdo_update('mc_members', array('baby_birthday' => $baby_birthday), array('uid' => $uid));

        if (!empty($result)) {
            message('更新成功！', referer(), 'success');
        } else {
            message('更新失败');
        }
    }

    /**
     * 注册身份选择.
     *
     * @return [type] [description]
     */
    public function doMobileRegistType()
    {
        global $_W,$_GPC;

        checkauth();

        include $this->template('regist-type');
    }

    /**
     * 注册身份更新.
     *
     * @return [type] [description]
     */
    public function doMobileRegTypeUpdate()
    {
        global $_W,$_GPC;

        checkauth();

        $uid = $_W['member']['uid'];
        $user_type = $_GPC['reg_type'];

        $result = pdo_update('mc_members', array('groupid' => $user_type), array('uid' => $uid));

        $url = '';
        if ($user_type == '5') {
            $url = $this->createMobileUrl('DoctorStep1');
        } else {
            $url = $this->createMobileUrl('PregType');
        }

        if (!empty($result)) {
            message('身份信息更新成功！', $url, 'success');
        } else {
            message('身份更新失败');
        }
    }

    /**
     * 上传孕检结果照片.
     *
     * @return [type] [description]
     */
    public function doMobileupload()
    {
        global $_W,$_GPC;

        checkauth();

        $uid = empty($_GPC['user_id'])?$_W['member']['uid']:$_GPC['user_id'];

        /* 获取个人信息 */
        $mem_info = pdo_get('mc_members', array('uid' => $uid));

        /* 获取个人状态列表 */
        $sql = 'SELECT * FROM '.tablename('mc_moment')."
					WHERE uid = '".$uid."' ORDER BY upload_time DESC";
        $moment_list = pdo_fetchall($sql);

        /* 解析时间 */
        for ($i = 0; $i < count($moment_list); ++$i) {
            $moment_list[$i]['day'] = date('d', $moment_list[$i]['upload_time']);
            $moment_list[$i]['month'] = date('m', $moment_list[$i]['upload_time']);
            $moment_list[$i]['year'] = date('Y', $moment_list[$i]['upload_time']);
            $moment_list[$i]['pic_list'] = $this->getMomPic($moment_list[$i]['id']);
        }

        include $this->template('upload');
    }

    /**
     * 获取图片列表.
     *
     * @param [type] $moment_id [description]
     *
     * @return [type] [description]
     */
    public function getMomPic($moment_id)
    {
        /* 获取每条状态的图片列表 */
        $sql = 'SELECT * FROM '.tablename('mc_mom_pic')."
					WHERE moment_id = '".$moment_id."' ORDER BY pic_id";
        $pic_list = pdo_fetchall($sql);

        return $pic_list;
    }

    /**
     * 显示检查结果详情.
     *
     * @return [type] [description]
     */
    public function doMobileShowMomDetail()
    {
        global $_W,$_GPC;

        checkauth();

        $uid = $_W['member']['uid'];
        $moment_id = $_GPC['moment_id'];

        /* 获取个人信息 */
        $mem_info = pdo_get('mc_members', array('uid' => $uid));

        /* 获取状态信息 */
        $mom_info = pdo_get('mc_moment', array('uid' => $uid, 'id' => $moment_id));

        /* 状态图片列表 */
        $pic_list = $this->getMomPic($moment_id);

        include $this->template('moment_detail');
    }

    /**
     * 拍照上传.
     *
     * @return [type] [description]
     */
    public function doMobileDoUpload()
    {
        global $_W,$_GPC;

        checkauth();
        // $APPID = "wxee3496c4bd4556c0";
        // $APPSECRET = "12611564db5e70ae66c4e39b56e8562a";
        // $tmp = file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$APPID&secret=$APPSECRET");
        global $_W;
        load()->classs('weixin.account');
        $id = $_W['acid'];
        if (empty($id)) {
            $id = $_W['uniacid'];
        }
        $accObj = WeixinAccount::create($id);
        $access_token = $accObj->fetch_token();
        // var_dump($access_token);
		if($_COOKIE['jsapi_ticket'])
		{
			$jsapi_ticket = $_COOKIE['jsapi_ticket'];
		}
		else
		{
			$tmp = json_decode(file_get_contents("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi"));
			$jsapi_ticket = $tmp->ticket;
			setcookie('jsapi_ticket',$jsapi_ticket,7000);
			$_COOKIE['jsapi_ticket'] == $jsapi_ticket;
		}
        $noncestr = 'Wm3WZYTPz0wzccnW';
        $timestamp = time();
        $url = 'http://shiyue.october-baby.com/baby/app/index.php?i=4&c=entry&do=doupload&m=water_baby';
        $string = "jsapi_ticket=$jsapi_ticket&noncestr=$noncestr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);

        include $this->template('do_upload');
    }

    public function doMobileSaveImage()
    {
        global $_W,$_GPC;

        checkauth();

        $media_id = $_GPC['media_id'];

        $img_src = $this->saveImage($media_id,'chat');

        $img_src = 'http://shiyue.october-baby.com/baby'.substr($img_src,2);

        echo $img_src;
    }

    /**
     * 保存用户上传图片，并返回本地保存链接.
     *
     * @return [type] [description]
     */
    public function saveImage($media_id, $upload_path = 'upload')
    {
        if ($media_id == '' || empty($media_id)) {
            return null;
        }

        $access_token = $this->getToken();

        $url = 'http://file.api.weixin.qq.com/cgi-bin/media/get?';
        $url .= 'access_token='.$access_token;
        $url .= '&media_id='.$media_id;

        $save_dir = '../attachment/images/'.$upload_path.'/';
        $filename = date('YmdHis', time()).mt_rand().'.jpg';

        $res = $this->getFile($url, $save_dir, $filename, 1);

        // var_dump($res);
        return $res;
    }

    /**
     * 下载并保存文件.
     *
     * @param [type] $url      [description]
     * @param string $save_dir [description]
     * @param string $filename [description]
     * @param int    $type     [description]
     *
     * @return [type] [description]
     */
    public function getFile($url, $save_dir = '', $filename = '', $type = 0)
    {
        if (trim($url) == '') {
            return false;
        }
        if (trim($save_dir) == '') {
            $save_dir = './';
        }
        if (0 !== strrpos($save_dir, '/')) {
            $save_dir .= '/';
        }

        //创建保存目录
        if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
            return false;
        }

        //获取远程文件所采用的方法
        if ($type) {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $content = curl_exec($ch);
            curl_close($ch);
        } else {
            ob_start();
            readfile($url);
            $content = ob_get_contents();
            ob_end_clean();
        }
        $size = strlen($content);

        //文件大小
        $fp2 = @fopen($save_dir.$filename, 'a');
        fwrite($fp2, $content);
        fclose($fp2);
        unset($content, $url);

        // return array(
        //     'file_name' => $filename,
        //     'save_path' => $save_dir.$filename,
        // );
        return $save_dir.$filename;
    }

    /**
     * 多文件上传.
     *
     * @return [type] [description]
     */
    public function doMobileMultiUpload()
    {
        global $_W,$_GPC;

        checkauth();

        $mom_con = $_GPC['info'];

        $media = $_GPC['media_id'];
        $media_list = preg_split('/,/', $media);

        if ($mom_con != '') {

            /* 插入检查结果 */
            $mom_data = array(
                'uid' => $_W['member']['uid'],
                'content' => $_GPC['info'],
                'upload_time' => time(),
            );
            $result = pdo_insert('mc_moment', $mom_data);

            $mom_id = '';
            if (!empty($result)) {
                $mom_id = pdo_insertid();
            }

            // $img = $_FILES['check_result'];
            if (!empty($media_list) && $mom_id != '') {
                // echo 'test';

                foreach ($media_list as $media_id) {
                    if (!empty($media_id)) {
                        $res = $this->saveImage($media_id);
                        if (!is_null($res)) {
                            // $pic_url = $res['save_path'];
                            // print_r($pic_url);
                            pdo_insert('mc_mom_pic', array('pic_url' => $res, 'moment_id' => $mom_id));
                        }
                    }
                }
            }

            $_GPC['info'] = '';
            $_GPC['media_id'] = '';
        }

        message('', $this->createMobileUrl('upload'), 'success');
    }

    /**
     * 检测会话是否有效
     * @param  [type] $user_id   [description]
     * @param  [type] $doctor_id [description]
     * @return [type]            [description]
     */
    public function checkChatExpir($user_id, $doctor_id)
    {
        global $_w, $_GPC;

        $chat_info = pdo_get('mc_doctor_user', array('user_id' => $user_id, 'doctor_id' => $doctor_id));
        $conv_id = $chat_info['conv_id'];
        $sql = "SELECT * FROM ".tablename('user_doctor_conv')." WHERE conv_id = '$conv_id' ORDER BY addtime DESC limit 1";
        $chat_info = pdo_fetch($sql);
        if (!empty($chat_info)) {
            $expire = $chat_info['expire'];
            if ($expire == '1') {
                $replytime = intval($chat_info['replytime']);
                if (($replytime == 0) || (time() - $replytime <= 21600)) {
                    message('上次会话未结束，可继续咨询！',$this->createMobileUrl('chat', array('type' => '0', 'doctor_id' => $doctor_id, 'room_id' => $chat_info['room_id'])),'success');
                }
                else {
                    pdo_update('user_doctor_conv', array('expire' => '0'), array('id' => $chat_info['id']));
                }
            }
        }
    }

    /**
     * 微信支付.
     *
     * @return [type] [description]
     */
    public function doMobilePay()
    {
        global $_W,$_GPC;
        //var_dump($_W);
        checkauth();

        $uid = $_W['member']['uid'];
        $doctor_id = $_GPC['doctor_id'];

        /* 是否有有效会话 */
        $this->checkChatExpir($uid, $doctor_id);

        //获取用户要充值的金额数
        // $money = pdo_get('mc_doctor_user', array('user_id' => $uid, 'doctor_id' => $doctor_id));
        $money = pdo_get('mc_members', array('uid' => $doctor_id));
        $fee = floatval($money['revenue']);

        if ($fee <= 0) {
            message('支付错误, 金额小于0');
        }

        /*随机生成订单号*/
        srand((float) microtime() * 1000000);
        $ordersn = rand();

        //构造支付请求中的参数
        $params = array(
            'title' => '咨询费',          //收银台中显示的标题
            'ordersn' => $ordersn, //time(),//date('Ymdhis'), //$chargerecord['tid'],  //收银台中显示的订单号
            'tid' => date('Ymdhis'), //$chargerecord['tid'],      //充值模块中的订单号，此号码用于业务模块中区分订单，交易的识别码
            'fee' => $fee,      //收银台中显示需要支付的金额,只能大于 0
            // 'user' => $_W['member']['uid'],     //付款用户, 付款的用户名(选填项)
            'user' => $doctor_id,     //付款用户, 付款的用户名(选填项)
        );

        $_SESSION['doctor_id'] = $doctor_id;
        // exit(0);
        //调用pay方法
        $this->pay($params);
    }

    /**
     * 支付结果.
     */
    //该代码片断在/framework/builtin/recharge/site.php中
    public function payResult($params)
    {
        // message('支付成功！', url('entry', array('do' => 'chat','m' => 'water_baby')), 'success');
        //一些业务代码
        //根据参数params中的result来判断支付是否成功
        if ($params['result'] == 'success' && $params['from'] == 'notify') {
            if ($params['result'] == 'success') {
                message('支付成功！', url('entry', array('do' => 'chat', 'm' => 'water_baby', 'type' => '0')), 'success');
            } else {
                message('支付失败！', url('entry', array('do' => 'consult', 'm' => 'water_baby')), 'error');
            }
        }
        //因为支付完成通知有两种方式 notify，return,notify为后台通知,return为前台通知，需要给用户展示提示信息
        //return做为通知是不稳定的，用户很可能直接关闭页面，所以状态变更以notify为准
        //如果消息是用户直接返回（非通知），则提示一个付款成功
        if ($params['from'] == 'return') {
            if ($params['result'] == 'success') {
                message('支付成功！', url('entry', array('do' => 'chat', 'm' => 'water_baby', 'type' => '0')), 'success');
            } else {
                message('支付失败！', url('entry', array('do' => 'consult', 'm' => 'water_baby')), 'error');
            }
        }
    }

     /**
      * 医院选择.
      */
     public function doMobileDoctorStep1()
     {
         global $_W,$_GPC;

         checkauth();

         /* 医院列表 */
         $sql = 'SELECT * FROM '.tablename('mc_hospital').' ORDER BY hosp_id';
         $hosp_list = pdo_fetchall($sql);

         /* 用户选择 */
         $mem = pdo_get('mc_members', array('uid' => $_W['member']['uid']), array('hosp_id'));

         include $this->template('doctor-step1');
     }

     /**
      * 更新医生所属医院.
      *
      * @return [type] [description]
      */
     public function doMobileDoctorHospUpdate()
     {
         global $_W,$_GPC;

         checkauth();

         $url = $this->createMobileUrl('DoctorStep2');

         $hosp_id = $_GPC['hosp_id'];
         $old_hosp_id = $_GPC['old_hosp_id'];

         if ($hosp_id == $old_hosp_id) {
             message('更新成功！', $url, 'success');
         }

         $uid = $_W['member']['uid'];

         $_SESSION['hosp_id'] = $hosp_id;

         message('更新成功！', $url, 'success');
     }

     /**
      * 科室选择.
      *
      * @return [type] [description]
      */
     public function doMobileDoctorStep2()
     {
         global $_W,$_GPC;

         checkauth();

         /* 科室列表 */
         $sql = 'SELECT * FROM '.tablename('mc_hosp_dep').' ORDER BY dep_id';
         $dep_list = pdo_fetchall($sql);

         /* 用户选择 */
         $mem = pdo_get('mc_members', array('uid' => $_W['member']['uid']), array('dep_id'));

         include $this->template('doctor-step2');
     }

     /**
      * 更新医生科室选择.
      *
      * @return [type] [description]
      */
     public function doMobileDoctorDepUpdate()
     {
         global $_W,$_GPC;

         $url = $this->createMobileUrl('DoctorStep3');

         $dep_id = $_GPC['dep_id'];
         $old_dep_id = $_GPC['old_dep_id'];

        //  if ($dep_id == $old_dep_id) {
        //      message('更新成功！', $url, 'success');
        //  }

         $_SESSION['dep_id'] = $dep_id;
         message($_SESSION['dep_id'], $url, 'success');
     }

     /**
      * 职称选择.
      *
      * @return [type] [description]
      */
     public function doMobileDoctorStep3()
     {
         global $_W,$_GPC;

         checkauth();

         /* 职称列表 */
        $sql = 'SELECT * FROM '.tablename('mc_hosp_title').' ORDER BY title_id';
        $title_list = pdo_fetchall($sql);

        /* 用户选择 */
        $mem = pdo_get('mc_members', array('uid' => $_W['member']['uid']), array('title_id'));

         include $this->template('doctor-step3');
     }

     /**
      * 完成注册更新.
      *
      * @return [type] [description]
      */
     public function doMobileDoctorTitleUpdate()
     {
         global $_W,$_GPC;

         $url = $this->createMobileUrl('my');

         $title_id = $_GPC['title_id'];
         $old_title_id = $_GPC['old_title_id'];

        //  if ($title_id == $old_title_id) {
        //      message('更新成功！', $url, 'success');
        //  }

         $uid = $_W['member']['uid'];

         $hosp_id = $_SESSION['hosp_id'];
         unset($_SESSION['hosp_id']);

         $dep_id = $_SESSION['dep_id'];
         unset($_SESSION['dep_id']);

         $result = pdo_update('mc_members', array('groupid' => '5', 'hosp_id' => $hosp_id, 'dep_id' => $dep_id, 'title_id' => $title_id), array('uid' => $uid));

         if (!empty($result)) {
             message('更新成功！', $url, 'success');
         } else {
             message('更新失败');
         }
     }

     /**
      * 孕妇状态
      *
      * @return [type] [description]
      */
     public function doMobilePregType()
     {
         global $_W,$_GPC;

         checkauth();

         /* 用户选择 */
         $user = pdo_get('mc_members', array('uid' => $_W['member']['uid']), array('preg_type'));

         include $this->template('pregnant-type');
     }

     /**
      * 更改孕妇状态
      *
      * @return [type] [description]
      */
     public function doMobilePregTypeUpdate()
     {
         global $_W,$_GPC;

         $preg_type = $_GPC['preg_type'];
         $old_preg_type = $_GPC['old_preg_type'];

         if ($preg_type == $old_preg_type) {
             if ($preg_type == '0') {
                 message('更新成功！', $this->createMobileUrl('prepare'), 'success');
             }
             else {
                 message('更新成功！', $this->createMobileUrl('my'), 'success');
             }

         }

         $_SESSION['preg_type'] = $preg_type;

         $url = '';
         if ($preg_type == '0') {
             $url = $this->createMobileUrl('prepare');
         } else {
             pdo_update('mc_members', array('groupid' => '4', 'preg_type' => $preg_type), array('uid' => $_W['member']['uid']));
             unset($_SESSION['preg_type']);
             $url = $this->createMobileUrl('my');
         }
         message('身份更新成功！', $url, 'success');
     }

     /**
      * 许愿
      */
     public function doMobilePrepare()
     {
         global $_W,$_GPC;

         checkauth();

         /* 用户选择 */
         $user = pdo_get('mc_members', array('uid' => $_W['member']['uid']), array('baby_sex'));

         include $this->template('prepare');
     }

     /**
      * 更新宝宝性别.
      *
      * @return [type] [description]
      */
     public function doMobileBabySexUpdate()
     {
         global $_W,$_GPC;

         $url = $this->createMobileUrl('my');

         $baby_sex = $_GPC['sex_type'];
         $old_bay_sex = $_GPC['old_sex_type'];

        //  if ($baby_sex == $old_bay_sex) {
        //      message('更新成功！', $url, 'success');
        //  }

         $preg_type = $_SESSION['preg_type'];
         unset($_SESSION['preg_type']);

         $uid = $_W['member']['uid'];
         $update_data = array('groupid' => '4', 'preg_type' => $preg_type, 'baby_sex' => $baby_sex);
        //  message($update_data, $url, 'success');

         $result = pdo_update('mc_members', array('groupid' => '4', 'preg_type' => $preg_type, 'baby_sex' => $baby_sex), array('uid' => $uid));

         if (!empty($result)) {
             message($result, $url, 'success');
         } else {
             message($result);
         }
     }

    /**
     * update self discription.
     *
     * @return [type] [description]
     */
    public function doMobileBioUpdate()
    {
        global $_W,$_GPC;

        checkauth();

        $uid = $_W['member']['uid'];
        $bio = $_GPC['bio'];

        $result = pdo_update('mc_members', array('bio' => $bio), array('uid' => $uid));

        if (!empty($result)) {
            message('更新成功！', referer(), 'success');
        } else {
            message('更新失败');
        }
    }

    /**
     * 关于十月.
     */
    public function doMobileAbout()
    {
        global $_W,$_GPC;

        $article_info = pdo_get('water_baby_about');

        include $this->template('about');
    }

    /**
     * 孕妇与医生建立关系页面
     * http://shiyue.october-baby.com/baby/app/index.php?i=4&c=entry&do=relation&m=water_baby&doctor_id=50
     * @return [type] [description]
     */
    public function doMobileRelation()
    {
        global $_W, $_GPC;

        checkauth();

        $user_id = $_W['member']['uid'];
        $doctor_id = $_GPC['doctor_id'];

        $mem_doctor = $this->getMemInfo($doctor_id);

        include $this->template('relation');
    }

    /**
     * 建立关系
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function doMobileBuildRelation()
    {
        global $_W, $_GPC;

        checkauth();

        $user_id = $_W['member']['uid'];
        $doctor_id = $_GPC['doctor_id'];

        $table = 'mc_doctor_user';

        $error = '';

        // $a = $this->isExist($table, $user_id, $doctor_id, $error);
        // message($a, '', 'error');
        // exit();
        if($this->isExist($table, $user_id, $doctor_id, $error))
        {
            // message($error,'', 'error');
        }

        $conv_id = date('YmdHis', time()).mt_rand();
        $result = pdo_insert($table, array('user_id' => $user_id, 'doctor_id' => $doctor_id, 'conv_id' => $conv_id));
        if(!empty($result))
        {
            message('', $this->createMobileUrl('consult'), 'success');
        }
        else {
            message('出现错误，请重试!', referer(), 'error');
        }
    }

    /**
     * 孕妇与医生是否已经存在关系
     * @param  [type]  $table     [description]
     * @param  [type]  $user_id   [description]
     * @param  string  $doctor_id [description]
     * @param  string  $error     [description]
     * @return boolean            [description]
     */
    public function isExist($table, $user_id, $doctor_id = '0')
    {
        // message($doctor_id,'','error');
        /* 获取该用户所有医生关系 */
        $result = pdo_getall($table,array('user_id' => $user_id));

        if(!empty($result))
        {
            foreach ($result as $item) {
                // message($item['doctor_id'],'', 'error');
                // message($doctor_id == $item['doctor_id'],'', 'error');

                if ($item['doctor_id'] == $doctor_id) {
                    message('已经与该医生建立关系',$this->createMobileUrl('consult'), 'error');
                    // break;
                }
                else {
                    message('已经与其他医生建立关系',$this->createMobileUrl('consult'), 'error');
                }
            }
            return TRUE;
        }

        return FALSE;
    }


    /**
     * 给医生/律师推送模板消息提醒
     * @param string $value [description]
     */
    public function doMobileSendWechatMsg()
    {
        global $_W, $_GPC;

        checkauth();

        $openid = $_GPC['openid'];
        $username = $_GPC['username'];
        $doctorname = $_GPC['doctorname'];
        $content = $_GPC['content'];

        $access_token = $this->getToken();

        $type = $_GPC['type'];
        $room_id = $_GPC['room_id'];
        $msgtype = $_GPC['msgtype'];
        $doctor_id = $_GPC['doctor_id'];

        $url = 'http://shiyue.october-baby.com/baby/app/';
        /**
         * 0:医生/律师 -> 孕妇
         * 1:孕妇 -> 医生/律师
         * @var [type]
         */
        if ($msgtype == '0') {
            $url .= $this->createMobileUrl('chat', array('room_id' => $room_id, 'type' => $type, 'doctor_id' => $doctor_id, 'auth_type' => '1'));
        }
        else {
            $conv_id = $_GPC['conv_id'];
            $url .= $this->createMobileUrl('doctorchat', array('conv_id' => $conv_id, 'room_id' => $room_id, 'type' => $type));
        }

        /* 构造发送数据 */
        $data = array();

        // http://shiyue.october-baby.com/baby/app/index.php?i=4&c=entry&conv_id=20160929202832794001815&type=0&do=DoctorChat&m=water_baby

        $data['touser'] = $openid;
        $data['template_id'] = 'RY9eO78CBFeyyt1-YFUTLuzjVDu6zroTeQeW-0NzulA';
        $data['url'] = $url;

        $msg = array();

        $tmp = array('value' => $doctorname.'您好，你有一条未读消息', 'color' => '#173177');
        $msg['first'] = $tmp;
        $tmp = array('value' => $username, 'color' => '#173177');
        $msg['keyword1'] = $tmp;
        $tmp = array('value' => $content, 'color' => '#173177');
        $msg['keyword2'] = $tmp;
        $tmp = array('value' => '点击查看消息', 'color' => '#173177');
        $msg['remark'] = $tmp;

        $data['data'] = $msg;

        // $data['data'] = array('first' => $doctorname.'您好，你有一条用户咨询待解决', 'keyword1' => $username, 'keyword2' => $content, 'remark' => '点击处理咨询');
        // $data['data'] = array('first' => 'abcd', 'keyword1' => 'ef', 'keyword2' => '34', 'remark' => '56');




        // ihttp_post($url, json_encode($data));

        // message(json_encode($data),'','error');
        // exit();


        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;

        ihttp_post($url, json_encode($data));

        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $msgurl);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // // curl_setopt($ch, CURLOPT_USERAGENT, "qianyunlai.com's CURL Example beta");
        // $data = curl_exec($ch);
        // curl_close($ch);
        message('','','success');
        // return $data;

    }

    /**
     * 医生值班表
     * @return [type] [description]
     */
    public function doMobileDoctor3View()
    {
        global $_W,$_GPC;

        checkauth();

        $doctor_id = $_GPC['doctor_id'];

        $mem_doctor = $this->getMemInfo($doctor_id);

        /* 当期周几 */
        // $cur_time = time();
        // $cur_week = date('w', $cur_time);
        // switch($cur_week){
        //     case 1:
        //         return "星期一";
        //         break;
        //     case 2:
        //         return "星期二";
        //         break;
        //     case 3:
        //         return "星期三";
        //         break;
        //     case 4:
        //         return "星期四";
        //         break;
        //     case 5:
        //         return "星期五";
        //         break;
        //     case 6:
        //         return "星期六";
        //         break;
        //     case 0:
        //         return "星期日";
        //         break;
        // }

        $last_monday = strtotime("-2 monday");
        $next_sunday = strtotime("+3 monday") - 1;

        /* 获取医生值班列表 */
        $sql = "SELECT * FROM ".tablename('water_baby_work_day')." WHERE user_id = '$doctor_id'";
        $sql .= " and worktime between '".$last_monday."' and '".$next_sunday."'";
        $work_list = pdo_fetchall($sql);

        $work_data = $this->getWorkData($work_list, $last_monday, $next_sunday);

        $item = [0,1,2,3,4,5,6];
        $item2 = [7,8,9,10,11,12,13];
        $item3 = [14,15,16,17,18,19,20];
        $item4 = [21,22,23,24,25,26,27];
        $week = ['星期一','星期二','星期三','星期四','星期五','星期六','星期天','星期一','星期二','星期三','星期四','星期五','星期六','星期天','星期一','星期二','星期三','星期四','星期五','星期六','星期天','星期一','星期二','星期三','星期四','星期五','星期六','星期天'];

        include $this->template('doctor3view');
    }

    /**
     * 构造显示数据
     * @param  [type] $work_list   [description]
     * @param  [type] $last_monday [description]
     * @param  [type] $next_sunday [description]
     * @return [type]              [description]
     */
    public function getWorkData($work_list, $last_monday, $next_sunday)
    {
        $work_data = array();

        for ($i=1; $i < 29; $i++) {
            $temp_data = array("date"=>'',worktype=>'',daytype=>'');
            $time = $last_monday + $i * 86400 - 1;
            $day = date('m-d', $time);
            $temp_data['date'] = $day;
            $temp_data['worktype'] = '0';
            $temp_data['daytype'] = '1';
            foreach ($work_list as $item) {
                if (date('m-d', $item['worktime']) == $day) {
                    $temp_data['worktype'] = $item['worktype'];
                    $temp_data['daytype'] = $item['daytype'];
                }
            }
            $work_data[] = $temp_data;
        }

        return $work_data;
    }

    /**
     * 更新医生工作时间
     * @return [type] [description]
     */
    public function doMobileUpdateWorkDay()
    {
        global $_W,$_GPC;

        checkauth();

        $date = $_GPC['date'];
        if (empty($date)) {
            message('日期不能为空','','error');
            exit();
        }
        $worktime = strtotime($date);
        $daytype = empty($_GPC['daytype'])? '1':$_GPC['daytype'];
        $worktype = empty($_GPC['type'])? '0':$_GPC['worktype'];

        $user_id = $_W['member']['uid'];

        $row = pdo_get('water_baby_work_day', array('user_id' => $user_id, 'worktime' => $worktime));

        if (empty($row)) {
            pdo_insert('water_baby_work_day', array('user_id' => $user_id, 'worktime' => $worktime, 'addtime' => time(), 'worktype' => $worktype, 'daytype' => $daytype));
        }
        else {
            if ($row['daytype'] == $daytype) {
                pdo_update('water_baby_work_day', array('worktype' => $worktype), array('id' => $row['id']));
            }
            else {
                pdo_update('water_baby_work_day', array('worktype' => $worktype, 'daytype' => '3'), array('id' => $row['id']));
            }
        }

        message('更新成功',referer(),'success');
    }

    /**
     * 体重曲线
     * @return [type] [description]
     */
    public function doMobileViewWeight()
    {
        global $_W,$_GPC;

        checkauth();

        $user_id = $_W['member']['uid'];

        /* 用户体重列表 */
        $sql = "SELECT * FROM ".tablename('water_baby_weight')." WHERE user_id = '$user_id' order by addtime desc limit 14";
        $weight_list = pdo_fetchall($sql);

        $weight_list = array_reverse($weight_list);

        //var_dump($weight_list);

        $labels = "";
        $weight = "";

        foreach ($weight_list as $item) {
            $labels = $labels.",'".date('Y-m-d',$item['addtime'])."'";
            $weight = $weight.",".$item['weight'];
        }

        $labels = substr($labels, 1);
        $weight = substr($weight, 1);

        // var_dump($labels);

        include $this->template('view_weight');
    }

    /**
     * 更新体重
     * @return [type] [description]
     */
    public function doMobileWeightUpdate()
    {
        global $_W,$_GPC;

        checkauth();

        $user_id = $_W['member']['uid'];
        $weight = $_GPC['weight'];

        /* 校验更新时间 */
        $sql = "SELECT addtime FROM ".tablename('water_baby_weight')." WHERE user_id = '$user_id' ORDER BY addtime DESC LIMIT 1";
        $last = pdo_fetch($sql);

        // $last_day = date()
        if ((date('Y-m-d',time())) == (date('Y-m-d',$last['addtime']))) {
            message('每天只能上传一次呦！','','error');
        }

        // if ((time() - $last['addtime']) < 86400) {
        //     message('每天只能上传一次呦！','','error');
        // }

        $result = pdo_insert('water_baby_weight', array('weight' => $weight, 'user_id' => $user_id, 'addtime' => time()));

        if (!empty($result)) {
            message('更新成功',referer(),'success');
        }
        message('更新失败',referer(),'error');
    }


    /************* Web *******************************/

    /**
     * 文章分类管理.
     */
    public function dowebArticletype()
    {
        global $_W,$_GPC;
        $pageNumber = max(1, intval($_GPC['page']));
        $pageSize = 20;
        $sql = 'SELECT * FROM '.tablename($this->articletypetable)." WHERE uniacid = '{$_W['uniacid']}'  ORDER BY indexno LIMIT ".($pageNumber - 1) * $pageSize.','.$pageSize;
        $list = pdo_fetchall($sql);
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->articletypetable)." WHERE uniacid = '{$_W['uniacid']}'  ORDER BY indexno");
        $pager = pagination($total, $pageNumber, $pageSize);
        include $this->template('articletype');
    }

    /**
     * 增加分类.
     */
    public function dowebaddArticletype()
    {
        global $_W,$_GPC;
        load()->func('tpl');
        $articletypeid = intval($_GPC['articletypeid']);
        if ($articletypeid > 0) {
            $articletype = pdo_fetch('SELECT * FROM '.tablename($this->articletypetable)." WHERE id= '{$articletypeid}'");
            if (!$articletype) {
                message('抱歉，信息不存在或是已经删除！', '', 'error');
            }
            //$topicurl = $_W['siteroot'].'app/'.$this->createMobileUrl ( 'topic',array('topicid'=>$topicid));
        }

        if ($_GPC['op'] == 'delete') {
            $articletype = pdo_fetch('SELECT id FROM '.tablename($this->articletypetable)." WHERE id = '{$articletypeid}'");
            if (empty($articletype['id'])) {
                message('抱歉，信息不存在或是已经被删除！');
            }
            pdo_delete($this->articletable, array('articletypeid' => $topicid));
            pdo_delete($this->articletypetable, array('id' => $articletypeid));
            message('删除成功！', referer(), 'success');
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
            } else {
                $data['uniacid'] = $_W['uniacid'];
                pdo_insert($this->articletypetable, $data);
                $articletype = pdo_insertid();
            }
            $articletype = pdo_fetch('SELECT * FROM '.tablename($this->articletypetable)." WHERE id = '{$articletypeid}'");
            message('更新成功！', referer(), 'success');
        }
        include $this->template('addarticletype');
    }

    /**
     * 文章管理.
     */
    public function dowebarticle()
    {
        global $_W,$_GPC;
        $pageNumber = max(1, intval($_GPC['page']));
        $pageSize = 200;
        $articletypeid = intval($_GPC['articletypeid']);
        $condition = ' and 1=1 ';
        if ($articletypeid > 0) {
            $condition .= "and articletypeid ='{$articletypeid}'";
        }
        $sql = 'SELECT * FROM '.tablename($this->articletable)."
					WHERE uniacid = '{$_W['uniacid']}' {$condition} ORDER BY articletypeid,indexno desc,addtime desc LIMIT ".($pageNumber - 1) * $pageSize.','.$pageSize;
        $list = pdo_fetchall($sql);
        for ($i = 0; $i < count($list); ++$i) {
            $list[$i]['addtime'] = date('Y-m-d', $list[$i]['addtime']);
            $list[$i]['type_name'] = $this->getArticleTypeName($list[$i]['articletypeid']);
        }
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($this->articletable)."
					WHERE uniacid = '{$_W['uniacid']}' {$condition} ORDER BY indexno");
        $pager = pagination($total, $pageNumber, $pageSize);
        include $this->template('article');
    }

    /**
     * 获取文章分类名称
     * @param  string $articletypeid [description]
     * @return [type]                [description]
     */
    public function getArticleTypeName($articletypeid='')
    {
        $tmp = pdo_get($this->articletypetable,array('id' => $articletypeid));
        return $tmp['title'];
    }

    /**
     * 编辑帖子.
     */
    public function dowebaddarticle()
    {
        global $_W,$_GPC;
        load()->func('tpl');
        $articleid = intval($_GPC['articleid']);
        $articletypeid = intval($_GPC['articletypeid']);
        // $articletypeid = intval($_GPC['state']);
        // $articletypesql = 'SELECT * FROM '.tablename($this->articletypetable)." WHERE uniacid = '{$_W['uniacid']}' and state = '$state'  ORDER BY indexno ";
        $articletypesql = 'SELECT * FROM '.tablename($this->articletypetable)." WHERE uniacid = '{$_W['uniacid']}' ORDER BY indexno ";
        $articletypelist = pdo_fetchall($articletypesql);
        if ($articleid > 0) {
            $article = pdo_fetch('SELECT * FROM '.tablename($this->articletable)." WHERE id= '{$articleid}'");
            if (!$article) {
                message('抱歉，信息不存在或是已经删除！', '', 'error');
            }
        }

        if ($_GPC['op'] == 'delete') {
            $article = pdo_fetch('SELECT id FROM '.tablename($this->articletable)." WHERE id = '{$articleid}'");
            if (empty($article['id'])) {
                message('抱歉，信息不存在或是已经被删除！');
            }
            pdo_delete($this->articletable, array('id' => $articleid));
            message('删除成功！', referer(), 'success');
        }

        if (checksubmit()) {
            $imgs = serialize($_GPC ['imgs']);
            $settop = intval($_GPC ['settop']);
            $articletypeid = intval($_GPC ['articletypeid']);
            if ($articletypeid <= 0) {
                message('articletypeid id is null');
            }
            $data = array(
                    'title' => $_GPC ['title'],
                    'desc' => $_GPC ['desc'],
                    'img' => $_GPC ['img'],
                    'content' => htmlspecialchars_decode($_GPC ['content']),
                    'articletypeid' => $articletypeid,
                    'indexno' => intval($_GPC ['indexno']),
                    'state' => intval($_GPC ['state']),
                    'status' => intval($_GPC ['status']),
                    );
            if (!empty($articleid)) {
                pdo_update($this->articletable, $data, array('id' => $articleid));
            } else {
                $data['addtime'] = time(); //date("Y-m-d H:i:s");
                $data['uniacid'] = $_W['uniacid'];
                pdo_insert($this->articletable, $data);
                $topicid = pdo_insertid();
            }
            $article = pdo_fetch('SELECT * FROM '.tablename($this->articletable)." WHERE id = '{$articleid}'");
            $imgs = unserialize($article['imgs']);
            message('更新成功！', referer(), 'success');
        }
        include $this->template('addarticle');
    }

     /**
      * 医院管理.
      *
      * @return [type] [description]
      */
     public function dowebHospital()
     {
         global $_W,$_GPC;
         $pageNumber = max(1, intval($_GPC['page']));
         $pageSize = 20;

        /* 医院列表 */
        $sql = 'SELECT * FROM '.tablename('mc_hospital').' ORDER BY hosp_id LIMIT '.($pageNumber - 1) * $pageSize.','.$pageSize;
         $list = pdo_fetchall($sql);
        /* 医院总数 */
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename('mc_hospital'));
         $pager = pagination($total, $pageNumber, $pageSize);

         include $this->template('hospital');
     }

    /**
     * 添加医院.
     */
    public function dowebAddHospital()
    {
        global $_W,$_GPC;
        load()->func('tpl');
        $hosp_id = intval($_GPC['hosp_id']);
        if ($hosp_id > 0) {
            $hospital = pdo_fetch('SELECT * FROM '.tablename('mc_hospital')." WHERE hosp_id= '{$hosp_id}'");
            if (!$hospital) {
                message('抱歉，信息不存在或是已经删除！', '', 'error');
            }
            //$topicurl = $_W['siteroot'].'app/'.$this->createMobileUrl ( 'topic',array('topicid'=>$topicid));
        }

        if ($_GPC['op'] == 'delete') {
            $hospital = pdo_fetch('SELECT hosp_id FROM '.tablename('mc_hospital')." WHERE hosp_id = '{$hosp_id}'");
            if (empty($hospital['hosp_id'])) {
                message('抱歉，信息不存在或是已经被删除！');
            }
            pdo_delete('mc_members', array('hosp_id' => $hosp_id));
            pdo_delete('mc_hospital', array('hosp_id' => $hosp_id));
            message('删除成功！', referer(), 'success');
        }

        if (checksubmit()) {
            $data = array(
                    'hosp_name' => $_GPC ['title'],
                    'hosp_desc' => $_GPC ['desc'],
                    'hosp_img' => $_GPC ['img'],
            );

            if (!empty($hosp_id)) {
                pdo_update('mc_hospital', $data, array('hosp_id' => $hosp_id));
            } else {
                pdo_insert('mc_hospital', $data);
                $hosp_id = pdo_insertid();
            }
            $hospital = pdo_fetch('SELECT * FROM '.tablename('mc_hospital')." WHERE hosp_id = '{$hosp_id}'");
            message('更新成功！', referer(), 'success');
        }
        include $this->template('addhospital');
    }

    /**
     * 科室管理.
     *
     * @return [type] [description]
     */
    public function dowebHospDep()
    {
        global $_W,$_GPC;
        $pageNumber = max(1, intval($_GPC['page']));
        $pageSize = 20;

       /* 科室列表 */
       $sql = 'SELECT * FROM '.tablename('mc_hosp_dep').' ORDER BY dep_id LIMIT '.($pageNumber - 1) * $pageSize.','.$pageSize;
        $list = pdo_fetchall($sql);

       /* 科室总数 */
       $total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename('mc_hosp_dep'));
        $pager = pagination($total, $pageNumber, $pageSize);

        include $this->template('hosp_dep');
    }

    /**
     * 编辑科室信息.
     *
     * @return [type] [description]
     */
    public function dowebAddHospDep()
    {
        global $_W,$_GPC;
        load()->func('tpl');
        $dep_id = intval($_GPC['dep_id']);
        if ($dep_id > 0) {
            $hosp_dep = pdo_fetch('SELECT * FROM '.tablename('mc_hosp_dep')." WHERE dep_id= '{$dep_id}'");
            if (!$hosp_dep) {
                message('抱歉，信息不存在或是已经删除！', '', 'error');
            }
            //$topicurl = $_W['siteroot'].'app/'.$this->createMobileUrl ( 'topic',array('topicid'=>$topicid));
        }

        if ($_GPC['op'] == 'delete') {
            $hosp_dep = pdo_fetch('SELECT * FROM '.tablename('mc_hosp_dep')." WHERE dep_id= '{$dep_id}'");
            if (empty($hosp_dep['dep_id'])) {
                message('抱歉，信息不存在或是已经被删除！');
            }
            pdo_delete('mc_members', array('dep_id' => $dep_id));
            pdo_delete('mc_hosp_dep', array('dep_id' => $dep_id));
            message('删除成功！', referer(), 'success');
        }

        if (checksubmit()) {
            $data = array(
                    'dep_name' => $_GPC ['title'],
                    'dep_desc' => $_GPC ['desc'],
                    'dep_img' => $_GPC ['img'],
            );

            if (!empty($dep_id)) {
                pdo_update('mc_hosp_dep', $data, array('dep_id' => $dep_id));
            } else {
                pdo_insert('mc_hosp_dep', $data);
                $dep_id = pdo_insertid();
            }
            $hosp_dep = pdo_fetch('SELECT * FROM '.tablename('mc_hosp_dep')." WHERE dep_id= '{$dep_id}'");
            message('更新成功！', referer(), 'success');
        }
        include $this->template('addhosp_dep');
    }

    /**
     * 职称管理.
     *
     * @return [type] [description]
     */
    public function dowebHospTitle()
    {
        global $_W,$_GPC;
        $pageNumber = max(1, intval($_GPC['page']));
        $pageSize = 20;

       /* 科室列表 */
       $sql = 'SELECT * FROM '.tablename('mc_hosp_title').' ORDER BY title_id LIMIT '.($pageNumber - 1) * $pageSize.','.$pageSize;
        $list = pdo_fetchall($sql);

       /* 科室总数 */
       $total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename('mc_hosp_title'));
        $pager = pagination($total, $pageNumber, $pageSize);

        include $this->template('hosp_title');
    }

    /**
     * 编辑职称.
     *
     * @return [type] [description]
     */
    public function dowebAddHospTitle()
    {
        global $_W,$_GPC;
        load()->func('tpl');
        $title_id = intval($_GPC['title_id']);
        if ($title_id > 0) {
            $hosp_title = pdo_fetch('SELECT * FROM '.tablename('mc_hosp_title')." WHERE title_id= '{$title_id}'");
            if (!$hosp_title) {
                message('抱歉，信息不存在或是已经删除！', '', 'error');
            }
            //$topicurl = $_W['siteroot'].'app/'.$this->createMobileUrl ( 'topic',array('topicid'=>$topicid));
        }

        if ($_GPC['op'] == 'delete') {
            $hosp_title = pdo_fetch('SELECT * FROM '.tablename('mc_hosp_title')." WHERE title_id= '{$title_id}'");
            if (empty($hosp_title['title_id'])) {
                message('抱歉，信息不存在或是已经被删除！');
            }
            pdo_delete('mc_members', array('title_id' => $title_id));
            pdo_delete('mc_hosp_title', array('title_id' => $title_id));
            message('删除成功！', referer(), 'success');
        }

        if (checksubmit()) {
            $data = array(
                    'title_name' => $_GPC ['title'],
                    'title_desc' => $_GPC ['desc'],
                    'title_img' => $_GPC ['img'],
            );

            if (!empty($title_id)) {
                pdo_update('mc_hosp_title', $data, array('title_id' => $title_id));
            } else {
                pdo_insert('mc_hosp_title', $data);
                $title_id = pdo_insertid();
            }
            $hosp_title = pdo_fetch('SELECT * FROM '.tablename('mc_hosp_title')." WHERE title_id= '{$title_id}'");
            message('更新成功！', referer(), 'success');
        }
        include $this->template('addhosp_title');
    }

    /**
     * 编辑关于十月.
     *
     * @return [type] [description]
     */
    public function dowebEditAbout()
    {
        global $_W,$_GPC;

        load()->func('tpl');

        $article_info = pdo_get('water_baby_about');
        $articleid = intval($article_info['article_id']);

        if (checksubmit()) {
            $data = array(
                    'title' => $_GPC ['title'],
                    'content' => htmlspecialchars_decode($_GPC ['content']),
                    );
            if (!empty($articleid)) {
                pdo_update('water_baby_about', $data, array('article_id' => $articleid));
            } else {
                pdo_insert('water_baby_about', $data);
                $topicid = pdo_insertid();
            }
            $article_info = pdo_fetch('SELECT * FROM '.tablename('water_baby_about')." WHERE article_id = '{$articleid}'");
            message('更新成功！', referer(), 'success');
        }
        include $this->template('about');
    }

    /**
     * 首页Banner管理
     * @return [type] [description]
     */
    public function dowebBanner()
    {
        global $_W,$_GPC;
        $pageNumber = max(1, intval($_GPC['page']));
        $pageSize = 20;
        $table = 'water_baby_banner';
        $sql = 'SELECT * FROM '.tablename($table)." ORDER BY indexno LIMIT ".($pageNumber - 1) * $pageSize.','.$pageSize;
        $list = pdo_fetchall($sql);
        $total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename($table));
        $pager = pagination($total, $pageNumber, $pageSize);
        include $this->template('banner');
    }

    /**
     * 增加/修改/删除Banner.
     */
    public function dowebaddBanner()
    {
        global $_W,$_GPC;
        load()->func('tpl');
        $table = 'water_baby_banner';
        $img_id = intval($_GPC['img_id']);
        if ($img_id > 0) {
            $banner = pdo_fetch('SELECT * FROM '.tablename($table)." WHERE id= '{$img_id}'");
            if (!$banner) {
                message('抱歉，信息不存在或是已经删除！', '', 'error');
            }
            //$topicurl = $_W['siteroot'].'app/'.$this->createMobileUrl ( 'topic',array('topicid'=>$topicid));
        }

        if ($_GPC['op'] == 'delete') {
            $banner = pdo_fetch('SELECT * FROM '.tablename($table)." WHERE id= '{$img_id}'");
            if (empty($banner['id'])) {
                message('抱歉，信息不存在或是已经被删除！');
            }
            pdo_delete($table, array('id' => $img_id));
            message('删除成功！', referer(), 'success');
        }

        if (checksubmit()) {
            $data = array(
                    'title' => $_GPC ['title'],
                    'img' => $_GPC ['img'],
                    'indexno' => intval($_GPC ['indexno']),
                    'state' => intval($_GPC ['state']),
            );

            if (!empty($img_id)) {
                pdo_update($table, $data, array('id' => $img_id));
            } else {
                // $data['uniacid'] = $_W['uniacid'];
                pdo_insert($table, $data);
                $img_id = pdo_insertid();
            }
            $banner = pdo_fetch('SELECT * FROM '.tablename($table)." WHERE id = '{$img_id}'");
            message('更新成功！', referer(), 'success');
        }
        include $this->template('addbanner');
    }


    /**
     * 根据文章分类或者文章列表
     * @param  [type] $typeid [description]
     * @param  [type] $limit  [description]
     * @return [type]         [description]
     */
    public function getArticleListByTid($typeid, $limit)
    {
        global $_W;

        $groupid = $_W['member']['groupid'];

        $state = $groupid == '5'? '1':'2';

        $sql = 'SELECT * FROM '.tablename($this->articletable)."
					WHERE uniacid = '{$_W['uniacid']}' and articletypeid = '{$typeid}' and state = '$state' ORDER BY indexno desc,addtime desc LIMIT ".$limit;

        // $sql = 'SELECT * FROM '.tablename($this->articletable)."
		// 			WHERE uniacid = '{$_W['uniacid']}' and articletypeid = '{$typeid}' and state = '$state' and indexno = '2' ORDER BY addtime desc";
        // $sql = 'SELECT * FROM '.tablename($this->articletable)."
		// 			WHERE uniacid = '{$_W['uniacid']}' and articletypeid = '{$typeid}' and state = '$state' and indexno != '2' ORDER BY addtime desc LIMIT ".$limit;
        $list = pdo_fetchall($sql);

        return $list;
    }

    public function getarticleSumByTid($typeid)
    {
        global $_W;
        $result = pdo_fetch('SELECT count(*) as cnt FROM '.tablename($this->articletable)." WHERE articletypeid = '{$typeid}' and uniacid = '{$_W['uniacid']}'");

        return $result ['cnt'] <= 0 ? 0 : $result ['cnt'];
    }

    /**
     * 根据id获取医生用户数量.
     *
     * @param string $hosp_id [description]
     *
     * @return [type] [description]
     */
    public function getDoctorSumById($id = '', $type = '')
    {
        global $_W;
        $result = pdo_fetch('SELECT count(*) as cnt FROM '.tablename('mc_members')." WHERE {$type} = '{$id}'");

        return $result ['cnt'] <= 0 ? 0 : $result ['cnt'];
    }

    /**
     * 获取毫秒数.
     *
     * @return number
     */
    public function getMillisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        $basecode = (float) sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);

        return date('YmdHis').substr($basecode, 2, 10).mt_rand(1000, 9999);
    }

    /**
     * 获取Token值
     *
     * @return unknown
     */
    public function getToken()
    {
        global $_W;
        load()->classs('weixin.account');
        $id = $_W['acid'];
        if (empty($id)) {
            $id = $_W['uniacid'];
        }
        $accObj = WeixinAccount::create($id);
        $access_token = $accObj->fetch_token();

        return $access_token;
    }

    /**
     * 发送模板消息
     * $data为消息模板数组.
     */
    public function sendMBXX($access_token, $data)
    {
        // $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
        // ihttp_post($url, json_encode($data));
    }

    /**
     * 查询数据库获取粉丝信息.
     *
     * @param unknown $openid
     *
     * @return unknown
     */
    public function getFansDBInfo($openid)
    {
        global $_W;
        $fans = pdo_fetch('SELECT * FROM '.tablename($this->fanstable)." WHERE uniacid = '{$_W['uniacid']}' and openid ='{$openid}'");

        return $fans;
    }
}
