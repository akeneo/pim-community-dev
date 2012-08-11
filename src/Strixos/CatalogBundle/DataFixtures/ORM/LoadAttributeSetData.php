<?php
// src/Strixos/CatalogBundle/DataFixtures/ORM/LoadUserData.php

namespace Strixos\CatalogBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
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
class LoadAtributeSetData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // base set
        $baseSet = $this->_createBaseSet($manager);
        // t-shirt set
        $this->_createTShirtSet($manager, $baseSet);
    }

    /**
     * Create base attribute set
     */
    protected function _createBaseSet(ObjectManager $manager)
    {
        // create attribute set
        $attributeSet = new AttributeSet();
        $attributeSet->setCode('base');
        // default attribute code to type
        $attributes = array(
            // base
            'name' => Attribute::BACKEND_TYPE_VARCHAR,
            'description' => Attribute::BACKEND_TYPE_TEXT,
            'short_description' => Attribute::BACKEND_TYPE_TEXT,
            // metas / seo
            'meta_title' => Attribute::BACKEND_TYPE_VARCHAR,
            'meta_keyword' => Attribute::BACKEND_TYPE_TEXT,
            'meta_description' => Attribute::BACKEND_TYPE_VARCHAR,
            'url_key' => Attribute::BACKEND_TYPE_VARCHAR,
            // prices and costs
            'price' => Attribute::BACKEND_TYPE_DECIMAL,
            'special_price' => Attribute::BACKEND_TYPE_DECIMAL,
            'special_from_date' => Attribute::BACKEND_TYPE_DATETIME,
            'special_to_date' => Attribute::BACKEND_TYPE_DATETIME,
            'cost' => Attribute::BACKEND_TYPE_DECIMAL,
            'tax_class' => Attribute::BACKEND_TYPE_INT,
            // image
            'image' => Attribute::BACKEND_TYPE_VARCHAR,
            'image_label' => Attribute::BACKEND_TYPE_VARCHAR,
            'small_image' => Attribute::BACKEND_TYPE_VARCHAR,
            'small_image_label' => Attribute::BACKEND_TYPE_VARCHAR,
            'thumbnail' => Attribute::BACKEND_TYPE_VARCHAR,
            'thumbnail_label' => Attribute::BACKEND_TYPE_VARCHAR,
            // technical
            'status' => Attribute::BACKEND_TYPE_INT,
            'weight' => Attribute::BACKEND_TYPE_DECIMAL,
            'weight_type' => Attribute::BACKEND_TYPE_INT,
            'country_of_manufacture' => Attribute::BACKEND_TYPE_VARCHAR,
            'is_returnable' => Attribute::BACKEND_TYPE_VARCHAR,
            'news_from_date' => Attribute::BACKEND_TYPE_DATETIME,
            'news_to_date' => Attribute::BACKEND_TYPE_DATETIME,
        );
        // create attributes
        foreach ($attributes as $code => $type) {
            $attribute = new Attribute();
            $attribute->setCode($code);
            $attribute->setType($type);
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
    protected function _createTShirtSet(ObjectManager $manager, AttributeSet $sourceSet)
    {
        $attributeSet = $sourceSet->copy('tshirt-straight');
        // size and color attributes
        $attributes = array(
            'tshirt-straight' => Attribute::BACKEND_TYPE_INT,
            'tshirt-color'    => Attribute::BACKEND_TYPE_INT,
        );
        // create attributes
        foreach ($attributes as $code => $type) {
            $attribute = new Attribute();
            $attribute->setCode($code);
            $attribute->setType($type);
            $manager->persist($attribute);
            // add attribute to default set
            $attributeSet->addAttribute($attribute);
        }
        // persist set
        $manager->persist($attributeSet);
        $manager->flush();
    }
}