<?php
namespace Oro\Bundle\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\DataModelBundle\Model\AbstractEntityAttribute;

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

        // attribute company (if not exists)
        $attCode = 'company';
        $att = $this->getCustomerManager()->getAttributeRepository()->findOneByCode($attCode);
        if ($att) {
            $messages[]= "Attribute ".$attCode." already exists";
        } else {
            $att = $this->getCustomerManager()->getNewAttributeInstance();
            $att->setCode($attCode);
            $att->setTitle('Company');
            $att->setType(AbstractEntityAttribute::TYPE_STRING);
            $att->setTranslatable(false);
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
            $att->setTitle('Company');
            $att->setType(AbstractEntityAttribute::TYPE_STRING);
            $att->setTranslatable(false);
            $this->getCustomerManager()->getStorageManager()->persist($att);
            $messages[]= "Attribute ".$attCode." has been created";
        }

        $this->getCustomerManager()->getStorageManager()->flush();

        $this->get('session')->setFlash('notice', implode(', ', $messages));

        return $this->redirect($this->generateUrl('oro_customer_attribute_index'));
    }

}
