<?php


namespace App\Service\Employee;


use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class EmployeeListService
{
    private EmployeeRepository $employeeRepository;
    private PaginatorInterface $paginator;

    /**
     * EmployeeListService constructor.
     * @param EmployeeRepository $employeeRepository
     * @param PaginatorInterface $paginator
     */
    public function __construct(EmployeeRepository $employeeRepository, PaginatorInterface $paginator)
    {
        $this->employeeRepository = $employeeRepository;
        $this->paginator = $paginator;
    }

    public function getList(Request $request): array
    {
        $pagination = $this->getPagination($request);
        return [
            'page' => $pagination->getCurrentPageNumber(),
            'totalPages' => ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage()),
            'limit' => $pagination->getItemNumberPerPage(),
            'records' => array_map(function (Employee $employee) {
                return $employee->toPublicFieldsArray();
            }, (array) $pagination->getItems())
        ];
    }

    public function getPagination(Request $request): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->employeeRepository->createQueryBuilder('employee'),
            $request->query->get('page', 1),
            $request->query->getInt('limit',20)
        );
    }
}