<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\BloggyRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use DovStone\Bundle\SymfonyBlogAdminBundleMyNoSQLBased\Service\PleaseService;

/**
 * @Route("/")
 */
class AppController extends AbstractController
{
    public $perPage = 3;

    public function __construct(PleaseService $please, BloggyRepository $bRepo, UserRepository $uRepo)
    {
        $this->please = $please;
        $this->please->serve('execute_before')->appExecBeforeService();
        $this->please->prevContainer->get('service.execute_before')->__run();
        $this->User = $this->please->serve('security')->getUser();
        $this->crud = $this->please->serve('crud');
        $this->resServ = $this->please->serve('response');
        $this->bRepo = $bRepo;
        $this->uRepo = $uRepo;
    }

    /**
     * @Route("stars/{username}-{id}", name="readUser", requirements={"username":"([a-z0-9-]+)"})
    */
    public function readUser($username, $id)
    {
      return $this->crud->read([
        'collection' => 'bloggies',
        'finder' => function() use ($id) {
          return u()->find($id)->fetch();
        },
        'onFound' => function($user){
          $user = $this->uRepo->pop($user);
          $title = $user['username'];
          $this->please->setPost(array_merge([
            'title' => $title,
            'description' => $title,
            'keywords' => $title
          ], $user));
          return $this->renderTpl('star');
        } 
      ]);
    }

    /**
     * @Route("category/{slug}-{id}", name="readCategory", requirements={"category":"([a-z0-9-]+)"})
    */
    public function readCategory($slug, $id)
    {
      return $this->crud->readList([
        'collection' => 'bloggies',
        'finderCriteria' => function() use ($id) {
          return [
            [['type','==','news'], ['acf.props.parent','==',$id], ['published','==','on']],
            []
          ];
        },
        'perPage' => $this->perPage,
        'view' => function($items, $knp, $count) use ($id){
          $category = b()->find($id)->fetch();
          if($category){
            $title = ' "'.$category['title'].'"';
            $descKw = $category['title'];
          }
          $title = "Catégorie".$title ?? '';
          $this->please->setPost([ 'title' => $title, 'description' => $title, 'keywords' => $this->please->serve('string')->getTag($title) ]);
          $data = [
            'limit' => $this->perPage,
            'items' => $this->bRepo->pop($items),
            'count' => $count,
            'knp' => $knp,
          ];
          return $this->renderTpl('listing-by-category', compact('data'));
        } 
      ]);
    }

    /**
     * @Route("recherche", name="searchInTheSite")
    */
    public function searchInTheSite()
    {
      return $this->crud->readList([
        'collection' => 'bloggies',
        'finderCriteria' => function() {
          $q = trim($this->please->getRequestStackQuery()->get('q'));
          if($q !== ''){
              $criteria = [];
              $words = explode(' ', $q);
              $c = [];
              foreach ($words as $i => $word) {
                  if(!empty($word)){
                      $c[] = [
                        ['title','like',$word],
                        'or',
                        ['description','like',$word],
                        'or',
                        ['keywords','like',$word]
                      ];
                      if( $i < count($words)-1 ){ $c[] = 'and'; }
                  }
              }
              $criteria = array_merge($criteria, $c);
          }
          else {
            $criteria = ['__','==','__']; // just for return empty data
          }
          return [
              $criteria, 
              ['createdAt' => 'desc']
          ];
        },
        'perPage' => $this->perPage,
        'view' => function($items, $knp, $count){
          $q = $this->please->getRequestStackQuery()->get('q');
          $title = "Résultat de recherche: \"$q\"";
          $this->please->setPost([
            'title' => $title,
            'htmlTitle' => "Résultat de recherche: <br> \"$q\"",
            'description' => $title,
            'keywords' => $this->please->serve('string')->getTag($title)
          ]);
          $data = [
            'limit' => $this->perPage,
            'items' => $this->bRepo->pop($items),
            'count' => $count,
            'knp' => $knp,
          ];
          return $this->renderTpl('search-results', compact('data'));
        } 
      ]);
    }
    
    private function renderTpl(string $templateName, array $parameters = array(), $response = null)
    {
        return $this->please->cachableResponse(
            $this->renderView("layouts/{$templateName}.html.twig", $parameters, $response)
        );
    }
}
