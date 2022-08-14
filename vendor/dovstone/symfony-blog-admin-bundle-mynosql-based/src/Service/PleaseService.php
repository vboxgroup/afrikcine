<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service;

use Twig\Markup;
use JasonGrimes\Paginator;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\Filesystem\Filesystem;
use DovStone\MyNoSQL\HostConnection as MyNoSQL;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Repository\BundleUserRepository;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Repository\BundleBloggyRepository;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\__PhpHtmlCssJsMinifierService;


class PleaseService extends AbstractController
{
    public $prevContainer;
    //
    private $appUiD;

    public function __construct(ContainerInterface $container)
    {
        $this->prevContainer = $container;
        $this->appUiD = sha1($_SERVER['APP_NAME']);
    }

    public function getContainer()
    {
        return $this->prevContainer;
    }

    public function getService($serviceName)
    {
        return $this->prevContainer->get("_service.{$serviceName}");
    }

    public function getRepo($repositoryName)
    {
        if( in_array($repositoryName, ['BloggyRepository', 'BloggyRepo', 'Bloggy', 'bloggy', 'B', 'b']) ) {
            return new BundleBloggyRepository($this);
        }
        if( in_array($repositoryName, ['UserRepository', 'UserRepo', 'User', 'user', 'U', 'u']) ) {
            return new BundleUserRepository($this);
        }
        return null;
    }

    public function serve($serviceName)
    {
        return $this->getService($serviceName);
    }

    public function setGlobal($bigData)
    {
        if( $bigData ){
            foreach($bigData as $globalName => $data){
                if( is_callable($data) ){
                    $data = $data();
                }
                $this->prevContainer->get('session')->set('__global__' . $this->appUiD . '__' . $globalName, serialize($data));
            }
        }
    }

    public function setFlash($bigData, $message = null)
    {
        if( is_string($bigData) ){
            $this->setGlobal([ $bigData => $message ]);
        }
        else {
            $this->setGlobal($bigData);
        }
    }

    public function getGlobal($globalName = null, $default = null)
    {
        if( is_null($globalName) ){
            return $this->prevContainer->get('session');
        }
        $globalValue = $this->prevContainer->get('session')->get('__global__' . $this->appUiD . '__' . $globalName);
        if( !is_null($globalValue) ){
            return unserialize($globalValue);
        }
        return $default;
    }
    
    public function setPost($info): void
    {
        $info = array_merge([
            'title' => 'Mon Titre',
            'description' => $info['description'] ?? $info['title'],
            'href' => $this->serve('url')->getCurrentUrl(),
            'layout' =>  "",
        ], $info);

        $this->setGlobal([ 'post' => $info ]);
    }

    public function getPost()
    {
        return $this->getGlobal('post');
    }

    public function unsetGlobal($globalName): void
    {
        if( is_array($globalName) ){
            foreach($globalName as $gbName){
                $this->prevContainer->get('session')->set('__global__' . $this->appUiD . '__' . $gbName, null);
            }
        }
        else {
            $this->prevContainer->get('session')->set('__global__' . $this->appUiD . '__' . $globalName, null);
        }
    }

    public function getCached($path)
    {
        $dirServ = $this->serve('dir');
        $file = $dirServ->dirPath($path.".html.twig");
        $key = $this->serve('string')->getSlug($path);

        if( file_exists($file) ){
            $this->setStorage([
                [$key, function() use ($path) {
                    return $this->renderView(str_ireplace('theme', '', $path) . '.html.twig');
                }]
            ]);
            return new Markup($this->getStorage($key), 'UTF-8');
        }
        return null;
    }

    public function setStorage(array $bigData, $filename = null)
    {
        $stringServ = $this->serve('string');
        $dirServ = $this->serve('dir');
        $fs = new Filesystem();

        if( $bigData ){
            foreach($bigData as $data){

                if( count($data) === 2 ){

                    $filename = $stringServ->getSlug($data[0]);
                    $content = $data[1];

                    $file = $dirServ->dirPath("var/cache/swagg/$filename.txt");
                    
                    //creating file if not exists yet
                    if( !$fs->exists($file) ){ $fs->appendToFile($file, ''); }

                    //only put content if file is empty
                    if( empty(file_get_contents($file)) ){
                        
                        $content = is_callable($content) ? $content() : $content;

                        $fs->appendToFile($file, serialize($content));
                    }
                }
            }
        }

        return $this->getStorage($filename);
    }

