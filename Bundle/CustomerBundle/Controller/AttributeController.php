<?php
namespace Oro\Bundle\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\FlexibleEntityBundle\Model\Attribute\Type\AbstractAttributeType;

/**
 * Customer attribute controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @Route("/attribute")
 */
class AttributeController extends Controller
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
        $attributes = $this->getCustomerManager()->getAttributeRepository()
            ->findBy(array('entityType' => $this->getCustomerManager()->getEntityName()));

        return array('attributes' => $attributes);
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

        // attribute company (if not exists)
        $attCode = 'company';
        $att = $this->getCustomerManager()->getAttributeRepository()->findOneByCode($attCode);
        if ($att) {
            $messages[]= "Attribute ".$attCode." already exists";
        } else {
            $att = $this->getCustomerManager()->createAttribute();
            $att->setCode($attCode);
            $att->setTitle('Company');
            $att->setBackendModel(AbstractAttributeType::BACKEND_MODEL_ATTRIBUTE_VALUE);
            $att->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
            $this->getCustomerManager()->getStorageManager()->persist($att);
            $messages[]= "Attribute ".$attCode." has been created";
        }

        // attribute date of birth (if not exists)
        $attCode = 'dob';
        $att = $this->getCustomerManager()->getAttributeRepository()->findOneByCode($attCode);
        if ($att) {
            $messages[]= "Attribute ".$attCode." already exists";
        } else {
            $att = $this->getCustomerManager()->createAttribute();
            $att->setCode($attCode);
            $att->setTitle('Date of birth');
            $att->setBackendModel(AbstractAttributeType::BACKEND_MODEL_ATTRIBUTE_VALUE);
            $att->setBackendType(AbstractAttributeType::BACKEND_TYPE_DATE);
            $this->getCustomerManager()->getStorageManager()->persist($att);
            $messages[]= "Attribute ".$attCode." has been created";
        }

        // attribute gender (if not exists)
        $attCode = 'gender';
        $att = $this->getCustomerManager()->getAttributeRepository()->findOneByCode($attCode);
        if ($att) {
            $messages[]= "Attribute ".$attCode." already exists";
        } else {
            $att = $this->getCustomerManager()->createAttribute();
            $att->setCode($attCode);
            $att->setTitle('Gender');
            $att->setBackendModel(AbstractAttributeType::BACKEND_MODEL_ATTRIBUTE_VALUE);
            $att->setBackendType(AbstractAttributeType::BACKEND_TYPE_OPTION);
            // add option and related value
            $opt = $this->getCustomerManager()->createNewAttributeOption();
            $optVal = $this->getCustomerManager()->createAttributeOptionValue();
            $optVal->setValue('Mr');
            $opt->addOptionValue($optVal);
            $att->addOption($opt);
            // add another option
            $opt = $this->getCustomerManager()->createNewAttributeOption();
            $optVal = $this->getCustomerManager()->createAttributeOptionValue();
            $optVal->setValue('Mrs');
            $opt->addOptionValue($optVal);
            $att->addOption($opt);
            $this->getCustomerManager()->getStorageManager()->persist($att);
            $messages[]= "Attribute ".$attCode." has been created";
        }

        $this->getCustomerManager()->getStorageManager()->flush();

        $this->get('session')->setFlash('notice', implode(', ', $messages));

        return $this->redirect($this->generateUrl('oro_customer_attribute_index'));
    }

}
