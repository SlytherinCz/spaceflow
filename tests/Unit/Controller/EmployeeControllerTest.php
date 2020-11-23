<?php

namespace App\Tests\Unit\Controller;

use App\Controller\EmployeeController;
use App\Entity\Employee;
use App\Service\Employee\EmployeeFactoryService;
use App\Service\Employee\EmployeeListService;
use App\Service\Employee\InvalidInputException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationList;

class EmployeeControllerTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $listService;
    private \PHPUnit\Framework\MockObject\MockObject $entityManager;
    private \PHPUnit\Framework\MockObject\MockObject $factory;
    /**
     * @var EmployeeController
     */
    private EmployeeController $controller;
    /**
     * @var Employee
     */
    private Employee $employee;

    protected function setUp(): void {
        $this->listService = $this->getMockBuilder(EmployeeListService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getList'])
            ->getMock();

        $this->entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['persist','remove','flush'])
            ->getMock();

        $this->factory = $this->getMockBuilder(EmployeeFactoryService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['updateByRequest', 'createFromRequest'])
            ->getMock();

        $this->controller = new EmployeeController(
            $this->listService,
            $this->entityManager,
            $this->factory
        );

        $this->employee = new Employee();
        $this->employee->setName('Johnathan');
        $this->employee->setAnniversaryDate(new \DateTime());
    }

    public function testShowExistingId(){
        $response = $this->controller->detail($this->employee);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testShowNonExistentId(){
        $response = $this->controller->detail(null);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testDeleteExistingId() {
        $this->entityManager->expects($this->once())->method('remove')->with($this->employee);
        $this->entityManager->expects($this->once())->method('flush');
        $response = $this->controller->delete($this->employee);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testDeleteNonexistentId() {
        $response = $this->controller->delete(null);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testDeleteORMException() {
        $this->entityManager->expects($this->once())->method('remove')->with($this->employee);
        $this->entityManager->expects($this->once())->method('flush')->willThrowException(new ORMException());
        $response = $this->controller->delete($this->employee);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function testUpdateNonexistentId() {
        $response = $this->controller->update(null, new Request());
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testUpdateExistingId(){
        $request = new Request();
        $modifiedEmployee = new Employee();
        $modifiedEmployee->setAnniversaryDate(new \DateTime());
        $this->factory->expects($this->once())
            ->method('updateByRequest')
            ->with($this->employee, $request)
            ->willReturn($modifiedEmployee);
        $this->entityManager->expects($this->once())->method('persist')->with($modifiedEmployee);
        $this->entityManager->expects($this->once())->method('flush');
        $response = $this->controller->update($this->employee, $request);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testUpdateExistingIdORMException(){
        $request = new Request();
        $modifiedEmployee = new Employee();
        $modifiedEmployee->setAnniversaryDate(new \DateTime());
        $this->factory->expects($this->once())
            ->method('updateByRequest')
            ->with($this->employee, $request)
            ->willReturn($modifiedEmployee);
        $this->entityManager->expects($this->once())->method('persist')->with($modifiedEmployee);
        $this->entityManager->expects($this->once())->method('flush')->willThrowException(new ORMException());
        $response = $this->controller->update($this->employee, $request);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function testUpdateExistingIdInvalidInput(){
        $request = new Request();
        $this->factory->expects($this->once())
            ->method('updateByRequest')
            ->with($this->employee, $request)
            ->willThrowException(new InvalidInputException(new ConstraintViolationList()));
        $response = $this->controller->update($this->employee, $request);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testUpdateExistingIdInvalidJSON(){
        $request = new Request();
        $this->factory->expects($this->once())
            ->method('updateByRequest')
            ->with($this->employee, $request)
            ->willThrowException(new \JsonException());
        $response = $this->controller->update($this->employee, $request);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testCreate(){
        $request = new Request();
        $this->factory->expects($this->once())
            ->method('createFromRequest')
            ->with($request)
            ->willReturn($this->employee);
        $response = $this->controller->create($request);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testCreateInvalidInput(){
        $request = new Request();
        $this->factory->expects($this->once())
            ->method('createFromRequest')
            ->with($request)
            ->willThrowException(new InvalidInputException(new ConstraintViolationList()));
        $response = $this->controller->create($request);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testCreateInvalidJSON(){
        $request = new Request();
        $this->factory->expects($this->once())
            ->method('createFromRequest')
            ->with($request)
            ->willThrowException(new \JsonException());
        $response = $this->controller->create($request);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testCreateORMException(){
        $request = new Request();
        $this->factory->expects($this->once())
            ->method('createFromRequest')
            ->with($request)
            ->willReturn($this->employee);
        $this->entityManager->expects($this->once())->method('persist')->with($this->employee);
        $this->entityManager->expects($this->once())->method('flush')->willThrowException(new ORMException());
        $response = $this->controller->create($request);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function testList() {
        $request = new Request();
        $this->listService->expects($this->once())->method('getList')->with($request);
        $response = $this->controller->list($request);
    }
}