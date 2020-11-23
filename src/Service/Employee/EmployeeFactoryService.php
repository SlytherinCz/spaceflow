<?php

namespace App\Service\Employee;

use App\Entity\Employee;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EmployeeFactoryService
{

    private ValidatorInterface $validator;

    /**
     * EmployeeFactoryService constructor.
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Request $request
     * @return Employee
     * @throws \JsonException
     * @throws InvalidInputException
     */
    public function createFromRequest(Request $request): Employee
    {
        return $this->create(json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @param Employee $employee
     * @param Request $request
     * @return Employee
     * @throws \JsonException
     * @throws InvalidInputException
     */
    public function updateByRequest(Employee $employee, Request $request): Employee
    {
        return $this->update($employee, json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * @param array $input
     * @return Employee
     * @throws InvalidInputException
     */
    public function create(array $input): Employee
    {
        $employee = new Employee();
        $input = $this->parseInput($input);
        $this->validate($input);
        $employee->setName($input[Employee::NAME_FIELD]);
        $employee->setAnniversaryDate(\DateTime::createFromFormat('Y-m-d',$input[Employee::ANNIVERSARY_FIELD]));
        return $employee;
    }

    /**
     * @param Employee $employee
     * @param array $input
     * @return Employee
     * @throws InvalidInputException
     */
    public function update(Employee $employee, array $input): Employee
    {
        $input = $this->parseInput($input);
        $this->validate($input);
        $employee->setName($input[Employee::NAME_FIELD]);
        $employee->setAnniversaryDate(\DateTime::createFromFormat('Y-m-d',$input[Employee::ANNIVERSARY_FIELD]));
        return $employee;
    }

    /**
     * @param $input
     * @throws InvalidInputException
     */
    private function validate($input): void
    {
        $violations = $this->validator->validate($input, $this->createValidationCriteria());
        if(count($violations) > 0) {
            throw new InvalidInputException($violations);
        }
    }

    /**
     * @param array $input
     * @return array
     */
    private function parseInput(array $input): array
    {
        return [
            Employee::NAME_FIELD => $input[Employee::NAME_FIELD] ?? "",
            Employee::ANNIVERSARY_FIELD => $input[Employee::ANNIVERSARY_FIELD] ?? ""
        ];
    }

    /**
     * @return Collection
     */
    private function createValidationCriteria(): Collection
    {
        return new Collection([
            Employee::NAME_FIELD  => [
                new NotBlank()
            ],
            Employee::ANNIVERSARY_FIELD  => [
                new DateTime([
                    'format' => 'Y-m-d',
                    'message' => 'Couldn\'t parse date string. Provide an existing date in Y-m-d format (2020-12-31).'
                ]),
                new NotBlank()
            ]
        ]);
    }
}