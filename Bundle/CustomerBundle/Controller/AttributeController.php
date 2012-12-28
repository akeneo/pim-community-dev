<?php
namespace Oro\Bundle\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\DataModelBundle\Model\Attribute\AttributeTypeString;
use Oro\Bundle\DataModelBundle\Model\Attribute\AttributeTypeList;
use Oro\Bundle\DataModelBundle\Model\Attribute\AttributeTypeDate;

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
            ->findBy(array('entityType' => $this->getCustomerManager()->getEntityShortname()));

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

        // prepare attribute types
        $attTypeString = new AttributeTypeString();
        $attTypeList = new AttributeTypeList();
        $attTypeDate = new AttributeTypeDate();

        // attribute company (if not exists)
        $attCode = 'company';
        $att = $this->getCustomerManager()->getAttributeRepository()->findOneByCode($attCode);
        if ($att) {
            $messages[]= "Attribute ".$attCode." already exists";
        } else {
            $att = $this->getCustomerManager()->getNewAttributeInstance();
            $att->setCode($attCode);
            $att->setTitle('Company');
            $att->setAttributeType($attTypeString);
            $att->setTranslatable(false); // false by default
            $this->getCustomerManager()->getStorageManager()->persist($att);
            $messages[]= "Attribute ".$attCode." has been created";
        }

        // attribute date of birth (if not exists)
        $attCode = 'dob';
        $att = $this->getCustomerManager()->getAttributeRepository()->findOneByCode($attCode);
        if ($att) {
            $messages[]= "Attribute ".$attCode." already exists";
        } else {
            $att = $this->getCustomerManager()->getNewAttributeInstance();
            $att->setCode($attCode);
            $att->setTitle('Date of birth');
            $att->setAttributeType($attTypeDate);
            $this->getCustomerManager()->getStorageManager()->persist($att);
            $messages[]= "Attribute ".$attCode." has been created";
        }

        // attribute gender (if not exists)
        $attCode = 'gender';
        $att = $this->getCustomerManager()->getAttributeRepository()->findOneByCode($attCode);
        if ($att) {
            $messages[]= "Attribute ".$attCode." already exists";
        } else {
            $att = $this->getCustomerManager()->getNewAttributeInstance();
            $att->setCode($attCode);
            $att->setTitle('Gender');
            $att->setAttributeType($attTypeList);
            // add option and related values
            $opt = $this->getCustomerManager()->getNewAttributeOptionInstance();
            // En
            $valMr = $this->getCustomerManager()->getNewAttributeOptionValueInstance();
            $valMr->setValue('Masculine');
            $opt->addOptionValue($valMr);
            // Fr
            $valMr = $this->getCustomerManager()->getNewAttributeOptionValueInstance();
            $valMr->setLocaleCode('fr');
            $valMr->setValue('Masculin');
            $opt->addOptionValue($valMr);
            $att->addOption($opt);
            // add another option
            $opt = $this->getCustomerManager()->getNewAttributeOptionInstance();
            // En
            $valMrs = $this->getCustomerManager()->getNewAttributeOptionValueInstance();
            $valMrs->setValue('Feminine');
            $opt->addOptionValue($valMrs);
            // Fr
            $valMrs = $this->getCustomerManager()->getNewAttributeOptionValueInstance();
            $valMrs->setLocaleCode('fr');
            $valMrs->setValue('Féminin');
            $opt->addOptionValue($valMrs);
            $att->addOption($opt);
            $this->getCustomerManager()->getStorageManager()->persist($att);
            $messages[]= "Attribute ".$attCode." has been created";
        }

        $this->getCustomerManager()->getStorageManager()->flush();

        $this->get('session')->setFlash('notice', implode(', ', $messages));

        return $this->redirect($this->generateUrl('oro_customer_attribute_index'));
    }

}
