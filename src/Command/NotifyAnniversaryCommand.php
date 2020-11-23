<?php

namespace App\Command;

use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class NotifyAnniversaryCommand extends Command
{
    protected static $defaultName = 'app:notify:anniversary';

    private EmployeeRepository $repository;

    private ClientInterface $httpClient;

    private string $slackWebhookUrl;

    /**
     * NotifyAnniversaryCommand constructor.
     * @param EmployeeRepository $repository
     * @param ClientInterface $httpClient
     * @param string $slackWebhookUrl
     */
    public function __construct(EmployeeRepository $repository, ClientInterface $httpClient, string $slackWebhookUrl)
    {
        $this->repository = $repository;
        $this->httpClient = $httpClient;
        $this->slackWebhookUrl = $slackWebhookUrl;
        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setDescription('Send a notification about Employees having an anniversary')
            ->addArgument('date', InputArgument::REQUIRED, 'A date to notify about')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $date = \DateTime::createFromFormat('Y-m-d',$input->getArgument('date'));
        if(!$date instanceof \DateTime) {
            $io->error('Not a valid date string. Get valid format using $(date +"%Y-%m-%d") in shell');
            return Command::FAILURE;
        }
        $io->text('Searching Employee database');
        $employees = $this->repository->findByMonthAndDay($date->format('m'), $date->format('d'));
        /** @var Employee $employee */
        foreach($employees as $employee) {
            $io->text("Notifying " . $employee->getName());
            try {
                $this->dispatchMessage($this->prepareMessage($employee, $date));
            } catch (RequestException $e) {
                $io->error($e->getMessage());
                return Command::FAILURE;
            }

        }
        return Command::SUCCESS;
    }

    private function prepareMessage(Employee $employee, \DateTime $date): array
    {
        $years = $employee->getAnniversaryDate()->diff($date)->y;
        return [
            "text" => "Congratulations " .
                $employee->getName() .
                " on your anniversary. We have the honor of working with you for " .
                $years .
                ($years > 1 ? " years " : " year ") .
                "and counting."
        ];
    }

    private function dispatchMessage(array $message): void
    {
        $this->httpClient->request(
            'POST',
            $this->slackWebhookUrl,
            [
                'json' => $message
            ]
        );
    }
}
