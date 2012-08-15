<?php
// src/Strixos/CatalogBundle/DataFixtures/ORM/LoadAttributeSetData.php

namespace Strixos\CatalogBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Strixos\CatalogBundle\Entity\AttributeSet;
use Strixos\CatalogBundle\Entity\Attribute;
use Strixos\CatalogBundle\Entity\Option;

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
    const ATTRIBUTE_LAPTOP_SCREEN = 'laptop_screen_size';
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
            'name'              => array('input' => Attribute::FRONTEND_INPUT_TEXTFIELD, 'is_required' => true, 'is_unique' => false),
            'description'       => array('input' => Attribute::FRONTEND_INPUT_TEXTAREA, 'is_required' => false, 'is_unique' => false),
            'short_description' => array('input' => Attribute::FRONTEND_INPUT_TEXTAREA, 'is_required' => false, 'is_unique' => false),
            // metas / seo
            'meta_title'        => array('input' => Attribute::FRONTEND_INPUT_TEXTFIELD, 'is_required' => false, 'is_unique' => false),
            'meta_keyword'      => array('input' => Attribute::FRONTEND_INPUT_TEXTFIELD, 'is_required' => false, 'is_unique' => false),
            'meta_description'  => array('input' => Attribute::FRONTEND_INPUT_TEXTAREA, 'is_required' => false, 'is_unique' => false),
            'url_key'           => array('input' => Attribute::FRONTEND_INPUT_TEXTFIELD, 'is_required' => false, 'is_unique' => false),
            // prices and costs
            'price'             => array('input' => Attribute::FRONTEND_INPUT_PRICE, 'is_required' => false, 'is_unique' => false),
            'special_price'     => array('input' => Attribute::FRONTEND_INPUT_PRICE, 'is_required' => false, 'is_unique' => false),
            'special_from_date' => array('input' => Attribute::FRONTEND_INPUT_DATE, 'is_required' => false, 'is_unique' => false),
            'special_to_date'   => array('input' => Attribute::FRONTEND_INPUT_DATE, 'is_required' => false, 'is_unique' => false),
            'cost'              => array('input' => Attribute::FRONTEND_INPUT_PRICE, 'is_required' => false, 'is_unique' => false),
            'tax_class'         => array('input' => Attribute::FRONTEND_INPUT_TEXTFIELD, 'is_required' => false, 'is_unique' => false),
            // image
            'image'             => array('input' => Attribute::FRONTEND_INPUT_TEXTFIELD, 'is_required' => false, 'is_unique' => false),
            'image_label'       => array('input' => Attribute::FRONTEND_INPUT_TEXTFIELD, 'is_required' => false, 'is_unique' => false),
            'small_image'       => array('input' => Attribute::FRONTEND_INPUT_TEXTFIELD, 'is_required' => false, 'is_unique' => false),
            'small_image_label' => array('input' => Attribute::FRONTEND_INPUT_TEXTFIELD, 'is_required' => false, 'is_unique' => false),
            'thumbnail'         => array('input' => Attribute::FRONTEND_INPUT_TEXTFIELD, 'is_required' => false, 'is_unique' => false),
            'thumbnail_label'   => array('input' => Attribute::FRONTEND_INPUT_TEXTFIELD, 'is_required' => false, 'is_unique' => false),
            // technical
            'status'            => array('input' => Attribute::FRONTEND_INPUT_SELECT, 'is_required' => false, 'is_unique' => false),
            'weight'            => array('input' => Attribute::FRONTEND_INPUT_TEXTFIELD, 'is_required' => false, 'is_unique' => false),
            'weight_type'       => array('input' => Attribute::FRONTEND_INPUT_TEXTFIELD, 'is_required' => false, 'is_unique' => false),
            'country_of_manufacture' => array('input' => Attribute::FRONTEND_INPUT_TEXTFIELD, 'is_required' => false, 'is_unique' => false),
            'is_returnable'     => array('input' => Attribute::FRONTEND_INPUT_CHECKBOX, 'is_required' => false, 'is_unique' => false),
            'news_from_date'    => array('input' => Attribute::FRONTEND_INPUT_DATE, 'is_required' => false, 'is_unique' => false),
            'news_to_date'      => array('input' => Attribute::FRONTEND_INPUT_DATE, 'is_required' => false, 'is_unique' => false),
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
            self::ATTRIBUTE_TSHIRT_COLOR => array('input' => Attribute::FRONTEND_INPUT_TEXTFIELD, 'is_required' => false, 'is_unique' => false),
            self::ATTRIBUTE_TSHIRT_SIZE => array('input' => Attribute::FRONTEND_INPUT_TEXTFIELD, 'is_required' => false, 'is_unique' => false),
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
            self::ATTRIBUTE_LAPTOP_CPU => array('input' => Attribute::FRONTEND_INPUT_SELECT, 'is_required' => false, 'is_unique' => false),
            self::ATTRIBUTE_LAPTOP_HDD => array('input' => Attribute::FRONTEND_INPUT_SELECT, 'is_required' => false, 'is_unique' => false),
            self::ATTRIBUTE_LAPTOP_MEMORY => array('input' => Attribute::FRONTEND_INPUT_SELECT, 'is_required' => false, 'is_unique' => false),
            self::ATTRIBUTE_LAPTOP_SCREEN => array('input' => Attribute::FRONTEND_INPUT_SELECT, 'is_required' => false, 'is_unique' => false),
        );
        $options = array(
            self::ATTRIBUTE_LAPTOP_CPU => array('I5', 'I7'),
            self::ATTRIBUTE_LAPTOP_HDD => array('IDE 1000 GO', 'IDE 750 GO', 'Sata 200 GO', 'Sata 400 GO'),
            self::ATTRIBUTE_LAPTOP_MEMORY => array('4 GO', '8 GO'),
            self::ATTRIBUTE_LAPTOP_SCREEN => array('11"', '13"', '15"', '17"'),
        );

        // create attributes
        foreach ($attributes as $code => $data) {
            $attribute = $this->_createAttribute($code, $data);
            // add options
            foreach ($options[$code] as $value) {
                $option = new Option();
                $option->setValue($value);
                $option->setAttribute($attribute);
                $manager->persist($option);
            }
            $manager->persist($attribute);
            // add attribute to default set
            $attributeSet->addAttribute($attribute);
        }
        // persist set
        $manager->persist($attributeSet);
        $manager->flush();
    }
}