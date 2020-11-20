<?php

namespace App\Service\EntityValidator;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class InvalidEntityException extends \Exception
{
    private ConstraintViolationListInterface $violations;

    public function __construct(ConstraintViolationListInterface $violations)
    {
        $this->violations = $violations;
        parent::__construct((string)$this->violations, 0, null);
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->violations as $violation) {
            $result[] = [
                'property' => $violation->getPropertyPath(),
                'value' => $violation->getInvalidValue(),
                'message' => $violation->getMessage()
            ];
        }
        return $result;
    }
}