    public function getStorage($filename)
    {
        $dirServ = $this->serve('dir');
        $file = $dirServ->dirPath("var/cache/swagg/$filename.txt");
        if( file_exists($file) ){
            return unserialize(file_get_contents($file));
        }
        return null;
    }

    public function unsetStorage($filesNames)
    {
        $dirServ = $this->serve('dir');

        if( is_string($filesNames) ){
            $filesNames = [$filesNames];
        }
        if($filesNames){
            foreach($filesNames as $fileName){
                $file = $fileName.'File';
                $file = $dirServ->dirPath("var/cache/swagg/$fileName.txt");
                if( file_exists($file) ){
                    unlink($file);
                }
            }
        }
        return null;
    }
    
    public function mergeData(...$data)
    {
        $merged = [];
        $data = json_decode(json_encode($data), true);
        $mixServ = $this->serve('mix');
        foreach($data as $d){
            $merged = $mixServ->arrayMergeRecursiveEx($merged, $d);
        }
        return $merged;
    }

    public function setBackOfficeNav($data)
    {
        $nav = '';
        $colors = ['#a03c3c', '#009688', '#3F51B5', '#000000', '#F44336', '#607D8B', '#CDDC39', '#FF5722', '#2196F3', '#795548', '#767948', '#b4bf0c'];
        if($data){
            foreach ($data as $i => $d) {
                if( sizeof($d) === 3 ){

                    $data[$i]['id'] = substr(md5($d[0]), 0, 8);
                    $data[$i]['title'] = $d[0];

                    $nav .= '<a href="'.$d[2].'" class="text-ellipsis" title="'.$d[0].'">
                            <div class="icon" style="background-color:'.$colors[rand(0, 11)].'"><span class="iconify" data-icon="'.$d[1].'" data-inline="false"></span></div>
                            <span>'.$d[0].'</span>
                        </a>';
                }
            }
        }
        $this->setGlobal([
            'backOfficeNav' => new Markup($nav, 'UTF-8'),
            'backOfficeNavData' => $data
        ]);
    }

    public function setMyNoSQL()
    {
        $database = explode('@', $this->serve('env')->getAppEnv('DATABASE'));
        if( count($database) === 2 ){
            $dsn = $database[0];
            $x = explode(':', $database[1]);
            $user = $x[0];
            $password = $x[1];
            $this->MyNoSQL = new MyNoSQL($dsn, $user, $password);
            return $this->MyNoSQL;
        }
        return null;
    }

    public function getMyNoSQL()
    {   
        $this->setMyNoSQL();
        return $this->MyNoSQL;
    }

    public function getMyNoSQLCollection($collectionName)
    {
        $this->setMyNoSQL();
        if( in_array($collectionName, [ 'Bloggies', 'bloggies', 'Bloggy', 'bloggy', 'B', 'b']) ){ $collectionName = 'bloggies'; }
        if( in_array($collectionName, [ 'Hits', 'hits', 'Hit', 'hit', 'H', 'h' ]) ){ $collectionName = 'hits'; }
        if( in_array($collectionName, [ 'Users', 'users', 'User', 'user', 'U', 'u' ]) ){ $collectionName = 'users'; }
        if($collectionName){
            return $this->MyNoSQL->collection($collectionName);
        }
    }
    
    public function getAttr($data, $fieldsPath, $onNull=null, $isEmptiale=true)
    {   
        $attrFetchedVal = null;
        $fieldsPath = explode('.', $fieldsPath);
        $size = sizeof($fieldsPath);
        for ($i=0; $i < $size; $i++) {
            $val = $this->_getAttrLoop($data, $fieldsPath, $i, $onNull);
            if( $isEmptiale==false && (is_null($val) || empty($val)) ){
                return $onNull;
            }
            return $val;
        }
        return null;
    }
    
    public function getUser()
    {   
        return $this->serve('security')->getUser();
    }
    
    public function getRequestStack()
    {
        return $this->prevContainer->get('request_stack');
    }

    public function getRequest()
    {
        return $this->prevContainer->get('request_stack')->getCurrentRequest();
    }

    public function getRequestStackQuery()
    {
        return $this->getRequestStack()->getCurrentRequest()->query;
    }

    public function getRequestStackRequest()
    {
        return $this->getRequestStack()->getCurrentRequest()->request;
    }

    public function getRequestUri()
    {
        return $this->getRequestStack()->getCurrentRequest()->getRequestUri();
    }

    public function isXHR()
    {
        return $this->getRequest()->isXmlHttpRequest();
    }

    public function isNotXHR()
    {
        return !$this->isXHR();
    }

