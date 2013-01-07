<?php
namespace Oro\Bundle\CustomerBundle\Test\Service;

use Oro\Bundle\CustomerBundle\Entity\CustomerAttributeValue;

use Oro\Bundle\FlexibleEntityBundle\Model\Attribute\Type\AbstractAttributeType;

use Oro\Bundle\CustomerBundle\Entity\Customer;

use Oro\Bundle\FlexibleEntityBundle\Tests\KernelAwareTest;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class CustomerManagerTest extends KernelAwareTest
{

    /**
     * @var FlexibleEntityManager
     */
    protected $manager;

    /**
     * @staticvar integer
     */
    protected static $customerCount = 0;

    /**
     * List ofnom  customers
     * @var multitype
     */
    protected $customerList = array();

    /**
     * @var EntityAttributeValue
     */
    protected $attCompany;

    /**
     * @var EntityAttributeValue
     */
    protected $attDob;

    /**
     * @var EntityAttributeValue
     */
    protected $attGender;

    /**
     * UT set up
     */
    public function setUp()
    {
        parent::setUp();
        $this->manager = $this->container->get('customer_manager');

        // create attributes
        $this->attCompany = $this->createAttribute(
            'company',
            'Company',
            AbstractAttributeType::BACKEND_MODEL_ATTRIBUTE_VALUE,
            AbstractAttributeType::BACKEND_TYPE_VARCHAR
        );
        $this->attDob = $this->createAttribute(
            'dob',
            'Date of Birth',
            AbstractAttributeType::BACKEND_MODEL_ATTRIBUTE_VALUE,
            AbstractAttributeType::BACKEND_TYPE_DATE
        );
//         $this->attGender = $this->createAttribute(
//             'gender',
//             'Gender',
//             AbstractAttributeType::BACKEND_MODEL_ATTRIBUTE_VALUE,
//             AbstractAttributeType::BACKEND_TYPE_OPTION
//         );

        // create entities
        for ($idx=0; $idx<5; $idx++) {
            $this->customerList[$idx] = $this->createCustomer('Nicolas', 'Dupont', 'Akeneo', '2012-12-25', 'Mr');
        }
        $this->customerList[$idx++] = $this->createCustomer('Romain', 'Monceau');
        $this->customerList[$idx++] = $this->createCustomer('Romain', 'Dupont', 'Akeneo');


        // commit add transaction
        $this->manager->getStorageManager()->flush();
    }

    /**
     * UT tear down
     */
    public function tearDown()
    {
        parent::tearDown();

        // remove entities
        foreach ($this->customerList as $customer) {
            $this->manager->getStorageManager()->remove($customer);
        }
        $this->customerList = array();

        // remove attributes
        $this->manager->getStorageManager()->remove($this->attCompany);
        $this->manager->getStorageManager()->remove($this->attDob);
//         $this->manager->getStorageManager()->remove($this->attGender);

        // commit remove transaction
        $this->manager->getStorageManager()->flush();
    }

    /**
     * Create customer
     * @param string $firstname Firstname of the customer
     * @param string $lastname  Lastname of the customer
     * @param string $company   Company of the customer
     * @param string $dob       Date of Birth of the customer
     * @param string $gender    Gender of the customer
     *
     * @return Customer
     */
    protected function createCustomer($firstname = "", $lastname = "", $company = "", $dob = "", $gender = "")
    {
        // create values
        $valueCompany = $this->createValue($this->attCompany, $company);
        $valueDob     = $this->createValue($this->attDob, new \DateTime($dob));
//         $valueGender  = $this->createValue($this->attGender, $gender);

        // create customer
        $customer = $this->manager->createEntity();
        $customer->setFirstname($firstname);
        $customer->setLastname($lastname);
        $customer->setEmail('email-'.$firstname.'.'.$lastname.self::$customerCount++.'@mail.com');

        // add values
        $customer->addValue($valueCompany);
        $customer->addValue($valueDob);
//         $customer->addValue($valueGender);

        // persists customer
        $this->manager->getStorageManager()->persist($customer);

        return $customer;
    }

    /**
     * Create value
     * @param OrmEntityAttribute $attribute Attribute object
     * @param mixed              $value     Value of the attribute
     *
     * @return CustomerAttributeValue
     */
    protected function createValue($attribute, $value)
    {
        // create value
        $entityValue = $this->manager->createEntityValue();
        $entityValue->setAttribute($attribute);
        $entityValue->setData($value);

        return $entityValue;
    }

    /**
     * Create attribute
     *
     * @param string $code         Attribute code
     * @param string $title        Attribute title
     * @param string $backendModel Attribute backend model
     * @param string $backendType  Attribute backend type
     *
     * @return OrmEntityAttribute
     */
    protected function createAttribute($code, $title, $backendModel, $backendType)
    {
        // create attribute
        $attribute = $this->manager->createAttribute();
        $attribute->setCode($code);
        $attribute->setTitle($title);
        $attribute->setBackendModel($backendModel);
        $attribute->setBackendType($backendType);

        // persists attribute
        $this->manager->getStorageManager()->persist($attribute);

        return $attribute;
    }

    /**
     * Test related method
     */
    public function testCreateEntity()
    {
        $newCustomer = $this->manager->createEntity();
        $this->assertTrue($newCustomer instanceof Customer);
        $newCustomer->setFirstname('Nicolas');
        $newCustomer->setLastname('Dupont');
        $this->assertEquals($newCustomer->getFirstname(), 'Nicolas');
    }

    /**
     * Test find by with attributes method
     */
    public function testQueryFindByWithAttributes()
    {
        // test find by with attributes
        $customers = $this->getRepo()->findByWithAttributes();
        $this->assertCount(7, $customers);

        // test with lazy loading
        $customers = $this->getRepo()->findBy(array());
        $this->assertCount(7, $customers);

        // test filtering by firstname
        $customers = $this->getRepo()->findByWithAttributes(array(), array('firstname' => 'Nicolas'));
        $this->assertCount(5, $customers);
        $customers = $this->getRepo()->findBy(array('firstname' => 'Nicolas'));
        $this->assertCount(5, $customers);

        // test filtering by firstname and company
        $customers = $this->getRepo()->findByWithAttributes(array('company'), array('firstname' => 'Romain', 'company' => 'Akeneo'));
        $this->assertCount(1, $customers);

        // test filtering and limiting
        $customers = $this->getRepo()->findByWithAttributes(array('company'), array('lastname' => 'Dupont', 'company' => 'Akeneo'), null, 5, 0);
        $this->assertCount(5, $customers);

        // test filtering, limiting and ordering
        $customers = $this->getRepo()->findByWithAttributes(array(), array('firstname' => 'Romain'), array('lastname' => 'ASC'), 5, 0);
        $this->assertCount(2, $customers);
    }

    /**
     * @return Oro\Bundle\FlexibleEntityBundle\Entity\Repository\OrmFlexibleEntityRepository
     */
    protected function getRepo()
    {
        return $this->manager->getEntityRepository();
    }
}
