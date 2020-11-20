<?php

namespace App\Service\EntityValidator;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityValidatorService
{
    private ValidatorInterface $validator;

    /**
     * EntityValidatorService constructor.
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }


    /**
     * @param $entity
     * @throws InvalidEntityException
     */
    public function validate($entity): void
    {
        $violations = $this->validator->validate($entity);
        if(count($violations) > 0) {
            throw new InvalidEntityException($violations);
        }
    }
}