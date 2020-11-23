<?php

namespace App\Tests\Unit\Command;

use App\Command\NotifyAnniversaryCommand;
use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;

class NotifyAnniversaryCommandTest extends TestCase
{
    private \PHPUnit\Framework\MockObject\MockObject $repository;
    private \PHPUnit\Framework\MockObject\MockObject $httpClient;
    private NotifyAnniversaryCommand $command;
    private \PHPUnit\Framework\MockObject\MockObject $outputMock;
    private string $url;

    protected function setUp(): void
    {
        $this->repository = $this->getMockBuilder(EmployeeRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findByMonthAndDay'])
            ->getMock();

        $this->httpClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['request'])
            ->getMock();

        $this->outputMock = $this->getMockBuilder(ConsoleOutput::class)
            ->disableOriginalConstructor()
            ->getMock();

        $formatter = $this->getMockBuilder(OutputFormatter::class)
            ->onlyMethods(['format'])
            ->getMock();

        $formatter->expects($this->any())
            ->method('format')
            ->will($this->returnArgument(0));

        $this->outputMock->method('getFormatter')->willReturn(
            $formatter
        );

        $this->url = 'URLLikeYouNeverSeenBefore';

        $this->command = new NotifyAnniversaryCommand(
            $this->repository,
            $this->httpClient,
            $this->url
        );
    }

    public function testInvalidDateInput() {
        $input = new StringInput("invalid-date");
        $result = $this->command->run($input,$this->outputMock);
        $this->assertEquals(1, $result);
    }

    public function testOneMatch() {
        $input = new StringInput("2020-01-02");
        $employee = new Employee();
        $employee->setName("Richard D. Winters");
        $employee->setAnniversaryDate(\DateTime::createFromFormat('Y-m-d', "2018-01-02"));
        $this->repository->expects($this->once())
            ->method('findByMonthAndDay')
            ->with(1,2)
            ->willReturn(new ArrayCollection([
                $employee
            ]));
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                $this->url,
                $this->callback(function(array $content){
                    $this->assertStringContainsString('2 years',$content['json']['text']);
                    return true;
                })
            );
        $result = $this->command->run($input, $this->outputMock);
        $this->assertEquals(0, $result);
    }

    public function testOneMatchHttpException() {
        $input = new StringInput("2020-01-02");
        $employee = new Employee();
        $employee->setName("Richard D. Winters");
        $employee->setAnniversaryDate(\DateTime::createFromFormat('Y-m-d', "2018-01-02"));
        $this->repository->expects($this->once())
            ->method('findByMonthAndDay')
            ->with(1,2)
            ->willReturn(new ArrayCollection([
                $employee
            ]));
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                $this->url,
                $this->callback(function(array $content){
                    $this->assertStringContainsString('2 years',$content['json']['text']);
                    return true;
                })
            )->willThrowException(new RequestException('Network says no', new Request('POST','uri')));
        $result = $this->command->run($input, $this->outputMock);
        $this->assertEquals(1, $result);
    }
}