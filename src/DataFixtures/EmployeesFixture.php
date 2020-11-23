<?php

namespace App\DataFixtures;

use App\Entity\Employee;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EmployeesFixture extends Fixture
{

    private \Faker\Generator $faker;

    public function load(ObjectManager $manager)
    {
        $lastNewYears = new \DateTimeImmutable(date("Y") -10 . "-01-01");
        $this->faker = Factory::create();
        for ($i = 0; $i <= 2000; $i++) {
            $employeesAnniversary = $lastNewYears->add(date_interval_create_from_date_string($i . " days"));
            $employee = new Employee();
            $employee->setAnniversaryDate($employeesAnniversary);
            $employee->setName($this->faker->name);
            $manager->persist($employee);
        }
        $manager->flush();
    }
}