    public function isBot()
    {
        if (preg_match('/bot|crawl|curl|dataprovider|search|get|spider|find|java|majesticsEO|google|yahoo|teoma|contaxe|yandex|libwww-perl|facebookexternalhit/i', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        }
        return false;
    }

    public function compress($content)
    {
        return new Markup(
            mb_convert_encoding(
                (new __PhpHtmlCssJsMinifierService())->getMinifiedHtml($content),
                'UTF-8',
                'UTF-8'
            ),
        'UTF-8');
    }
    
    public function XHRreturn($params)
    {
        $xhr_continue = $this->getRequest()->getRequestUri();
        $xhr_continue = strpos($xhr_continue, '/logout')!==false?'':'?xhr_continue='.$xhr_continue;

        if(isset($params['ifNotXHR'])){
            return $this->redirect($this->generateUrl($params['ifNotXHR'], $params['params'] ?? []).$xhr_continue);
        }
    }
    
    public function redirectToHome()
    {
        return $this->redirect($this->serve('url')->getUrl());
    }

    public function redirectToReferer()
    {
        $referer = $this->getRequest()->headers->get('referer');
        return $this->redirect(is_null($referer) ? $this->serve('url')->getUrl('/') : $referer);
    }

    public function getReferer()
    {
        return $this->getRequest()->headers->get('referer');
    }

    public function getRefererParam($name='', $onNull='')
    {
        parse_str(parse_url($this->getReferer(), PHP_URL_QUERY), $queries);
        return attr($queries, $name, $onNull);
    }

    public function _redirect($href = '/')
    {
        return $this->redirect($this->serve('url')->getUrl($href));
    }
    
    public function getCurl($url, $isRoute = true)
    {
        if( true === $isRoute ){
            $url = $this->serve('env')->getAppEnv('APP_ORIGIN') . $this->generateUrl($url);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }
    
    public function genUrl(string $route, array $parameters = [])
    {
        return $this->generateUrl($route, $parameters);
    }
    
    public function convertToKnpPaginatorBundle(array $items=[], int $perPage, string $pageQuery = 'page')
    {
        $paginator = $this->prevContainer->get('knp_paginator')->paginate(
            $items,
            (int) $this->prevContainer->get('request_stack')->getCurrentRequest()->query->get('page', 1),
            $perPage
        );
        $paginator->offset = $this->getPaginationOffset($perPage, $pageQuery);
        return $paginator;
    }
    
    public function jasonPaginator($items=[], $perPage = 15)
    {
        //$total = $collection->findAllBy($finderCriteria[0] ?? [], $finderCriteria[1] ?? [])->count();
        $total = count($items);
        $perPage = (int)$perPage <= 0 ? 1 : (int)$perPage;
        $currPage = (int) $this->prevContainer->get('request_stack')->getCurrentRequest()->query->get('page', 1);
        $urlPattern = '?page=(:num)';
        return new Paginator($total, $perPage, $currPage, $urlPattern);
    }
    
    public function mimicKnpPaginator(int $total, int $perPage = 15)
    {
        return $this->convertToKnpPaginatorBundle(array_fill(0, $total, 'stOne'), $perPage);
    }
    
    public function getPaginationOffset(int $limit, string $pageQuery = 'page')
    {
        $page = $_GET[$pageQuery] ?? 1;
        $page = $page <= 0 ? 1 : $page;
        $offset = $page * $limit - $limit;
        return $offset;
    }
    
    public function fetchEager(?array $data = [], array $orderBy=['createdAt' => 'desc'])
    {
        return $this->getRepo('bloggy')->fetchEager($data, $orderBy);
    }

    public function sendEmail($params)
    {
        $m = $this->serve('env')->getAppEnv('MAILER');
        preg_match('/(.+)(\|)(.+)(\|)(.+)/', $m, $MAILER);

        if(count($MAILER) !== 6){
            return $params['onError']("Error parsing MAILER provided in .env");
        }

        $username = $MAILER[1];
        $password = $MAILER[3];

        $smtp = explode('::', $MAILER[5]);
        $SMTPSecure = $smtp[0];
        $Host = $smtp[1];
        $Port = $smtp[2];

        $params = array_merge([
            'IsSMTP' => true, // false means IsMail
            'SMTPAuth' =>  true,
            'SMTPSecure' => $SMTPSecure,
            'Host' => $Host,
            'Port' => $Port, //465
            'isHTML' => true,
            //
            'username' => $username,
            'password' => $password,
            //
            'subject' => 'Wonderful Subject',
            'from' => ['john@doe.com' => 'John Doe'],
            'to' => ['receiver@domain.org', 'other@domain.org' => 'A name'],

            'body' => function($mail, $mediaService, $urlService, $params){},
            'onSuccess' => function($params){},
            'onError' => function($e){},
        ], $params);

        // Instantiation and passing `true` enables exceptions
        $mail = new PHPMailer();

        $mail->SMTPDebug = false;

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        if( $params['IsSMTP'] ){
            $mail->IsSMTP(); // telling the class to use SMTP
        }
        $mail->SMTPAuth = $params['SMTPAuth']; // enable SMTP authentication
        $mail->SMTPSecure = $params['SMTPSecure']; // sets the prefix to the servier
        $mail->Host = $params['Host']; // sets the SMTP server
        $mail->Port = $params['Port']; // set the SMTP port
        $mail->isHTML($params['isHTML']);
        //
        $mail->Username = $params['username']; // SMTP account username
        $mail->Password = $params['password']; // SMTP account password

        foreach ($params['to'] as $k => $v) { $k === 0 ? $mail->AddAddress($v) : $mail->AddAddress($k, $v); }

        //foreach ($params['from'] as $k => $v) { $k === 0  ? $mail->SetFrom($v) : $mail->SetFrom($k, $v); }
        foreach ($params['from'] as $k => $v) { $k === 0  ? $mail->SetFrom($username) : $mail->SetFrom($username, $this->serve('env')->getAppEnv('APP_NAME')); }

        $mail->Subject = $params['subject'];
        $mail->Body = $params['body']( $mail, $this->serve('asset'), $this->serve('url'), (object)$params );

        if($mail->Send() && $mail->ErrorInfo == ""){
            return $params['onSuccess']($params, $mail);
        }
        else {
            return $params['onError']($mail->ErrorInfo);
        }
    }

    public function cachableResponse($view)
    {
        if( $this->isXHR() && $this->getRequestStackQuery()->get('_ajaxify') ){
            $tplServ = $this->serve('template');
            return new JsonResponse(
                $tplServ->parseView(
                    $tplServ->sanitizeView($view)
                )
            );
        }
        return $this->newResponse(
            $this->cachableView($view)
        );
    }

    public function cachableView($view)
    {
        return $view;
        /*
            if(
                $this->isXHR()
                &&
                $this->getRequestStackQuery()->get('_ajaxify')
                &&
                $this->serve('env')->getAppEnv('DEEP_CACHE') == 'true'
            ){
                $key = $this->serve('string')->getSlug($this->serve('url')->getCurrentUrl());
                $key = trim($key, '-ajaxify-true');

                $view = $this->setStorage([
                    [ $key, function() use ($view) { return $view; } ]
                ]);
                //
                $view = $this->getStorage($key);
            }
            return $view;
        */
    }

    public function cacheRendered(callable $callback, $renderCache = true)
    {
        $key = $this->serve('string')->getSlug($this->getRequestUri());

        // lets check ttl
        if($renderCache){
            if( file_exists($cachedFile = $this->serve('dir')->dirPath("var/cache/swagg/$key.txt")) ){
                $ttl = is_bool($renderCache) ? "1 hour" : $renderCache;
                $ctime = filemtime($cachedFile);
                $expiryTime = date("Y-m-d H:i:s", strtotime("+$ttl", $ctime));
                $now = date("Y-m-d H:i:s");
                if( $now > $expiryTime ){
                    unlink($cachedFile);
                }
            }
        }

        if( !$renderCache ){
            return $callback();
        }

        if( $view = $this->getStorage($key) ){
            if( $this->isXHR() ){
                if( method_exists($view, 'getContent') ){
                    return new JsonResponse(json_decode($view->getContent()));
                }
            }
            return $view;
        }
        $view = $callback();
        $this->setStorage([[$key, $view]]);
        //
        return $view;
    }

    public function response($view)
    {
        return $this->newResponse($view);
    }

    public function newResponse($view)
    {
        $response = new Response($this->serve('template')->sanitizeView($view));
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        return $response;
    }
    
    protected function _getAttrLoop($obj, $attrs, $i, $onNull)
    {
        $size = sizeof($attrs);
        $attrToFetch = $attrs[$i];
        if( isset($obj[$attrToFetch]) ){
            $val = $obj[$attrToFetch];
            if($i == $size-1){
                return $val;
            }
            return $this->_getAttrLoop($val, $attrs, ++$i, $onNull);
        }
        return $onNull ?? null;
    }
}
