<?php

namespace App\Tests\Unit\Service\Employee;

use App\Entity\Employee;
use App\Service\Employee\EmployeeFactoryService;
use App\Service\Employee\InvalidInputException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\RecursiveValidator;

class EmployeeFactoryServiceTest extends TestCase
{
    /**
     * @var EmployeeFactoryService
     */
    private EmployeeFactoryService $factory;
    private \PHPUnit\Framework\MockObject\MockObject $validator;
    private $brokenJsonRequest;
    /**
     * @var Request
     */
    private Request $validJsonRequest;

    protected function setUp(): void {
        $this->validator = $this->getMockBuilder(RecursiveValidator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate'])
            ->getMock();

        $this->factory = new EmployeeFactoryService(
            $this->validator
        );

        $this->validJsonRequest = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode([Employee::NAME_FIELD=>"Johnathan Doucheton the Third", Employee::ANNIVERSARY_FIELD => "2015-02-09"])
        );
    }

    private function getRequest(array $body): Request
    {
        $this->validJsonRequest = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode($body)
        );
    }

    public function testCreateFromRequest()
    {
        $this->validator->expects($this->once())->method('validate')->willReturn(new ConstraintViolationList([]));
        $employee = $this->factory->createFromRequest($this->validJsonRequest);
        $this->assertEquals("Johnathan Doucheton the Third", $employee->getName());
        $this->assertEquals(\DateTime::createFromFormat('Y-m-d', '2015-02-09'),$employee->getAnniversaryDate());
    }

    public function testUpdateByRequest()
    {
        $this->validator->expects($this->once())->method('validate')->willReturn(new ConstraintViolationList([]));
        $employee = $this->factory->updateByRequest(new Employee(), $this->validJsonRequest);
        $this->assertEquals("Johnathan Doucheton the Third", $employee->getName());
        $this->assertEquals(\DateTime::createFromFormat('Y-m-d', '2015-02-09'),$employee->getAnniversaryDate());
    }

    public function testValidateNonEmptyResult()
    {
        $this->expectException(InvalidInputException::class);
        $this->validator->expects($this->once())->method('validate')->willReturn(new ConstraintViolationList([
            new ConstraintViolation(
                'Computer says no.',
                null,
                [],
                '',
                null,
                null
            )
        ]));
        $this->factory->create([]);
    }
}