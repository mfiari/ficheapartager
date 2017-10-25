<?php
/**
 * Created by PhpStorm.
 * User: Administrateur
 * Date: 06/10/2017
 * Time: 17:28
 */

namespace AppBundle\Controller\Rest;


use AppBundle\manager\CompanyManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

/**
 * Class CompanyRestController
 * @package AppBundle\Controller\Rest
 * @Route ("/api/companies/")
 */
class CompanyRestController extends Controller
{
    /**
     * @var CompanyManager
     */
    protected $companyManager;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * CompanyRestController constructor.
     * @param CompanyManager $companyManager
     * @param Serializer $serializer
     */
    public function __construct(CompanyManager $companyManager, Serializer $serializer)
    {
        $this->companyManager = $companyManager;
        $this->serializer = $serializer;
    }


    /**
     * @Route ("/{_format}", methods={"GET"}, defaults={"_format": "json"})
     */
    public function listAction($_format) {

        $companies = $this->companyManager->getList();
        $json = $this->serializer->serialize($companies, $_format);

        return new Response($json);
    }

    /**
     * @Route ("/{id}/{_format}", methods={"GET"}, defaults={"_format": "json"})
     */
    public function showAction($id, $_format) {
        $company = $this->companyManager->find($id);
        $json = $this->serializer->serialize($company, $_format);

        return new Response($json);
    }
}
