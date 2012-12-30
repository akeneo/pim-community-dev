<?php

namespace Oro\Bundle\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Customer entity controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @Route("/customer")
 */
class CustomerController extends Controller
{

    /**
     * Get product manager
     * @return SimpleEntityManager
     */
    protected function getCustomerManager()
    {
        return $this->container->get('customer_manager');
    }

    /**
     * @Route("/index")
     * @Template()
     *
     * @return multitype
     */
    public function indexAction()
    {
        $customers = $this->getCustomerManager()->getEntityRepository()->findByWithAttributes();

        return array('customers' => $customers);
    }

    /**
     * @param integer $id
     *
     * @Route("/view/{id}")
     * @Template()
     *
     * @return multitype
     */
    public function viewAction($id)
    {
        $customer = $this->getCustomerManager()->getEntityRepository()->find($id);

        return array('customer' => $customer);
    }

    /**
     * @Route("/insert")
     *
     * @return multitype
     */
    public function insertAction()
    {
        $messages = array();

        // force in english
        $this->getCustomerManager()->setLocaleCode('en');

        // get attributes
        $attCompany = $this->getCustomerManager()->getAttributeRepository()->findOneByCode('company');
        $attDob = $this->getCustomerManager()->getAttributeRepository()->findOneByCode('dob');
        $attGender = $this->getCustomerManager()->getAttributeRepository()->findOneByCode('gender');
        // get first attribute option
        $optGender = $this->getCustomerManager()->getAttributeOptionRepository()->findOneBy(array('attribute' => $attGender));

        for ($ind= 1; $ind < 100; $ind++) {

            // add customer with email, firstname, lastname, dob
            $custEmail = 'email-'.($ind++).'@mail.com';
            $customer = $this->getCustomerManager()->getEntityRepository()->findOneByEmail($custEmail);
            if ($customer) {
                $messages[]= "Customer ".$custEmail." already exists";
            } else {
                $customer = $this->getCustomerManager()->getNewEntityInstance();
                $customer->setEmail($custEmail);
                $customer->setFirstname('Nicolas');
                $customer->setLastname('Dupont');
                // add dob value
                if ($attCompany) {
                    $value = $this->getCustomerManager()->getNewAttributeValueInstance();
                    $value->setAttribute($attDob);
                    $value->setData(new \DateTime('19-08-1984'));
                    $customer->addValue($value);
                }
                $messages[]= "Customer ".$custEmail." has been created";
                $this->getCustomerManager()->getStorageManager()->persist($customer);
            }

            // add customer with email, firstname, lastname, company and gender
            $custEmail = 'email-'.($ind++).'@mail.com';
            $customer = $this->getCustomerManager()->getEntityRepository()->findOneByEmail($custEmail);
            if ($customer) {
                $messages[]= "Customer ".$custEmail." already exists";
            } else {
                $customer = $this->getCustomerManager()->getNewEntityInstance();
                $customer->setEmail($custEmail);
                $customer->setFirstname('Romain');
                $customer->setLastname('Monceau');
                // add company value
                if ($attCompany) {
                    $value = $this->getCustomerManager()->getNewAttributeValueInstance();
                    $value->setAttribute($attCompany);
                    $value->setData('Akeneo');
                    $customer->addValue($value);
                }
                // add gender
                if ($attGender) {
                    $value = $this->getCustomerManager()->getNewAttributeValueInstance();
                    $value->setAttribute($attGender);
                    $value->setData($optGender);  // we set option as data, you can use $value->setOption($optGender) too
                    $customer->addValue($value);
                }
                $messages[]= "Customer ".$custEmail." has been created";
                $this->getCustomerManager()->getStorageManager()->persist($customer);
            }
        }

        $this->getCustomerManager()->getStorageManager()->flush();

        $this->get('session')->setFlash('notice', implode(', ', $messages));

        return $this->redirect($this->generateUrl('oro_customer_customer_index'));
    }

}
