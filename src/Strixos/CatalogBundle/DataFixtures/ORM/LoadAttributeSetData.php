<?php
// src/Strixos/CatalogBundle/DataFixtures/ORM/LoadAttributeSetData.php

namespace Strixos\CatalogBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Strixos\CatalogBundle\Entity\AttributeSet;
use Strixos\CatalogBundle\Entity\Attribute;

/**
 * Execute with "php app/console doctrine:fixtures:load"
 *
 * @author Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LoadAttributeSetData extends AbstractFixture implements OrderedFixtureInterface
{
    const ATTRIBUTE_SET_BASE      = 'base';

    const ATTRIBUTE_SET_TSHIRT    = 'tshirt';
    const ATTRIBUTE_TSHIRT_COLOR  = 'tshirt_color';
    const ATTRIBUTE_TSHIRT_SIZE   = 'tshirt_size';

    const ATTRIBUTE_SET_LAPTOP    = 'laptop';
    const ATTRIBUTE_LAPTOP_SCREEN = 'laptop_screen-size';
    const ATTRIBUTE_LAPTOP_CPU    = 'laptop_cpu';
    const ATTRIBUTE_LAPTOP_MEMORY = 'laptop_memory';
    const ATTRIBUTE_LAPTOP_HDD    = 'laptop_hdd';

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // base set
        $baseSet = $this->_createBaseSet($manager);
        // t-shirt set
        $this->_createTShirtSet($manager, $baseSet);
        // laptop set
        $this->_createLaptopSet($manager, $baseSet);
    }

    /**
     * Executing order
     * @see Doctrine\Common\DataFixtures.OrderedFixtureInterface::getOrder()
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * Create base attribute set
     */
    protected function _createBaseSet(ObjectManager $manager)
    {
        // create attribute set
        $attributeSet = new AttributeSet();
        $attributeSet->setCode(self::ATTRIBUTE_SET_BASE);
        // default attribute code to type
        $attributes = array(
            // base
            'name' => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => true, 'is_unique' => false),
            'description' => array('type' => Attribute::BACKEND_TYPE_TEXT, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'short_description' => array('type' => Attribute::BACKEND_TYPE_TEXT, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            // metas / seo
            'meta_title' => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'meta_keyword' => array('type' => Attribute::BACKEND_TYPE_TEXT, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'meta_description' => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'url_key' => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            // prices and costs
            'price' => array('type' => Attribute::BACKEND_TYPE_DECIMAL, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'special_price' => array('type' => Attribute::BACKEND_TYPE_DECIMAL, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'special_from_date' => array('type' => Attribute::BACKEND_TYPE_DATETIME, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'special_to_date' => array('type' => Attribute::BACKEND_TYPE_DATETIME, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'cost' => array('type' => Attribute::BACKEND_TYPE_DECIMAL, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'tax_class' => array('type' => Attribute::BACKEND_TYPE_INT, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            // image
            'image' => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'image_label' => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'small_image' => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'small_image_label' => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'thumbnail' => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'thumbnail_label' => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            // technical
            'status' => array('type' => Attribute::BACKEND_TYPE_INT, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'weight' => array('type' => Attribute::BACKEND_TYPE_DECIMAL, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'weight_type' => array('type' => Attribute::BACKEND_TYPE_INT, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'country_of_manufacture' => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'is_returnable' => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'news_from_date' => array('type' => Attribute::BACKEND_TYPE_DATETIME, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            'news_to_date' => array('type' => Attribute::BACKEND_TYPE_DATETIME, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
        );
        // create attributes
        foreach ($attributes as $code => $data) {
            $attribute = $this->_createAttribute($code, $data);
            $manager->persist($attribute);
            // add attribute to default set
            $attributeSet->addAttribute($attribute);
        }
        // persist set
        $manager->persist($attributeSet);
        $manager->flush();
        return $attributeSet;
    }


    /**
    * Create t-shirt attribute set
     */
    protected function _createAttribute($code, $data)
    {
        $attribute = new Attribute();
        $attribute->setCode($code);
        $attribute->setType($data['type']);
        $attribute->setInput($data['input']);
        $attribute->setIsRequired($data['is_required']);
        $attribute->setIsUnique($data['is_unique']);
        return $attribute;
    }

    /**
    * Create t-shirt attribute set
    */
    protected function _createTShirtSet(ObjectManager $manager, AttributeSet $sourceSet)
    {
        $attributeSet = $sourceSet->copy(self::ATTRIBUTE_SET_TSHIRT);
        // size and color attributes
        $attributes = array(
            self::ATTRIBUTE_TSHIRT_COLOR => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            self::ATTRIBUTE_TSHIRT_SIZE => array('type' => Attribute::BACKEND_TYPE_INT, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
        );
        // create attributes
        foreach ($attributes as $code => $data) {
            $attribute = $this->_createAttribute($code, $data);
            $manager->persist($attribute);
            // add attribute to default set
            $attributeSet->addAttribute($attribute);
        }
        // persist set
        $manager->persist($attributeSet);
        $manager->flush();
    }


    /**
    * Create laptop attribute set
     */
    protected function _createLaptopSet(ObjectManager $manager, AttributeSet $sourceSet)
    {
        $attributeSet = $sourceSet->copy(self::ATTRIBUTE_SET_LAPTOP);
                // size and color attributes
        $attributes = array(
            self::ATTRIBUTE_LAPTOP_CPU => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            self::ATTRIBUTE_LAPTOP_HDD => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            self::ATTRIBUTE_LAPTOP_MEMORY => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
            self::ATTRIBUTE_LAPTOP_SCREEN => array('type' => Attribute::BACKEND_TYPE_VARCHAR, 'input' => Attribute::FRONTEND_TYPE_INPUT, 'is_required' => false, 'is_unique' => false),
        );
        // create attributes
        foreach ($attributes as $code => $data) {
            $attribute = $this->_createAttribute($code, $data);
            $manager->persist($attribute);
            // add attribute to default set
            $attributeSet->addAttribute($attribute);
        }
        // persist set
        $manager->persist($attributeSet);
        $manager->flush();
    }
}