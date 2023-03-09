<?php

use PHPUnit\Framework\TestCase;
use App\Entity\Category;
use App\Entity\Article;
use App\Controller\CategoryController;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

class CategoryControllerTest extends TestCase {
    public function testingWhenCategoryWithProvidedIdIsNotPresentInDatabase(): void {

    $categoryRepositoryMock = $this->createMock(CategoryRepository::class);
    $managerRegistryMock = $this->createMock(ManagerRegistry::class);

    $categoryController = new CategoryController($categoryRepositoryMock,$managerRegistryMock);

    $categoryRepositoryMock->expects($this->once())
                            ->method('findOneBy')
                            ->with(['id' => 1])
                            ->willReturn(null);

    $response = $categoryController->findCategoryById(1);

    $this->assertInstanceOf(JsonResponse::class, $response);

    $this->assertEquals('"category with id: 1 does not exists"', $response->getContent());

    }

    public function testingWhenCategoryeWithProvidedIdIsPresentInDatabase(): void {

    $category = new Category();
    $category->setName('Movies');

    $article = new Article();
    $article->setTitle('Spidermen');
    $article->setContent('Spidermen super hero');
    $category->setArticle($article);

    $categoryRepositoryMock = $this->createMock(CategoryRepository::class);
    $categoryRepositoryMock->expects($this->once())
        ->method('findOneBy')
        ->with(['id' => 1])
        ->willReturn($category);

    $managerRegistryMock = $this->createMock(ManagerRegistry::class);
    $categoryController = new CategoryController($categoryRepositoryMock, $managerRegistryMock);

    $request = new Request([], [], [], [], [], [], []);
    $response = $categoryController->findCategoryById(1, $request);

    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    $this->assertEquals(json_encode([
        'name' => 'Movies',
        'title' => 'Spidermen',
        'content' => 'Spidermen super hero',
    ]), $response->getContent());

    }

    public function testFindAllCategories(): void {

    $firstCategory = new Category();
    $firstCategory->setName('Movies');

    $firstArticle = new Article();
    $firstArticle->setTitle('Spidermen');
    $firstArticle->setContent('Spidermen super hero');
    $firstCategory->setArticle($firstArticle);

    $secondCategory = new Category();
    $secondCategory->setName('Books');

    $secondArticle = new Article();
    $secondArticle->setTitle('Harry Potter');
    $secondArticle->setContent('Harry Potter and order of Phoenix');
    $secondCategory->setArticle($secondArticle);

    $categoryRepositoryMock = $this->createMock(CategoryRepository::class);
    $categoryRepositoryMock->expects($this->once())
        ->method('findAll')
        ->willReturn([$firstCategory, $secondCategory]);

    $managerRegistryMock = $this->createMock(ManagerRegistry::class);
    $categoryController = new CategoryController($categoryRepositoryMock, $managerRegistryMock);

    $request = new Request([], [], [], [], [], [], []);
    $response = $categoryController->findAllCategories($request);

    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

    $this->assertEquals(json_encode([
        [
            'name' => 'Movies',
            'title' => 'Spidermen',
            'content' => 'Spidermen super hero',
        ],
        [
            'name' => 'Books',
            'title' => 'Harry Potter',
            'content' => 'Harry Potter and order of Phoenix',
        ],
    ]), $response->getContent());

    }

    public function testCreateCategory(): void {

    $entityManagerMock = $this->createMock(EntityManagerInterface::class);
    $entityManagerMock->expects($this->exactly(2))->method('persist')
        ->withConsecutive(
            [$this->isInstanceOf(Category::class)],
            [$this->isInstanceOf(Article::class)]
        );

    $entityManagerMock->expects($this->once())->method('flush');
    
    $managerRegistryMock = $this->createMock(ManagerRegistry::class);
    $managerRegistryMock->expects($this->once())->method('getManager')->willReturn($entityManagerMock);
    
    $categoryRepositoryMock = $this->createMock(CategoryRepository::class);
    
    $categoryController = new CategoryController($categoryRepositoryMock, $managerRegistryMock);
    
    $data = [
        'name' => 'Sport',
        'title' => 'Football games',
        'content' => '23 football games',
    ];

    $jsonData = json_encode($data);
    
    $request = new Request([], [], [], [], [], [], $jsonData);
    
    $response = $categoryController->createCategory($request);
    
    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

    }
    
    public function testDeleteCategory(): void {

    $category = new Category();
    $category->setName('Movies');

    $entityManagerMock = $this->createMock(EntityManagerInterface::class);
    $entityManagerMock->expects($this->once())
        ->method('remove')
        ->with($category);
    $entityManagerMock->expects($this->once())
        ->method('flush');

    $categoryRepositoryMock = $this->createMock(CategoryRepository::class);
    $categoryRepositoryMock->expects($this->once())
        ->method('findOneBy')
        ->with(['id' => 1])
        ->willReturn($category);

    $managerRegistryMock = $this->createMock(ManagerRegistry::class);
    $managerRegistryMock->expects($this->once())
        ->method('getManager')
        ->willReturn($entityManagerMock);

    $categoryController = new CategoryController($categoryRepositoryMock, $managerRegistryMock);

    $request = new Request([], [], [], [], [], [], []);
    $response = $categoryController->deleteCategory(1, $request);

    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

    }
    public function testUpdateCategory(): void {

    $categoryData = [
        'name' => 'Hello World',
    ];

    $jsonCategoryData = json_encode($categoryData);

    $category = new Category();
    $category->setName($categoryData['name']);

    $request = new Request([], [], [], [], [], [], $jsonCategoryData);

    $entityManagerMock = $this->createMock(EntityManagerInterface::class);
    $entityManagerMock->expects($this->once())->method('persist');
    $entityManagerMock->expects($this->once())->method('flush');

    $categoryRepositoryMock = $this->createMock(CategoryRepository::class);
    $categoryRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($category);

    $managerRegistryMock = $this->createMock(ManagerRegistry::class);
    $managerRegistryMock->expects($this->once())->method('getManager')->willReturn($entityManagerMock);

    $categoryController = new CategoryController($categoryRepositoryMock, $managerRegistryMock);

    $response = $categoryController->updateCategory(1, $request);

    $this->assertInstanceOf(JsonResponse::class, $response);
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

    }

}


        
        


   

   
    