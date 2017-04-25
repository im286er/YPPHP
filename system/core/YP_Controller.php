<?php
/**
 * User: yongli
 * Date: 17/4/19
 * Time: 16:37
 * Email: yong.li@szypwl.com
 * Copyright: 深圳优品未来科技有限公司
 */
namespace YP\Core;

use YP\YP;
use YP\Config\Services;
use YP\Libraries\YP_Twig as Twig;
use YP\Core\YP_Log as Log;
use YP\Core\YP_Response as Response;
use YP\Libraries\YP_Validation as Validation;
use YP\Core\YP_IncomingRequest as IncomingRequest;

/**
 * Class Controller
 *
 * @package CodeIgniter
 */
class YP_Controller
{

    /**
     * 用于加载帮助函数
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * 请求对象
     *
     * @var Request
     */
    protected $request;

    /**
     * 响应对象
     *
     * @var Response
     */
    protected $response;

    /**
     * 日志对象
     *
     * @var Log
     */
    protected $logger;

    /**
     * 该控制器中的所有方法是否应强制HTTPS访问
     * 设置HSTS报头的秒数
     *
     * @var int
     */
    protected $forceHTTPS = 0;

    /**
     * 校验对象
     *
     * @var
     */
    protected $validator;

    /**
     * 模板对象
     *
     * @var
     */
    protected $twig;

    /**
     * 方法
     *
     * @var
     */
    protected $method;

    /**
     * 模板的后缀
     *
     * @var
     */
    protected $extension = '.html';

    /**
     * 模板目录
     *
     * @var
     */
    protected $tempPath;

    /**
     * YP_Controller constructor.
     *
     * @param IncomingRequest $request
     * @param Response        $response
     * @param Log|null        $logger
     */
    public function __construct(IncomingRequest $request, Response $response, Log $logger = null)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->logger   = is_null($logger) ? Services::log(true) : $logger;
        $this->logger->info('Controller "' . get_class($this) . '" loaded.');
        if ($this->forceHTTPS > 0) {
            $this->forceHTTPS($this->forceHTTPS);
        }
        $this->loadHelpers();
        $this->initTwig();
    }

    //--------------------------------------------------------------------
    /**
     * A convenience method to use when you need to ensure that a single
     * method is reached only via HTTPS. If it isn't, then a redirect
     * will happen back to this method and HSTS header will be sent
     * to have modern browsers transform requests automatically.
     *
     * @param int $duration The number of seconds this link should be
     *                      considered secure for. Only with HSTS header.
     *                      Default value is 1 year.
     */
    public function forceHTTPS(int $duration = 31536000)
    {
        force_https($duration, $this->request, $this->response);
    }

    /**
     * 缓存当前页码
     *
     * @param int $time
     */
    public function cachePage(int $time)
    {
        YP::cache($time);
    }

    /**
     * 加载帮助文件
     */
    protected function loadHelpers()
    {
        if (empty($this->helpers)) {
            return;
        }
        foreach ($this->helpers as $helper) {
            helper($helper);
        }
    }

    /**
     * 校验请求的参数
     * 如果校验失败,将错误存放到类的$error的属性上
     *
     * @param IncomingRequest $request
     * @param                 $rules
     * @param array|null      $messages
     *
     * @return bool
     */
    public function validate(IncomingRequest $request, $rules, array $messages = null): bool
    {
        $this->validator = Services::validation();
        // 校验路由
        $success = $this->validator->withRequest($request)->setRules($rules, $messages)->run();

        return $success;
    }

    /**
     * 初始化Twig模板引擎
     */
    public function initTwig()
    {
        // TWig配置信息
        $config = new \Config\Twig();
        $config = (array)$config;
        // 获得当前路由信息
        $router          = Services::router();
        $directory       = $router->directory();
        $controller      = explode('\\', $router->controllerName());
        $controller      = end($controller);
        $this->method    = $router->methodName();
        $this->extension = $config['extension'] ?? $this->extension;
        // 模板目录
        $config['template_dir'] = $config['template_dir'] . $directory . $controller . '/';
        // 缓存目录
        $config['cache_dir'] = $config['cache_dir'] . $directory . $controller . '/';
        $this->twig          = new Twig($config);
        $this->tempPath      = $config['template_dir'];
    }

    /**
     * 视图渲染
     *
     * @param array $data
     * @param null  $htmlFile
     */
    public function display($data = [], $htmlFile = null)
    {
        // 修改模板名称
        if (!is_null($htmlFile)) {
            $this->method = $htmlFile;
        }
        // 模板文件
        $tempFile     = $this->method . $this->extension;
        $tempFilePath = $this->tempPath . $tempFile;
        is_file($tempFilePath) or touch($tempFilePath);
        echo $this->twig->render($tempFile, $data);
        die;
    }

    /**
     * 分配变量到模板中
     *
     * @param      $var
     * @param null $value
     */
    public function assign($var, $value = null)
    {
        $this->twig->assign($var, $value);
    }

}