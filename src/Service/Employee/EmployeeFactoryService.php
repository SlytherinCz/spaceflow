<?php


namespace App\Service\Employee;


use App\Entity\Employee;
use App\Service\EntityValidator\EntityValidatorService;
use App\Service\EntityValidator\InvalidEntityException;
use Symfony\Component\HttpFoundation\Request;

class EmployeeFactoryService
{
    private EntityValidatorService $validator;

    /**
     * EmployeeFactoryService constructor.
     * @param EntityValidatorService $validator
     */
    public function __construct(EntityValidatorService $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Request $request
     * @return Employee
     * @throws InvalidEntityException
     * @throws \JsonException
     */
    public function createFromRequest(Request $request): Employee
    {
        return $this->create(json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @param array $input
     * @return Employee
     * @throws InvalidEntityException
     */
    public function create(array $input): Employee
    {
        $employee = new Employee();
        $employee->setName($input['name'] ?? "");

        $employee->setAnniversaryDate($input['anniversaryDate'] ?? null);
        $this->validator->validate($employee);
        return $employee;
    }
}