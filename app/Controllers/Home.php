<?php
/**
 * User: yongli
 * Date: 17/4/24
 * Time: 15:21
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace APP\Controllers;

use YP\Core\YP_Controller as Controller;
use HomeModel;
use YP_Email as Email;
use Config\Services;

class Home extends Controller
{

    public function initialization()
    {
        // TODO: Change the autogenerated stub
    }

    /**
     * 网站信息
     */
    public function index()
    {
        $time         = microtime(true) * 1000;
        $elapsed_time = number_format(($time - START_TIME), 0);
        $this->assign('title', '你好,Twig模板引擎');
        $this->assign('view_path', 'app/Views/Home/' . $this->method . $this->extension);
        $this->assign('controller_path', 'app/Controller/Home.php');
        $this->assign('evn', ENVIRONMENT);
        $this->assign('elapsed_time', $elapsed_time);
        $this->assign('version', VERSION);
        $this->display();
    }

    public function getUserInfo()
    {
        //        P($_POST);
        //        P($_GET);
        //        P($userInfo);


    }

    /**
     * 测试Eloquent组件
     */
    public function testEloquent()
    {
        $userInfo = HomeModel::select('id', 'username', 'email', 'photo_url')->get()->toArray();
        P($userInfo);
    }

    /**
     * redis测试
     */
    public function testRedis()
    {
        $cache = Services::cache();
        $cache->set('YP:cache', 'YP框架你好');
        $data = [
            ['name' => 'liyong', 'age' => 20],
            ['name' => 'zhujun', 'age' => 23],
            ['name' => 'lijian', 'age' => 24]
        ];
        foreach ($data as $key => $value) {
            $cache->hmset('YP:cache:' . $key, '', $value);
        }

    }

    /**
     * 邮件测试
     */
    public function testEmail()
    {
        $email  = new Email();
        $status = $email->sendEmail('优品框架发送邮件测试', ['626375290@qq.com'], $title = '深圳优品未来');
        P($email->errorInfo);
        P($status);
    }
}