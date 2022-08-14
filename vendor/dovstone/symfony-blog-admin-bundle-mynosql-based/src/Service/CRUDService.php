<?php

namespace DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service;

use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use DovStone\MyNoSQL\Cache;
use JasonGrimes\Paginator;
use Twig\Markup;

class CRUDService extends AbstractController
{
    private $please;

    public function __construct(PleaseService $please)
    {
        $this->please = $please;
        $this->log = $please->serve('log');
        $this->db = $this->please->getMyNoSQL();
    }

    public function create($params)
    {
        $params = array_merge([
            'collection' => null,
            'ifNotXHR' => null,
            'isGranted' => null,
            'isValid' => true,
            'validator' => [],
            'onInvalid' => function($errors){},
            'sanitizeRequest' => true,
            'sanitizeData' => true,
            'reservedWords' => ['criteria', 'props'],
            'callHook' => true,
            'sanitizer' => function(){},
            'onBadRequest' => function(){},
            'formView' => function() {},
            'onSuccess' => function($bloggy){}
        ], $params);

        $this->is = 'C';
        
        if( is_string($params['ifNotXHR']) ){
            if( !$this->please->isXHR() ) {
              return $this->please->XHRReturn($params);
            }
        }

        if(false === $this->please->serve('security')->__isGranted($params)){
            return is_callable($otherwise = $this->_otherwise($params)) ? $otherwise() : $this->please->serve('execute_before')->defaultFallback('otherwise');
        }

        $params['csrfToken'] = sha1(uniqid());
        
        if ($this->please->getRequest()->isMethod('POST')) {

            return $this->checkDataValidaty($params, function ($params)  {

                if( $params['isValid'] ){

                    $collection = $this->db->collection($params['collection']);

                    if( $params['sanitizeRequest'] === true ){ $this->_sanitizeRequest($params['collection']); }

                    $all = $_POST;//$all = $this->_getRequestAll();

                    //$sanitized = $params['sanitizer']($all, $collection, $this) ?? $all;
                    $sanitized =    $params['sanitizeData'] === false
                                  ? ($params['sanitizer']($all, $collection, $this) ?? [])
                                  : array_merge($all, $params['sanitizer']($all, $collection, $this) ?? $all) ; // <<- 30.07.22

                    if( isset($this->isBadRequest) ){ return $params['onBadRequest'](); } // <<- 21.04.22

                    if( $params['sanitizeData'] === true ){
                        $data = $this->_sanitizeData($params['collection'], $sanitized, $params); }
                        $data = $this->_sanitizeDataAnyway($params['collection'], $data, $params); // <<- 10.08.22
                        $data = array_merge($data ?? [], $sanitized ?? []); // <<- 22.07.21
                        $data = $this->_sanitizeDataAnyway($params['collection'], $data, $params); // <<- 12.08.22

                    if($params['callHook'] === true){ // <<- 30.07.22
                        $this->_setHookSanitizer(); // <<- 08.02.22
                        $hookData = $this->_getHookSanitizer($data['type'] ?? $data['roles'] ?? null, $data); // <<- 22.07.21
                        $data = array_merge($data, $hookData ?? []); // <<- 22.07.21
                    }

                    $data = array_diff_key($data, array_flip(['_ajaxify', 'token', 'ident', 'psw', 'confirm_psw', 'new_psw', 'confirm_new_psw', 'csrfToken', '_redirect']));
                    //
                    //////$data['sha1'] = sha1(json_encode($data));
                    //
                    $data = $this->dismissReservedWords($data, $params);

                    $data = $collection->insert($data);
                    //
                    ////// stand by //////$this->log->put('success', 'Enregistrement', $data);
                    //
                    $this->deleteCache($data);
                    //
                    return $params['onSuccess']($data, $params);
                }
                return $params['formView']($params);
            });
        }

        return $params['formView']($params);
    }
    
