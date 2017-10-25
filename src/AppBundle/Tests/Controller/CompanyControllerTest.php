<?php

namespace AppBundle\Tests\Controller;

use AppBundle\Entity\Company;
use AppBundle\manager\CompanyManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CompanyControllerTest extends WebTestCase
{
    public function testList()
    {
        $client = static::createClient();

        $compagnies = [
            (new Company())->setId(1)->setName('FLB')
        ];

        $managerMock = $this->prophesize(CompanyManager::class);
        $managerMock->getList()->willReturn($compagnies)->shouldBeCalledTimes(2);

        $client->getContainer()->set(CompanyManager::class, $managerMock->reveal());

        $crawler = $client->request('GET', '/company/');
        $this->assertEquals(200 , $client->getResponse()->getStatusCode());
        $this->assertEquals("Company List", $crawler->filter('h2')->text());
        $this->assertCount(1,$crawler->filter('table tbody tr'));
    }

    public function testShow()
    {
        $client = static::createClient();

        $company = (new Company())->setId(1)->setName('FLB');

        $managerMock = $this->prophesize(CompanyManager::class);
        $managerMock->getList()->willReturn([])->shouldBeCalledTimes(1);
        $managerMock->find(1)->willReturn($company)->shouldBeCalledTimes(1);

        $client->getContainer()->set(CompanyManager::class, $managerMock->reveal());

        $crawler = $client->request('GET', '/company/1');

        $this->assertEquals(200 , $client->getResponse()->getStatusCode());
        $this->assertEquals("FLB", $crawler->filter('h2')->text());
    }

}
