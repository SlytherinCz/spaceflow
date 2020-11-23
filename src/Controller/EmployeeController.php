<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Service\Employee\EmployeeFactoryService;
use App\Service\Employee\EmployeeListService;
use App\Service\Employee\InvalidInputException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmployeeController extends AbstractController
{
    private EmployeeListService $listService;

    private EntityManagerInterface $entityManager;

    private EmployeeFactoryService $factory;

    /**
     * EmployeeController constructor.
     * @param EmployeeListService $listService
     * @param EntityManagerInterface $entityManager
     * @param EmployeeFactoryService $factory
     */
    public function __construct(EmployeeListService $listService, EntityManagerInterface $entityManager, EmployeeFactoryService $factory)
    {
        $this->listService = $listService;
        $this->entityManager = $entityManager;
        $this->factory = $factory;
    }

    /**
     * @Route("/employees", name="employees_list", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function list(Request $request): Response
    {
        return new JsonResponse($this->listService->getList($request));
    }

    /**
     * @Route("/employees/{employee}", name="employees_show", methods={"GET"})
     * @param Employee|null $employee
     * @return Response
     */
    public function detail(?Employee $employee): Response
    {
        if (!$employee instanceof Employee) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($employee->toPublicFieldsArray());
    }

    /**
     * @Route("/employees/{employee}", name="employees_delete", methods={"DELETE"})
     * @param Employee|null $employee
     * @return Response
     */
    public function delete(?Employee $employee): Response
    {
        if (!$employee instanceof Employee) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        try {
            $this->entityManager->remove($employee);
            $this->entityManager->flush();
        } catch (ORMException $e) {
            return new Response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/employees/{employee}", name="employees_update", methods={"PUT"})
     * @param Employee|null $employee
     * @param Request $request
     * @return Response
     */
    public function update(?Employee $employee, Request $request): Response
    {
        if (!$employee instanceof Employee) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }
        try {
            $employee = $this->factory->updateByRequest($employee, $request);
            $this->entityManager->persist($employee);
            $this->entityManager->flush();
            return new JsonResponse($employee->toPublicFieldsArray(), Response::HTTP_OK);
        } catch (ORMException $e) {
            return new Response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (InvalidInputException $e) {
            return new JsonResponse($e->getViolationMessages(), Response::HTTP_BAD_REQUEST);
        } catch (\JsonException $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/employees", name="employees_create", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        try {
            $employee = $this->factory->createFromRequest($request);
            $this->entityManager->persist($employee);
            $this->entityManager->flush();
        } catch (ORMException $e) {
            return new Response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (InvalidInputException $e) {
            return new JsonResponse($e->getViolationMessages(), Response::HTTP_BAD_REQUEST);
        } catch (\JsonException $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
        return new JsonResponse($employee->toPublicFieldsArray(), Response::HTTP_OK);
    }
}