    public function basicCreate($params)
    {
        $params = array_merge([
            'collection' => null,
            'ifNotXHR' => null,
            'isGranted' => null,
            'isValid' => true,
            'validator' => [],
            'onInvalid' => function($errors){},
            'sanitizeRequest' => true,
            'sanitizeData' => true,
            'reservedWords' => ['criteria', 'props'],
            'callHook' => true,
            'sanitizer' => function(){},
            'onBadRequest' => function(){},
            'onSuccess' => function($bloggy){},
            'onError' => function(){}
        ], $params);

        $this->is = 'C';
        
        if( is_string($params['ifNotXHR']) ){
            if( !$this->please->isXHR() ) {
              return $this->please->XHRReturn($params);
            }
        }

        if(false === $this->please->serve('security')->__isGranted($params)){
            return is_callable($otherwise = $this->_otherwise($params)) ? $otherwise() : $this->please->serve('execute_before')->defaultFallback('otherwise');
        }

        return $this->checkDataValidaty($params, function ($params)  {

            if( $params['isValid'] ){

                $collection = $this->db->collection($params['collection']);
            
                if( $params['sanitizeRequest'] === true ){ $this->_sanitizeRequest($params['collection']); }

                $all = $_POST;//$all = $this->_getRequestAll();

                //$sanitized = $params['sanitizer']($all, $collection, $this) ?? $all;
                $sanitized =    $params['sanitizeData'] === false
                              ? ($params['sanitizer']($all, $collection, $this) ?? [])
                              : array_merge($all, $params['sanitizer']($all, $collection, $this) ?? $all) ; // <<- 30.07.22

                if( isset($this->isBadRequest) ){ return $params['onBadRequest'](); } // <<- 21.04.22

                if( $params['sanitizeData'] === true ){
                    $data = $this->_sanitizeData($params['collection'], $sanitized, $params); }
                    $data = $this->_sanitizeDataAnyway($params['collection'], $data, $params); // <<- 10.08.22
                    $data = array_merge($data ?? [], $sanitized ?? []); // <<- 22.07.21
                    $data = $this->_sanitizeDataAnyway($params['collection'], $data, $params); // <<- 12.08.22

                if($params['callHook'] === true){ // <<- 30.07.22
                    $this->_setHookSanitizer(); // <<- 08.02.22
                    $hookData = $this->_getHookSanitizer($data['type'] ?? $data['roles'] ?? null, $data); // <<- 22.07.21
                    $data = array_merge($data, $hookData ?? []); // <<- 22.07.21
                }

                $data = array_diff_key($data, array_flip(['_ajaxify', 'token', 'ident', 'psw', 'confirm_psw', 'new_psw', 'confirm_new_psw', 'csrfToken', '_redirect']));
                //
                //////$data['sha1'] = sha1(json_encode($data));
                //
                $data = $this->dismissReservedWords($data, $params);

                $data = $collection->insert($data);
                //
                ////// stand by //////$this->log->put('success', 'Enregistrement', $data);
                //
                $this->deleteCache($data);
                //
                return $params['onSuccess']($data, $params);
            }
            return $params['onError']();
        });
    }
    
    public function read($params)
    {
        $params = array_merge([
            'collection' => null,
            'ifNotXHR' => null,
            'isGranted' => null,
            'finder' => function($collection){},
            'onNotFound' => function(){},
            'onFound' => function($document){}
        ], $params);

        $this->is = 'R';
        
        if( is_string($params['ifNotXHR']) ){
            if( !$this->please->isXHR() ) {
              return $this->please->XHRReturn($params);
            }
        }
        if(false === $this->please->serve('security')->__isGranted($params)){
            return is_callable($otherwise = $this->_otherwise($params)) ? $otherwise() : $this->please->serve('execute_before')->defaultFallback('otherwise');
        }

        $collection = $this->db->collection($params['collection']);

        $document = is_callable($params['finder']) ? $params['finder']($collection) : $params['finder'];

        if(!$document){
            return $params['onNotFound']();
        }

        return $params['onFound']($document);
    }
     
    public function readList($params)
    {
        $params = array_merge([
            'collection' => null,
            'ifNotXHR' => null,
            'isGranted' => null,
            'finder' => function($collection){},
            'finderCriteria' => function($collection){},
            'perPage' => 15,
            'view' => function($documents, $paginator, $total, $params){}
        ], $params);

        $this->is = 'R';
        
        if( is_string($params['ifNotXHR']) ){
            if( !$this->please->isXHR() ) {
              return $this->please->XHRReturn($params);
            }
        }

        if(false === $this->please->serve('security')->__isGranted($params)){
            return is_callable($otherwise = $this->_otherwise($params)) ? $otherwise() : $this->please->serve('execute_before')->defaultFallback('otherwise');
        }

        $collection = $this->db->collection($params['collection']);

        //$documents = is_callable($params['finder']) ? $params['finder']($collection) : $params['finder'];

        $finderCriteria = is_callable($params['finderCriteria']) ? $params['finderCriteria']($collection) : $params['finderCriteria'];
        
        $total = $collection->countBy($finderCriteria[0] ?? [])->fetch();
        $documents = $collection
                    ->findBy($finderCriteria[0] ?? [])
                    ->orderBy($finderCriteria[1] ?? [])
                    ->limit($params['perPage'])
                    ->fetch()
                    ;
        $paginator = $this->please->mimicKnpPaginator($total, $params['perPage']);

        return $params['view']($documents, $paginator, $total, $params);
    }
    
