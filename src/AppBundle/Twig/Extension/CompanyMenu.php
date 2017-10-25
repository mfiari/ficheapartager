<?php
/**
 * Created by PhpStorm.
 * User: Administrateur
 * Date: 06/10/2017
 * Time: 13:09
 */

namespace AppBundle\Twig\Extension;


use AppBundle\manager\CompanyManager;

class CompanyMenu extends \Twig_Extension
{
    /**
     * @var CompanyManager
     */
    protected $manager;

    /**
     * CompanyMenu constructor.
     * @param CompanyManager $companyManager
     */
    public function __construct(CompanyManager $companyManager)
    {
        $this->manager = $companyManager;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('getCompanyMenu', [$this, 'getCompanyMenu'], ['is_safe' => ['html' => true]])
        ];
    }

    public function getCompanyMenu () {

        $html = '';

        foreach ($this->manager->getList() as $company) {
            $html .= '<a class="dropdown-item" href="#">'.$company->getName().'</a>';
        }
        return $html;
    }
}
