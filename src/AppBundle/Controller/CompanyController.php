<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Company;
use AppBundle\manager\CompanyManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class CompanyController extends Controller
{
    /**
     * @var CompanyManager
     */
    protected $companyManager;

    /**
     * CompanyController constructor.
     * @param CompanyManager $companyManager
     */
    public function __construct(CompanyManager $companyManager)
    {
        $this->companyManager = $companyManager;
    }

    /**
     * @Route("/company/")
     */
    public function listAction()
    {
        /* Avec le manager */
        $companies = $this->companyManager->getList();

        /* Sans le manager, via doctrine */
        /*$repo = $this->getDoctrine()->getRepository(Company::class);
        $companies = $repo->findAll();*/

        return $this->render('AppBundle:Company:list.html.twig', [
            'companies' => $companies,
        ]);
    }

    /**
     * @Route("/company/{id}")
     */
    public function showAction($id)
    {
        /* Avec le manager */
        $company = $this->companyManager->find($id);

        /* Sans le manager, via doctrine */
        /*$repo = $this->getDoctrine()->getRepository(Company::class);
        $company = $repo->find($id);*/

        return $this->render('AppBundle:Company:show.html.twig', [
            'company' => $company
        ]);
    }

}