    public function update($params)
    {
        $params = array_merge([
            'collection' => null,
            'ifNotXHR' => null,
            'isGranted' => null,
            'isValid' => true,
            'validator' => [],
            'onInvalid' => function($errors){},
            'finder' => function($collection) {},
            'onNotFound' => function(){
                return new JsonResponse([
                    'alert' => ['Donnée introuvable', 'danger'],
                    'reload' => false
                ]);
            },
            'sanitizeRequest' => true,
            'sanitizeData' => true,
            'reservedWords' => ['criteria', 'props'],
            'callHook' => true,
            'sanitizer' => function(){},
            'onBadRequest' => function(){},
            'formView' => function($document) {},
            'onSuccess' => function($document){}
        ], $params);

        $this->is = 'U';
        
        if( is_string($params['ifNotXHR']) ){
            if( !$this->please->isXHR() ) {
              return $this->please->XHRReturn($params);
            }
        }

        if(false === $this->please->serve('security')->__isGranted($params)){
            return is_callable($otherwise = $this->_otherwise($params)) ? $otherwise() : $this->please->serve('execute_before')->defaultFallback('otherwise');
        }

        $collection = $this->db->collection($params['collection']);

        $document = $params['finder']($collection);
        
        if(!$document){
            return $params['onNotFound']();
        }

        $document['csrfToken'] = sha1(uniqid());
        
        if ($this->please->getRequest()->isMethod('POST')) {

            return $this->checkDataValidaty($params, function ($params) use ($document, $collection) {

                if( $params['isValid'] ){

                    if( $params['sanitizeRequest'] === true ){ $this->_sanitizeRequest($params['collection'], $document); }

                    $all = $_POST;//$all = $this->_getRequestAll();
                    
                    //$sanitized = array_merge($all, $params['sanitizer']($all, $document, $collection, $this) ?? []);
                    $sanitized =    $params['sanitizeData'] === false
                                  ? ($params['sanitizer']($all, $document, $collection, $this) ?? [])
                                  : array_merge($all, $params['sanitizer']($all, $document, $collection, $this) ?? $all) ; // <<- 30.07.22
                    
                    if( isset($this->isBadRequest) ){ return $params['onBadRequest'](); } // <<- 21.04.22

                    //$sanitized = array_merge($document, $sanitized);
        
                    if( $params['sanitizeData'] === true ){
                        $data = $this->_sanitizeData($params['collection'], $sanitized, $params); }
                        $data = $this->_sanitizeDataAnyway($params['collection'], $document, $params); // <<- 10.08.22
                        $data = array_merge($data ?? [], $sanitized ?? []); // <<- 22.07.21
                        $data = $this->_sanitizeDataAnyway($params['collection'], $data, $params); // <<- 12.08.22

                    if($params['callHook'] === true){ // <<- 30.07.22
                        $this->_setHookSanitizer(); // <<- 08.02.22
                        $hookData = $this->_getHookSanitizer($data['type'] ?? $data['roles'] ?? null, $data); // <<- 22.07.21
                        $data = array_merge($data, $hookData ?? []); // <<- 22.07.21
                    }

                    $data = array_diff_key($data, array_flip(['_ajaxify', 'id', 'token', 'ident', 'psw', 'confirm_psw', 'new_psw', 'confirm_new_psw', 'csrfToken', '_continue']));
                    //
                    //////$data['sha1'] = sha1(json_encode($data));
                    //
                    //$data['updatedAt'] = $sanitized['createdAt'] ?? (new \DateTime())->format("Y-m-d H:i:s");
                    $data = $this->dismissReservedWords($data, $params);

                    $data = $collection->update(attr($document, 'id'), $data);
                    //
                    ////// stand by //////$this->log->put('info', 'Modification', $data);
                    //
                    $this->deleteCache($data);
                    //
                    return $params['onSuccess']($data);
                }
                return $params['formView']($document);
            });
        }

        return $params['formView']($document);
    }
    
