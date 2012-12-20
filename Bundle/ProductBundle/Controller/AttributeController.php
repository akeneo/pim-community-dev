<?php

namespace Oro\Bundle\ProductBundle\Controller;

use Oro\Bundle\ProductBundle\Entity\ProductEntity;
use Oro\Bundle\DataModelBundle\Model\EntityAttribute as AbstractEntityAttribute;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Default controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/attribute")
 */
class AttributeController extends Controller
{

    /**
     * Get product manager
     * @return FlexibleEntityManager
     */
    protected function getProductManager()
    {
        return $this->container->get('product_manager');
    }


    /**
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $attributes = $this->getProductManager()->getAttributeRepository()->findAll();

        return array('attributes' => $attributes);
    }

    /**
     * @Route("/insert")
     */
    public function insertAction()
    {
        $messages = array();

        // attribute name (if not exists)
        $attNameCode = 'name';
        $attName = $this->getProductManager()->getAttributeRepository()->findOneByCode($attNameCode);
        if ($attName) {
            $messages[]= "Attribute ".$attNameCode." already exists";
        } else {
            $attName = $this->getProductManager()->getNewAttributeInstance();
            $attName->setCode($attNameCode);
            $attName->setTitle('Name');
            $attName->setType(AbstractEntityAttribute::TYPE_STRING);
            $attName->setTranslatable(true);
            $this->getProductManager()->getPersistenceManager()->persist($attName);
            $messages[]= "Attribute ".$attNameCode." has been created";
        }

        // attribute description (if not exists)
        $attDescCode = 'description';
        $attDesc = $this->getProductManager()->getAttributeRepository()->findOneByCode($attDescCode);
        if ($attDesc) {
            $messages[]= "Attribute ".$attDescCode." already exists";
        } else {
            $attDesc = $this->getProductManager()->getNewAttributeInstance();
            $attDesc->setCode($attDescCode);
            $attDesc->setTitle('Description');
            $attDesc->setType(AbstractEntityAttribute::TYPE_TEXT);
            $attDesc->setTranslatable(true);
            $this->getProductManager()->getPersistenceManager()->persist($attDesc);
            $messages[]= "Attribute ".$attDescCode." has been created";
        }

        // attribute size (if not exists)
        $attSizeCode= 'size';
        $attSize = $this->getProductManager()->getAttributeRepository()->findOneByCode($attSizeCode);
        if ($attSize)  {
            $messages[]= "Attribute ".$attSizeCode." already exists";
        } else {
            $attSize = $this->getProductManager()->getNewAttributeInstance();
            $attSize->setCode($attSizeCode);
            $attSize->setTitle('Size');
            $attSize->setType(AbstractEntityAttribute::TYPE_NUMBER);
            $this->getProductManager()->getPersistenceManager()->persist($attSize);
            $messages[]= "Attribute ".$attSizeCode." has been created";
        }

        // translate attribute title in many locales in one time (saved when flush on entity manager)
        $repository = $this->getProductManager()->getPersistenceManager()->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $repository
            ->translate($attSize, 'title', 'de_De', 'size DE')
            ->translate($attSize, 'title', 'fr_FR', 'size FR')
            ->translate($attSize, 'title', 'es_ES', 'size ES');
        $messages[]= "Title of attribute ".$attSizeCode." has been translated in fr, de, es";

        // attribute color (if not exists)
        $attColorCode= 'color';
        $attColor = $this->getProductManager()->getAttributeRepository()->findOneByCode($attColorCode);
        if ($attColor)  {
            $messages[]= "Attribute ".$attColorCode." already exists";
        } else {
            $attColor = $this->getProductManager()->getNewAttributeInstance();
            $attColor->setCode($attColorCode);
            $attColor->setTitle('Color');
            $attColor->setType(AbstractEntityAttribute::TYPE_NUMBER);
            $this->getProductManager()->getPersistenceManager()->persist($attColor);
            $messages[]= "Attribute ".$attColorCode." has been created";
        }

        $this->getProductManager()->getPersistenceManager()->flush();

        $this->get('session')->setFlash('notice', implode(', ', $messages));

        return $this->redirect($this->generateUrl('oro_product_attribute_index'));
    }

}
