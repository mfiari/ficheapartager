<?php
/**
 * Created by PhpStorm.
 * User: Administrateur
 * Date: 05/10/2017
 * Time: 15:28
 */

namespace AppBundle\Tests\Entity;


use AppBundle\Entity\Company;
use AppBundle\Entity\Contact;
use PHPUnit\Framework\TestCase;

class ContactTest extends TestCase
{

    /**
     * @var Contact
     */
    protected $contact;

    /* Avant chaque test */
    public function setUp()
    {
        $this->contact = new Contact();
    }

    /* Après chaque test */
    public function tearDown()
    {

    }

    public function testHello () {
        $this->assertEquals(3, 1+2);
    }

    /*public function testHelloFail () {
        $this->assertEquals(1, 1+2);
    }*/

    public function testConstructor () {

        $this->assertNull($this->contact->getCompany());
        $this->assertNull($this->contact->getEmail());
        $this->assertNull($this->contact->getFirstName());
        $this->assertNull($this->contact->getId());
        $this->assertNull($this->contact->getLastName());
        $this->assertNull($this->contact->getTelephone());
    }

    public function testGetSetPrenom () {
        $prenom = 'Mike';
        $this->contact->setFirstName($prenom);
        $this->assertEquals($prenom,$this->contact->getFirstName());
    }

    public function testIntegrationWithCompagny () {
        $compagny = new Company();
        $compagny->setName("Acme");

        $this->contact->setCompany ($compagny);
        $this->assertEquals("Acme", $this->contact->getCompany()->getName());
    }
}
