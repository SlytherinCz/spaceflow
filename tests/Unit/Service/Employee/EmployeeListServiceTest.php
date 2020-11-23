<?php

namespace App\Tests\Unit\Service\Employee;

use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use App\Service\Employee\EmployeeListService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;
use PHPUnit\Framework\Constraint\ArrayHasKey;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class EmployeeListServiceTest extends TestCase
{
    /**
     * @var EmployeeListService
     */
    private EmployeeListService $service;
    private \PHPUnit\Framework\MockObject\MockObject $paginator;
    private \PHPUnit\Framework\MockObject\MockObject $repository;
    private \PHPUnit\Framework\MockObject\MockObject $entityManager;
    private \PHPUnit\Framework\MockObject\MockObject $paginationMock;

    protected function setUp(): void
    {
        $this->repository = $this->getMockBuilder(EmployeeRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $this->paginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['paginate'])
            ->getMock();

        $this->entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paginationMock = $this->getMockBuilder(SlidingPagination::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getCurrentPageNumber',
                'getTotalItemCount',
                'getItemNumberPerPage',
                'getItems'
            ])
            ->getMock();

        $this->service = new EmployeeListService(
            $this->repository,
            $this->paginator
        );
    }

    public function testGetList()
    {

        $this->paginationMock->expects($this->once())->method('getCurrentPageNumber')->willReturn(2);
        $this->paginationMock->expects($this->once())->method('getTotalItemCount')->willReturn(29);
        $this->paginationMock->expects($this->any())->method('getItemNumberPerPage')->willReturn(10);
        $this->paginationMock->expects($this->any())->method('getItems')->willReturn(
            $this->prepareArrayOfEmployees()
        );
        $request = new Request([
            'page' => '2',
            'limit' => '10'
        ]);
        $this->repository->expects($this->once())->method('createQueryBuilder')->willReturn(
            new QueryBuilder($this->entityManager)
        );
        $this->paginator->expects($this->once())->method('paginate')->willReturn(
            $this->paginationMock
        );
        $list = $this->service->getList($request);
        $this->assertEquals(2,$list['page']);
        $this->assertCount(3, $list['records']);
    }

    private function prepareArrayOfEmployees(): array
    {
        $result = [];
        for ($i = 0;$i < 3; $i++) {
            $employee = new Employee();
            $employee->setName('Jebediah Springfield');
            $employee->setAnniversaryDate(new \DateTime());
            $result[] = $employee;
        }
        return $result;
    }
}