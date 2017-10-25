<?php
/**
 * Created by PhpStorm.
 * User: Administrateur
 * Date: 06/10/2017
 * Time: 13:15
 */

namespace Tests\AppBundle\Twig\Extension;


use AppBundle\Entity\Company;
use AppBundle\manager\CompanyManager;
use AppBundle\Twig\Extension\CompanyMenu;
use PHPUnit\Framework\TestCase;

class CompanyMenuTest extends TestCase
{

    public function testCompanyMenu () {

        $compagnies = [
            (new Company())->setId(1)->setName('FLB')
        ];

        $managerMock = $this->prophesize(CompanyManager::class);
        $managerMock->getList()->willReturn($compagnies)->shouldBeCalledTimes(1);

        $companyMenu = new CompanyMenu($managerMock->reveal());

        $this->assertContains('FLB', $companyMenu->getCompanyMenu());


    }

}
