<?php
/**
 * Created by PhpStorm.
 * User: Administrateur
 * Date: 06/10/2017
 * Time: 10:58
 */

namespace AppBundle\manager;


use AppBundle\Entity\Company;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class CompanyManager
{
    /**
     * @var EntityManagerInterface
     */
    protected  $em;

    /**
     * CompanyManager constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param EntityManagerInterface $em
     */
    public function setEm($em)
    {
        $this->em = $em;
    }

    protected function getRepository () {
        return $this->em->getRepository(Company::class);
    }

    public function getList () {
        return $this->getRepository()->findAll();
    }

    public function find ($id) {
        return $this->getRepository()->find($id);
    }
}