    public function basicUpdate($params)
    {
        $params = array_merge([
            'collection' => null,
            'ifNotXHR' => null,
            'isGranted' => null,
            'isValid' => true,
            'validator' => [],
            'onInvalid' => function($errors){},
            'log' => true,
            'finder' => function($collection) {},
            'onNotFound' => function(){
                return new JsonResponse([
                    'alert' => ['Donnée introuvable', 'danger'],
                    'reload' => false
                ]);
            },
            'sanitizeRequest' => true,
            'sanitizeData' => true,
            'reservedWords' => ['criteria', 'props'],
            'callHook' => true,
            'sanitizer' => function($posted, $document, $collection, $params){},
            'onBadRequest' => function($posted, $document, $collection, $params){},
            'onSuccess' => function($document){}
        ], $params);

        $this->is = 'U';
        
        if( is_string($params['ifNotXHR']) ){
            if( !$this->please->isXHR() ) {
              return $this->please->XHRReturn($params);
            }
        }

        if(false === $this->please->serve('security')->__isGranted($params)){
            return is_callable($otherwise = $this->_otherwise($params)) ? $otherwise() : $this->please->serve('execute_before')->defaultFallback('otherwise');
        }

        $collection = $this->db->collection($params['collection']);

        $document = $params['finder']($collection);
        
        if(!$document){
            return $params['onNotFound']();
        }

        return $this->checkDataValidaty($params, function ($params) use ($document, $collection) {

            if( $params['isValid'] ){

                if( $params['sanitizeRequest'] === true ){
                    $this->_sanitizeRequest($params['collection'], $document);
                }

                $all = $_POST;//$all = $this->_getRequestAll();

                //$sanitized = array_merge($all, $params['sanitizer']($all, $document, $collection, $this) ?? $all);
                $sanitized =    $params['sanitizeData'] === false
                                ? ($params['sanitizer']($all, $document, $collection, $this) ?? [])
                                : array_merge($all, $params['sanitizer']($all, $document, $collection, $this) ?? $all) ; // <<- 30.07.22

                if( isset($this->isBadRequest) ){ return $params['onBadRequest'](); } // <<- 21.04.22

                //$sanitized = array_merge($document, $sanitized);

                if( $params['sanitizeData'] === true ){
                $data = $this->_sanitizeData($params['collection'], $sanitized, $params); }
                $data = $this->_sanitizeDataAnyway($params['collection'], $document, $params); // <<- 10.08.22
                $data = array_merge($data ?? [], $sanitized ?? []); // <<- 22.07.21
                $data = $this->_sanitizeDataAnyway($params['collection'], $data, $params); // <<- 12.08.22

                if($params['callHook'] === true){ // <<- 30.07.22
                    $this->_setHookSanitizer(); // <<- 08.02.22
                    $hookData = $this->_getHookSanitizer($data['type'] ?? $data['roles'] ?? null, $data); // <<- 22.07.21
                    $data = array_merge($data, $hookData ?? []); // <<- 22.07.21
                }

                $data = array_diff_key($data, array_flip(['_ajaxify', 'id', 'token', 'ident', 'psw', 'confirm_psw', 'new_psw', 'confirm_new_psw', 'csrfToken', '_continue']));
                //
                //////$data['sha1'] = sha1(json_encode($data));
                //
                //$data['updatedAt'] = $sanitized['createdAt'] ?? (new \DateTime())->format("Y-m-d H:i:s");
                //
                $data = $this->dismissReservedWords($data, $params);

                $data = $collection->update(attr($document, 'id'), $data);
                //
                if( $params['log'] === true ){
                    ////// stand by //////$this->log->put('info', 'Modification', $data);
                }
                //
                $this->deleteCache($data);
                //
                return $params['onSuccess']($data);
            }
        });
    }
    
