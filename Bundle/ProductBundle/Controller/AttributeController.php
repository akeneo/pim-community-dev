<?php

namespace Oro\Bundle\ProductBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\ProductBundle\Entity\ProductEntity;
use Oro\Bundle\FlexibleEntityBundle\Model\Attribute\Type\AbstractAttributeType;

/**
 * Default controller
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
     *
     * @return FlexibleEntityManager
     */
    protected function getProductManager()
    {
        return $this->container->get('product_manager');
    }

    /**
     * @Route("/index")
     * @Template()
     *
     * @return multitype
     */
    public function indexAction()
    {
        $attributes = $this->getProductManager()->getAttributeRepository()
            ->findBy(array('entityType' => $this->getProductManager()->getEntityName()));

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
        $this->getProductManager()->setLocaleCode('en');

        // attribute name (if not exists)
        $attributeCode = 'name';
        $attribute = $this->getProductManager()->getAttributeRepository()->findOneByCode($attributeCode);
        if ($attribute) {
            $messages[]= "Attribute ".$attributeCode." already exists";
        } else {
            $attribute = $this->getProductManager()->createAttribute();
            $attribute->setCode($attributeCode);
            $attribute->setTitle('Name');
            $attribute->setRequired(true);
            $attribute->setBackendModel(AbstractAttributeType::BACKEND_MODEL_ATTRIBUTE_VALUE);
            $attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
            $attribute->setTranslatable(true);
            $this->getProductManager()->getStorageManager()->persist($attribute);
            $messages[]= "Attribute ".$attributeCode." has been created";
        }

        // attribute description (if not exists)
        $attributeCode = 'description';
        $attribute = $this->getProductManager()->getAttributeRepository()->findOneByCode($attributeCode);
        if ($attribute) {
            $messages[]= "Attribute ".$attributeCode." already exists";
        } else {
            $attribute = $this->getProductManager()->createAttribute();
            $attribute->setCode($attributeCode);
            $attribute->setTitle('Description');
            $attribute->setBackendModel(AbstractAttributeType::BACKEND_MODEL_ATTRIBUTE_VALUE);
            $attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_TEXT);
            $attribute->setTranslatable(true);
            $this->getProductManager()->getStorageManager()->persist($attribute);
            $messages[]= "Attribute ".$attributeCode." has been created";
        }

        // attribute size (if not exists)
        $attributeCode= 'size';
        $attribute = $this->getProductManager()->getAttributeRepository()->findOneByCode($attributeCode);
        if ($attribute) {
            $messages[]= "Attribute ".$attributeCode." already exists";
        } else {
            $attribute = $this->getProductManager()->createAttribute();
            $attribute->setCode($attributeCode);
            $attribute->setTitle('Size');
            $attribute->setBackendModel(AbstractAttributeType::BACKEND_MODEL_ATTRIBUTE_VALUE);
            $attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_INTEGER);
            $this->getProductManager()->getStorageManager()->persist($attribute);
            $messages[]= "Attribute ".$attributeCode." has been created";
        }

        // attribute color (if not exists)
        $attributeCode= 'color';
        $attribute = $this->getProductManager()->getAttributeRepository()->findOneByCode($attributeCode);
        if ($attribute) {
            $messages[]= "Attribute ".$attributeCode." already exists";
        } else {
            $attribute = $this->getProductManager()->createAttribute();
            $attribute->setCode($attributeCode);
            $attribute->setTitle('Color');
            $attribute->setBackendModel(AbstractAttributeType::BACKEND_MODEL_ATTRIBUTE_VALUE);
            $attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_OPTION);
            $attribute->setTranslatable(false); // only one value but option can be translated in option values
            // add translatable option and related value "Red", "Blue", "Green"
            $colors = array("Red", "Blue", "Green");
            foreach ($colors as $color) {
                $option = $this->getProductManager()->createNewAttributeOption();
                $option->setTranslatable(true);
                $optionValue = $this->getProductManager()->createAttributeOptionValue();
                $optionValue->setValue($color);
                $option->addOptionValue($optionValue);
                $attribute->addOption($option);
            }
            $this->getProductManager()->getStorageManager()->persist($attribute);
            $messages[]= "Attribute ".$attributeCode." has been created";
        }

        $this->getProductManager()->getStorageManager()->flush();

        $this->get('session')->setFlash('notice', implode(', ', $messages));

        return $this->redirect($this->generateUrl('oro_product_attribute_index'));
    }

}
