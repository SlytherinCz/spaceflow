<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=EmployeeRepository::class)
 */
class Employee
{
    public const NAME_FIELD = 'name';

    public const ANNIVERSARY_FIELD = 'anniversaryDate';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     * @ORM\Column(type="uuid")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank
     */
    private $anniversaryDate;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAnniversaryDate(): ?\DateTimeInterface
    {
        return $this->anniversaryDate;
    }

    public function setAnniversaryDate(?\DateTimeInterface $anniversaryDate): self
    {
        $this->anniversaryDate = $anniversaryDate;

        return $this;
    }

    public function toPublicFieldsArray(): array
    {
        return [
            "id" => $this->getId(),
            "name" => $this->getName(),
            "anniversaryDate" => $this->getAnniversaryDate()->format("Y-m-d")
        ];
    }
}
