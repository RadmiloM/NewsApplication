<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


#[Route('/user')]
class ArticleController extends AbstractController {

   

    private $articleRepository;

    public function __construct(ArticleRepository $articleRepository){
        $this->articleRepository = $articleRepository;
    }

    /**
 * @Route("/articles", name="find_all_articles", methods={"GET"})
 */
public function findAllArticles(): JsonResponse
{
    $articles = $this->articleRepository->findAll();
    $data = [];

    foreach ($articles as $article) {
        $data[] = [
            'title' => $article->getTitle(),
            'content' => $article->getContent(),
        ];
    }
    
    return new JsonResponse($data, Response::HTTP_OK);
}


    /**
     * @Route("/createArticle", name="create_article", methods={"POST"})
     */
    public function createArticle(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $article = new Article();
        $article->setContent($data['content']);
        $article->setTitle($data['title']);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($article);
        $entityManager->flush();

        return new JsonResponse(Response::HTTP_CREATED);
    }

        /**
     * @Route("/article/{id}", name="find_one_article", methods={"GET"})
     */
    public function findArticleById($id): JsonResponse
    {
        $article = $this->articleRepository->findOneBy(['id' => $id]);

        if(!$article){
            return new JsonResponse("article with id: " .$id. " does not exists");
        }

        $data = [
            'title' => $article->getTitle(),
            'content' => $article->getContent()
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/edit/article/{id}", name="update_article", methods={"PUT"})
     */
    public function updateArticle($id, Request $request): JsonResponse
    {
        $article = $this->articleRepository->findOneBy(['id' => $id]);

        if(!$article) {
               return new JsonResponse("article with id: " .$id. " does not exists");
            }

        $data = json_decode($request->getContent(), true);

        empty($data['title']) ? true : $article->setTitle($data['title']);
        empty($data['content']) ? true : $article->setContent($data['content']);

        $this->articleRepository->updateArticle($article);

        return new JsonResponse(Response::HTTP_OK);
    }

   /**
    * @Route("/delete/article/{id}", name="delete_article", methods={"DELETE"})
    */
   public function deleteArticle($id): JsonResponse
    {
       $article = $this->articleRepository->findOneBy(['id' => $id]);

        if(!$article) {
                      return new JsonResponse("article with id: " .$id. " does not exists");
           }

       $this->articleRepository->removeArticle($article);

       return new JsonResponse(Response::HTTP_OK);
   }

}