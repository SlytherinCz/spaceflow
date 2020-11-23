<?php


namespace App\Service\Employee;


use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class InvalidInputException extends \Exception
{
    private array $messages = [];

    public function __construct(ConstraintViolationListInterface $violations)
    {
        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation){
            $this->messages[] = [
                'field' => $violation->getPropertyPath(),
                'value' => $violation->getInvalidValue(),
                'message' => $violation->getMessage()
            ];
        }
        parent::__construct(json_encode($this->messages));
    }

    public function getViolationMessages(): array
    {
        return $this->messages;
    }
}