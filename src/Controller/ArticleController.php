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
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Cache\Adapter\RedisAdapter;


#[Route('/user')]
class ArticleController extends AbstractController {
    
        private $articleRepository;
        private $managerRegistry;

        public function __construct(ArticleRepository $articleRepository,ManagerRegistry $managerRegistry){
            $this->articleRepository = $articleRepository;
            $this->managerRegistry = $managerRegistry;
        }

        /**
        * @Route("/articles", name="find_all_articles", methods={"GET"})
        */
        public function findAllArticles(): JsonResponse
        {   
            $client = RedisAdapter::createConnection("redis://localhost:6379");
            $cache = new RedisAdapter($client,"articles_items");
            $cachedArticles = $cache->getItem("articles_items");
            
            if (!$cachedArticles->isHit()) {
                $articles = $this->articleRepository->findAll();
                $data = [];
        
                foreach ($articles as $article) {
                    $data[] = [
                        'title' => $article->getTitle(),
                        'content' => $article->getContent(),
                    ];
                }
        
                $cachedArticles->set($data);
                $cachedArticles->expiresAfter(\DateInterval::createFromDateString('1 minute'));
                $cache->save($cachedArticles);
            } 
            else {
                
                $data = $cachedArticles->get();
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

            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            return new JsonResponse([],Response::HTTP_CREATED);
        }

        /**
         * @Route("/article/{id}", name="find_one_article", methods={"GET"})
         */
        public function findArticleById($id): JsonResponse
        {
            $article = $this->articleRepository->findOneBy(['id' => $id]);

            if(!$article) {

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


            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

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
            
            $entityManager = $this->managerRegistry->getManager();
            $entityManager->remove($article);
            $entityManager->flush();

            return new JsonResponse(Response::HTTP_OK);
        }

}