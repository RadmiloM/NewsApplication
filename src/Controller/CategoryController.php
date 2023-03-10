<?php

namespace App\Controller;
use App\Entity\Category;
use App\Entity\Article;
use App\Repository\CategoryRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Cache\Adapter\RedisAdapter;



#[Route('/user')]
class CategoryController extends AbstractController {
    
        private $categoryRepository;

        private $managerRegistry;


        public function __construct(CategoryRepository  $categoryRepository,ManagerRegistry $managerRegistry)
        {
            $this->categoryRepository = $categoryRepository;
            $this->managerRegistry = $managerRegistry;

        }

        /**
        * @Route("/createCategory", name="create_category", methods={"POST"})
        */
        public function createCategory(Request $request): JsonResponse
        {
           $data = json_decode($request->getContent(), true);

               $category = new Category();
               $category->setName($data['name']);

               $article = new Article();
               $article->setTitle($data['title']);
               $article->setContent($data['content']);
               $category->setArticle($article);

               $entityManager = $this->managerRegistry->getManager();
               $entityManager->persist($category);
               $entityManager->persist($article);
               $entityManager->flush();

               return new JsonResponse([],Response::HTTP_CREATED);
        }

        /**
        * @Route("/category/{id}", name="find_one_category", methods={"GET"})
        */
        public function findCategoryById($id): JsonResponse
        {
            $category = $this->categoryRepository->findOneBy(['id' => $id]);

            if(!$category) {
                        return new JsonResponse("category with id: " .$id. " does not exists");
                    }
                    
            $data = [
                'name' => $category->getName(),
                'title'=> $category->getArticle()->getTitle(),
                'content'=> $category->getArticle()->getContent()
            ];

            return new JsonResponse($data,Response::HTTP_OK);
        }

        /**
        * @Route("/categories", name="find_all_categories", methods={"GET"})
        */
        public function findAllCategories(): JsonResponse {

        $client = RedisAdapter::createConnection("redis://localhost:6379");
        $cache = new RedisAdapter($client,"categories_items");
        $cachedCategories = $cache->getItem("categories_items");

        if(!$cachedCategories->isHit()){
            $categories = $this->categoryRepository->findAll();

            $data = [];

            foreach ($categories as $category) {

                $data[] = [
                    'name' => $category->getName(),
                    'title'=> $category->getArticle()->getTitle(),
                    'content'=>$category->getArticle()->getContent()
                ];
            }

            $cachedCategories->set($data);
            $cachedCategories->expiresAfter(\DateInterval::createFromDateString('1 minute'));
            $cache->save($cachedCategories);
        }   
          else{
                
             $data= $cachedCategories->get();
        }
            
        return new JsonResponse($data, Response::HTTP_OK);
        }

        /**
        * @Route("/delete/category/{id}", name="delete_category", methods={"DELETE"})
        */
        public function deleteCategory($id): JsonResponse
        {
            $category = $this->categoryRepository->findOneBy(['id' => $id]);
            
            if(!$category) {
                        return new JsonResponse("category with id: " .$id. " does not exists");
                    }

            $entityManager = $this->managerRegistry->getManager();
            $entityManager->remove($category);
            $entityManager->flush();

            return new JsonResponse(Response::HTTP_OK);
        }


        /**
        * @Route("/edit/category/{id}", name="update_category", methods={"PUT"})
        */
        public function updateCategory($id, Request $request): JsonResponse
        {
            $category = $this->categoryRepository->findOneBy(['id' => $id]);
            if(!$category) {
                            return new JsonResponse("category with id: " .$id. " does not exists");
                        }

            $data = json_decode($request->getContent(), true);

            empty($data['name']) ? true : $category->setName($data['name']);

            $entityManager = $this->managerRegistry->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return new JsonResponse(Response::HTTP_OK);
        }
               
}