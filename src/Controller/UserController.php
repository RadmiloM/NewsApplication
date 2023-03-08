<?php

namespace App\Controller;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
class UserController extends AbstractController {

    private $userRepository;

    public function __construct(UserRepository $userRepository){
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/register", name="register_user", methods={"POST"})
     */
    public function createUser(Request $request,UserPasswordHasherInterface $passwordHasher): JsonResponse {
       $data = json_decode($request->getContent(), true);

       $user = new User();
       $user->setEmail($data['email']);
       $user->setRoles($data['roles']);
       $password = $data['password'];
       $hashedPassword = $passwordHasher->hashPassword($user,$password);
       $user->setPassword($hashedPassword);

       $category = new Category();
       $category->setName($data['name']);

       $article = new Article();
       $article->setTitle($data['title']);
       $article->setContent($data['content']);

       $user->setArticle($article);
       $category->setArticle($article);
       $user->setCategory($category);

       $entityManager = $this->getDoctrine()->getManager();
       $entityManager->persist($category);
       $entityManager->persist($article);
       $entityManager->persist($user);
       $entityManager->flush();


       return new JsonResponse("User with email " .$data['email']. " successfully registered",Response::HTTP_CREATED);
    }

   

}