    public function delete($params)
    {
        $params = array_merge([
            'collection' => null,
            'ifNotXHR' => null,
            'isGranted' => null,
            'finder' => function($collection) {},
            'onNotFound' => function(){
                return new JsonResponse([
                    'alert' => ['Donnée introuvable', 'danger'],
                    'reload' => false
                ]);
            },
            'onSuccess' => function($document){}
        ], $params);

        $this->is = 'D';
        
        if( is_string($params['ifNotXHR']) ){
            if( !$this->please->isXHR() ) {
              return $this->please->XHRReturn($params);
            }
        }

        if(false === $this->please->serve('security')->__isGranted($params)){
            return is_callable($otherwise = $this->_otherwise($params)) ? $otherwise() : $this->please->serve('execute_before')->defaultFallback('otherwise');
        }

        $collection = $this->db->collection($params['collection']);

        $document = $params['finder']($collection);
        
        if(!$document){
            return $params['onNotFound']();
        }
        //
        if( (isset($document['type']) && $document['type'] !== 'log') || isset($document['roles']) ){
            ////// stand by //////$this->log->put('danger', 'Suppression', $document);
        }
        //
        $collection->delete(attr($document, 'id'));
        //
        $this->deleteCache($document);
        //
        return $params['onSuccess']($document);
    }

    public function checkDataValidaty($params, callable $onValid)
    {
        $validationFailed = false;
        $errors = [];

        if (isset($params['validator']) && is_array($params['validator'])) {

            $postedData = $this->_getRequestAll();

            foreach ($params['validator'] as $fieldName => $fieldCallable) {
                $result = $fieldCallable($postedData, $this);
                if (is_string($result)) {
                    $errors[$fieldName] = $result;
                    $validationFailed = true;
                }
            }
        }

        if ($validationFailed === true) {
            if( isset($params['onInvalid']) && is_callable($params['onInvalid']) ){
                return $params['onInvalid']( $errors );
            }
            elseif (isset($params['formView'])) {
                return $params['formView']( $errors );
            }
        }

        return $onValid($params);
    }

    public function deleteCache($data): void
    {
        $id = $data['id'] ?? null;
        $type = $data['type'] ?? null;
        
        /*if($id){
            (new \DovStone\MyNoSQL\SQLHandler($this->please->getMyNoSQL()->getPDO()))
                ->_commit("DELETE FROM `cache` WHERE `uid`= $id");
        }*/

        // lets delete cached href
        if($id){
            $res = b()->findOneBy([['type','==','href'],['postId','==',$id]])->fetch();
            if( $res ){
                b()->deleteBy([['type','==','href'],['postId','==',$id]]);
                $href = str_ireplace($this->please->serve('url')->getUrl(), '', $res['href']);
                $words = explode('/', $href);
                if($words && strpos($href, '.html')==false){
                    foreach ($words as $word) {
                        b()->deleteBy([['type','==','href'],['href','like',$word]]);
                    }
                }
            }
        }
        //
        if( in_array($type, ['menu', 'page']) || (isset($data['inMenu']) && in_array($data['inMenu'], ['on', 'yes'])) ){
            $dirServ = $this->please->serve('dir');
            $files = $dirServ->listFiles('var/cache/swagg');
            if($files){
                foreach ($files as $filename) {
                    if(strpos($filename, 'sys-nav') !== false){
                        unlink($dirServ->dirPath("var/cache/swagg/$filename"));
                    }
                }
            }
            $this->please->unsetStorage($type);
        }
        //
        $this->please->unsetStorage($type);
        //
        (new Cache('fetchall'))->rmdir();
    }

    public function setBadRequest()
    {
        $this->isBadRequest = true;
        return $this;
    }

    private function _otherwise($params)
    {
        if( is_array($params['isGranted']) && isset($params['isGranted']['otherwise']) && is_callable($params['isGranted']['otherwise']) ){
            return $params['isGranted']['otherwise'];
        }
        return false;
    }

    private function _getRequestAll()
    {
        return $this->please->getRequest()->request->all();
        return $_POST;
    }

    private function _setHookSanitizer()
    {
        $fileSystem = new Filesystem();
        if ($fileSystem->exists($this->please->serve('dir')->getProjectDir() . '/src/Service/ExecuteBeforeService.php')) {
            if (method_exists(\App\Service\ExecuteBeforeService::class, '__hookSanitizer') && $this->please->prevContainer->has('service.execute_before')) {
                $this->hookData = $this->please->prevContainer->get('service.execute_before')->__hookSanitizer();
            }
        }
    }

