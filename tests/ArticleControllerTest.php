<?php

use PHPUnit\Framework\TestCase;
use App\Entity\Article;
use App\Controller\ArticleController;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;






class ArticleControllerTest extends TestCase {
    public function testingWhenArticleWithProvidedIdIsNotPresentInDatabase(): void {
        
    $articleRepositoryMock = $this->createMock(ArticleRepository::class);
    $managerRegistryMock = $this->createMock(ManagerRegistry::class);

    $articleController = new ArticleController($articleRepositoryMock,$managerRegistryMock);

    $articleRepositoryMock->expects($this->once())
                            ->method('findOneBy')
                            ->with(['id' => 1])
                            ->willReturn(null);

    $response = $articleController->findArticleById(1);

    $this->assertInstanceOf(JsonResponse::class, $response);

    $this->assertEquals('"article with id: 1 does not exists"', $response->getContent());

    }

    public function testingWhenArticleWithProvidedIdIsPresentInDatabase(): void {

    $article = new Article();
    $article->setTitle("Hello World");
    $article->setContent("Hello world text");
    
    $articleRepositoryMock = $this->createMock(ArticleRepository::class);
    $managerRegistryMock = $this->createMock(ManagerRegistry::class);


    $articleController = new ArticleController($articleRepositoryMock,$managerRegistryMock);

    $articleRepositoryMock->expects($this->once())
                            ->method('findOneBy')
                            ->with(['id' => 1])
                            ->willReturn($article);

    $response = $articleController->findArticleById(1);

    $this->assertInstanceOf(JsonResponse::class, $response);

    $responseData = json_decode($response->getContent(), true);

    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    $this->assertEquals($article->getTitle(), $responseData['title']);
    $this->assertEquals($article->getContent(), $responseData['content']);

    }

    public function testFindingAllArticles(): void {

    $firstArticle = new Article();
    $firstArticle->setTitle("Football");
    $firstArticle->setContent("Tomorrow we are expecting new game");

    $secondArticle = new Article();
    $secondArticle->setTitle("Basketball");
    $secondArticle->setContent("We are playing basketball today");

    $thirdArticle = new Article();
    $thirdArticle->setTitle("Programming");
    $thirdArticle->setContent("Symfony is great framework");

    $articleRepositoryMock = $this->createMock(ArticleRepository::class);
    $managerRegistryMock = $this->createMock(ManagerRegistry::class);

    
    $articleRepositoryMock->expects($this->once())
    ->method('findAll')
    ->willReturn([$firstArticle, $secondArticle,$thirdArticle]);

    $articleController = new ArticleController($articleRepositoryMock,$managerRegistryMock);

    $response = $articleController->findAllArticles();

    $data = json_decode($response->getContent(), true);

    $this->assertInstanceOf(JsonResponse::class, $response);

    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    $this->assertEquals(3, count($data));
    $this->assertEquals("Football", $data[0]['title']);
    $this->assertEquals("Tomorrow we are expecting new game", $data[0]['content']);
    $this->assertEquals("Basketball", $data[1]['title']);
    $this->assertEquals("We are playing basketball today", $data[1]['content']);
    $this->assertEquals("Programming", $data[2]['title']);
    $this->assertEquals("Symfony is great framework", $data[2]['content']);

    }

    public function testCreatingNewArticle(): void {

    $articleData = [
        'title' => 'Hello World',
        'content' => 'Hello world content'
    ];

    $jsonArticleData = json_encode($articleData);

    $request = new Request([], [], [], [], [], [], $jsonArticleData);

    $entityManagerMock = $this->createMock(EntityManagerInterface::class);
    $entityManagerMock->expects($this->once())->method('persist');
    $entityManagerMock->expects($this->once())->method('flush');

    $managerRegistryMock = $this->createMock(ManagerRegistry::class);
    $managerRegistryMock->expects($this->once())->method('getManager')->willReturn($entityManagerMock);

    $articleRepositoryMock = $this->createMock(ArticleRepository::class);

    $articleController = new ArticleController($articleRepositoryMock, $managerRegistryMock);

    $response = $articleController->createArticle($request);

    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
   
    }  
    
    public function testUpdateArticle(): void {

    $entityManagerMock = $this->createMock(EntityManagerInterface::class);
    $entityManagerMock->expects($this->once())->method('persist');
    $entityManagerMock->expects($this->once())->method('flush');

    $managerRegistryMock = $this->createMock(ManagerRegistry::class);
    $managerRegistryMock->expects($this->once())->method('getManager')->willReturn($entityManagerMock);

    $data = [
        'title' => 'Update article',
        'content' => 'Text for apdating article'
    ];

    $jsonContent = json_encode($data);

    $article = new Article();
    $articleRepositoryMock = $this->createMock(ArticleRepository::class);
    $articleRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($article);

    $request = new Request([], [], [], [], [], [], $jsonContent);
    $articleController = new ArticleController($articleRepositoryMock, $managerRegistryMock);

    $response = $articleController->updateArticle(1, $request);

    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

    }
    public function testDeleteArticle() {

    $entityManagerMock = $this->createMock(EntityManagerInterface::class);
    $entityManagerMock->expects($this->once())->method('remove');
    $entityManagerMock->expects($this->once())->method('flush');

    $managerRegistryMock = $this->createMock(ManagerRegistry::class);
    $managerRegistryMock->expects($this->once())->method('getManager')->willReturn($entityManagerMock);

    $article = new Article();
    $articleRepositoryMock = $this->createMock(ArticleRepository::class);
    $articleRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($article);

    $articleController = new ArticleController($articleRepositoryMock, $managerRegistryMock);

    $response = $articleController->deleteArticle(1);

    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

    }

}


        
        


   

   
    