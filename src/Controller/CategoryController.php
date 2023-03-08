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

#[Route('/user')]
class CategoryController extends AbstractController {
        private $categoryRepository;

        public function __construct(CategoryRepository  $categoryRepository)
        {
            $this->categoryRepository = $categoryRepository;
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

               $entityManager = $this->getDoctrine()->getManager();
               $entityManager->persist($category);
               $entityManager->persist($article);
               $entityManager->flush();

               return new JsonResponse(Response::HTTP_CREATED);
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
                $categories = $this->categoryRepository->findAll();

                $data = [];

                foreach ($categories as $category) {

                    $data[] = [
                        'name' => $category->getName(),
                        'title'=> $category->getArticle()->getTitle(),
                        'content'=>$category->getArticle()->getContent()
                    ];
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

                   $this->categoryRepository->removeCategory($category);

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

                   $this->categoryRepository->updateCategory($category);

                   return new JsonResponse(Response::HTTP_OK);
               }



}