    private function _getHookSanitizer($typeOrRole, $data)
    {
        if(isset($this->hookData)){
            $bArr = ['B', 'b', 'Bloggy', 'bloggy', 'Bloggies', 'bloggies'];
            $uArr = ['U', 'u', 'User', 'user'];

            foreach ($this->hookData as $collectionKey => $hookData) {
                if( in_array($collectionKey, $bArr) ){
                    if(is_string($typeOrRole)){
                        $hookCallback = $hookData[$typeOrRole] ?? null;
                        if( $hookCallback ){
                            return array_merge($data, $hookCallback($data, $_POST) ?? []);
                        }
                    }
                }
                elseif( in_array($collectionKey, $uArr) ){
                    foreach (attr($data, 'roles', []) as $role) {
                        $hookCallback = $hookData[$role] ?? null;
                        if( $hookCallback ){
                            return array_merge($data, $hookCallback($data, $_POST) ?? []);
                        }
                    }
                }
            }
        }
        return [];
    }

    private function _sanitizeRequest($collectionName, $document=null){
        
        $req = $this->please->getRequest()->request;
        
        foreach(
            in_array($collectionName, [
                'Bloggies', 'bloggies',
                'Bloggy', 'bloggy',
                'B', 'b'
            ]) ? [

                'title', 'type', 'parent', 'secondTitle', 'description',
                'customHref', 'slug', 'keywords', 'name', 'linkType', 'acfType', 'layout', 'layoutSingle', 'card', 'inMenu', 'image', 'video',
                'published', 'allowComments', 'sections', 'html', 'extraData', 'user', 'createdAt', 'updatedAt'

            ] : [

                'roles', 'password', 'oldPassword', 'username', 'usernameSlugged',
                'forgotToken', 'mle', 'lastname', 'firstname', 'telephone', 'email',
                'granted', 'enabled', 'user', 'createdAt', 'updatedAt'

            ] as $key){
            if( !isset($req->all()[$key]) ){
                $req->set($key, $document[$key] ?? '');
                //$_POST[$key] = $document[$key] ?? '';
            }
        }
    }

    private function _sanitizeData($collectionName, $s, $params)
    {
        $strServ = $this->please->serve('string');
        $p = $_POST; // $p = $this->_getRequestAll();

        if( $params['sanitizeRequest'] === false ){
            return array_merge($p, $s);
        }

        if( in_array($collectionName, [
            'Bloggies', 'bloggies',
            'Bloggy', 'bloggy',
            'B', 'b'
        ]) ){
            $p['type'] = $s['type'] ?? $p['type'] ?? 'page';

            $title = $s['title'] ?? $p['title'] ?? $p['type'] .'-'. rand(0, 9999);

            $p['title'] = $title;
            $p['parent'] = $s['parent'] ?? $p['parent'] ?? null;

            // mendatory but visually optionnal
            $secT = $s['secondTitle'] ?? $p['secondTitle'] ?? $title;
                $p['secondTitle'] = $secT ?: $title;
                $s['secondTitle'] = $p['secondTitle'] ?: $title;

            // mendatory but visually optionnal
            $desc = $s['description'] ?? $p['description'] ?? $title;
                $p['description'] = $desc ?: $title;
                $s['description'] = $p['description'] ?: $title;

            $p['customHref'] = !empty($p['customHref']) ? $p['customHref'] : '';

            // mendatory but visually optionnal
            $slug = $s['slug'] ?? $p['slug'] ?? $title;
            $p['slug'] = $slug ?: $title;
            $s['slug'] = $strServ->getSlug($p['slug'] ?: $title);

            // mendatory but visually optionnal
            $keywords = $s['keywords'] ?? $p['keywords'] ?? $title;
            $p['keywords'] = $keywords ?: $title;
            $s['keywords'] = $strServ->getTag($p['keywords'] ?: $title);

            // mendatory but visually optionnal
            $name = $s['name'] ?? $p['name'] ?? $title;
            $p['name'] = $name ?: $title;
            $s['name'] = $strServ->getSlug($p['name'] ?: $title);

            $p['linkType'] = $s['linkType'] ?? $p['linkType'] ?? 'article';
                $s['linkType'] = $p['linkType'] ?: 'article';

            $p['acfType'] = $s['acfType'] ?? $p['acfType'] ?? 'default';
            $p['layout'] = $s['layout'] ?? $p['layout'] ?? 'default';
            $p['layoutSingle'] = $s['layoutSingle'] ?? $p['layoutSingle'] ?? 'default';
            $p['card'] = $s['card'] ?? $p['card'] ?? 'default';

            $p['inMenu'] = $s['inMenu'] = ($_POST['inMenu'] ?? $s['published'] ?? $p['inMenu'] ?? 'off'); // checkbox
            $p['published'] = $s['published'] = ($_POST['published'] ?? $s['published'] ?? $p['published'] ?? 'on'); // checkbox
            $p['allowComments'] = $s['allowComments'] = ($_POST['allowComments'] ?? $s['allowComments'] ?? $p['allowComments'] ?? 'on'); // checkbox

            $p['sections'] = $s['sections'] ?? $p['sections'] ?? [];
            $p['html'] = $s['html'] ?? $p['html'] ?? '';
            $p['extraData'] = $s['extraData'] ?? $p['extraData'] ?? '';

            $p['createdAt'] = $s['createdAt'] ?? $p['createdAt'] ?? (new \DateTime())->format("Y-m-d H:i:s");
                $s['createdAt'] = $p['createdAt'] ?: (new \DateTime())->format("Y-m-d H:i:s");

            $p['updatedAt'] = $s['updatedAt'] = (new \DateTime())->format("Y-m-d H:i:s");
                $s['updatedAt'] = $p['updatedAt'] ?: (new \DateTime())->format("Y-m-d H:i:s");

            $p['user'] = $s['user'] ?? $p['user'] ?? $this->please->getUser()['id'] ?? null;

        }
        else if( in_array($collectionName, [
            'Users', 'users',
            'User', 'user',
            'U', 'u'
        ]) ){

            $username = $s['username'] ?? $p['username'] ?? 'utilisateur-' . rand(0, 9999);
    
            //$p['role'] = strtolower($s['role'] ?? 'guest' );
            //$p['roles'] = $s['roles'] ?? ['title' => 'Invité', 'slug' => 'guest', 'description' => "Aucun action sur le Back-office"];
            $p['roles'] = $s['roles'] ?? $p['roles'] ?? ['guest'];
            
            $p['password'] = $s['password'] ?? $p['password'] ?: sha1('000000');
                $s['password'] = $p['password'] ?: sha1('000000');

            //$p['oldPassword'] = $s['password'];
            $p['username'] = $s['username'] ?? $username;
            $p['usernameSlugged'] = $this->please->serve('string')->getSlug($p['username']);
            $p['forgotToken'] = $s['forgotToken'] ?? $p['forgotToken'] ?? null;
            $p['mle'] = $s['mle'] ?? $p['mle'] ?? substr(md5(substr(uniqid(''), 0, 20)), 0, 5);
                $s['mle'] = $p['mle'] ?: substr(md5(substr(uniqid(''), 0, 20)), 0, 5);
            //$p['lastname'] = $s['lastname'] ?? $p['lastname'] ?? $username;
            //$p['firstname'] = $s['firstname'] ?? $p['firstname'] ?? $username;
            $p['telephone'] = $s['telephone'] ?? $p['telephone'] ?? '';
            $p['email'] = $s['email'] ?? $p['email'] ?? '';
            $p['granted'] = $s['granted'] ?? $p['granted'] ?? 'on';
                $s['granted'] = $p['granted'] ?: 'on';

            $p['createdAt'] = $s['createdAt'] ?? $p['createdAt'] ?? (new \DateTime())->format("Y-m-d H:i:s");
                $s['createdAt'] = $p['createdAt'] ?: (new \DateTime())->format("Y-m-d H:i:s");
                
            $p['updatedAt'] = $s['updatedAt'] = (new \DateTime())->format("Y-m-d H:i:s");
                $s['updatedAt'] = $p['updatedAt'] ?: (new \DateTime())->format("Y-m-d H:i:s");
                
            $p['user'] = $s['user'] ?? $p['user'] ?? $this->please->getUser()['id'] ?? null;
        }

        return array_merge($p, $s);
    }

    private function _sanitizeDataAnyway($collectionName, $s, $params)
    {
        $strServ = $this->please->serve('string');
        $p = $_POST;

        if( $params['sanitizeRequest'] === false ){
            $p = $s;
        }

        if( in_array($collectionName, [
            'Bloggies', 'bloggies',
            'Bloggy', 'bloggy',
            'B', 'b'
        ]) ){
            $p['type'] = $s['type'] ?? $p['type'] ?? 'page';

            $title = $s['title'] ?? $p['title'] ?? $p['type'] .'-'. rand(0, 9999);

            $p['title'] = $title;

            // mendatory but visually optionnal
            $secT = $s['secondTitle'] ?? $p['secondTitle'] ?? $title;
                $p['secondTitle'] = $secT ?: $title;
                $s['secondTitle'] = $p['secondTitle'] ?: $title;

            // mendatory but visually optionnal
            $desc = $s['description'] ?? $p['description'] ?? $title;
                $p['description'] = $desc ?: $title;
                $s['description'] = $p['description'] ?: $title;

            // mendatory but visually optionnal
            $slug = $s['slug'] ?? $p['slug'] ?? $title;
                $p['slug'] = $slug ?: $title;
                $s['slug'] = $strServ->getSlug($p['slug'] ?: $title);

            // mendatory but visually optionnal
            $name = $s['name'] ?? $p['name'] ?? $title;
                $p['name'] = $name ?: $title;
                $s['name'] = $strServ->getSlug($p['name'] ?: $title);

            // mendatory but visually optionnal
            $keywords = $s['keywords'] ?? $p['keywords'] ?? $title;
                $p['keywords'] = $keywords ?: $title;
                $s['keywords'] = $strServ->getTag($p['keywords'] ?: $title);

            $p['published'] = $s['published'] = ($_POST['published'] ?? $s['published'] ?? $p['published'] ?? 'on'); // checkbox

            $p['createdAt'] = $s['createdAt'] ?? $p['createdAt'] ?? (new \DateTime())->format("Y-m-d H:i:s");
                $s['createdAt'] = $p['createdAt'] ?: (new \DateTime())->format("Y-m-d H:i:s");

            $p['updatedAt'] = $s['updatedAt'] = (new \DateTime())->format("Y-m-d H:i:s");
                $s['updatedAt'] = $p['updatedAt'] ?: (new \DateTime())->format("Y-m-d H:i:s");

            $p['user'] = $s['user'] ?? $p['user'] ?? $this->please->getUser()['id'] ?? null;

        }
        else if( in_array($collectionName, [
            'Users', 'users',
            'User', 'user',
            'U', 'u'
        ]) ){

            $username = $s['username'] ?? $p['username'] ?? 'utilisateur-' . rand(0, 9999);
    
            //$p['role'] = strtolower($s['role'] ?? 'guest' );
            //$p['roles'] = $s['roles'] ?? ['title' => 'Invité', 'slug' => 'guest', 'description' => "Aucun action sur le Back-office"];
            $p['roles'] = $s['roles'] ?? $p['roles'] ?? ['guest'];
            
            $p['password'] = $s['password'] ?? $p['password'] ?: sha1('000000');
                $s['password'] = $p['password'] ?: sha1('000000');

            //$p['oldPassword'] = $s['password'];
            $p['username'] = $s['username'] ?? $username;
            $p['usernameSlugged'] = $this->please->serve('string')->getSlug($p['username']);
            $p['forgotToken'] = $s['forgotToken'] ?? $p['forgotToken'] ?? null;
            $p['mle'] = $s['mle'] ?? $p['mle'] ?? substr(md5(substr(uniqid(''), 0, 20)), 0, 5);
                $s['mle'] = $p['mle'] ?: substr(md5(substr(uniqid(''), 0, 20)), 0, 5);
            //$p['lastname'] = $s['lastname'] ?? $p['lastname'] ?? $username;
            //$p['firstname'] = $s['firstname'] ?? $p['firstname'] ?? $username;
            $p['telephone'] = $s['telephone'] ?? $p['telephone'] ?? '';
            $p['email'] = $s['email'] ?? $p['email'] ?? '';
            $p['granted'] = $s['granted'] ?? $p['granted'] ?? 'on';
                $s['granted'] = $p['granted'] ?: 'on';

            $p['createdAt'] = $s['createdAt'] ?? $p['createdAt'] ?? (new \DateTime())->format("Y-m-d H:i:s");
                $s['createdAt'] = $p['createdAt'] ?: (new \DateTime())->format("Y-m-d H:i:s");
                
            $p['updatedAt'] = $s['updatedAt'] = (new \DateTime())->format("Y-m-d H:i:s");
                $s['updatedAt'] = $p['updatedAt'] ?: (new \DateTime())->format("Y-m-d H:i:s");

            $p['user'] = $s['user'] ?? $p['user'] ?? $this->please->getUser()['id'] ?? null;
        }
        return array_merge($p, $s);
    }

    private function dismissReservedWords($data, $params)
    {
        foreach ($data as $k => $v) {
            foreach ($params['reservedWords'] as $word) {
                if( isset($data[$word]) ){
                    unset($data[$word]);
                }
            }
        }
        return $data;
    }
}