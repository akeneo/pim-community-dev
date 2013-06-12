<?php
namespace Pim\Bundle\ProductBundle\Service;

use Pim\Bundle\ProductBundle\Manager\ProductManager;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ConfigBundle\Manager\LocaleManager;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;

use Doctrine\ORM\EntityRepository;

/**
 * Attribute Service
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AttributeService
{
    /**
     * @var ProductManager
     */
    protected $manager;

    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @var AttributeTypeFactory
     */
    protected $factory;

    /**
     * Constructor
     *
     * @param ProductManager       $manager       Product manager
     * @param LocaleManager        $localeManager Locale manager
     * @param AttributeTypeFactory $factory       Attribute type factory
     */
    public function __construct(ProductManager $manager, LocaleManager $localeManager, AttributeTypeFactory $factory)
    {
        $this->manager = $manager;
        $this->localeManager = $localeManager;
        $this->factory = $factory;
    }

    /**
     * Create a ProductAttribute object from data in the form
     *
     * @param array $data Form data
     *
     * @return ProductAttribute $attribute | null
     */
    public function createAttributeFromFormData($data)
    {
        if ($data instanceof ProductAttribute) {
            return $data;
        }

        if (gettype($data) === 'array' && isset($data['attributeType'])) {
            return $this->manager->createAttribute($data['attributeType']);
        } elseif (gettype($data) === 'array' && isset($data['id'])) {
            return $this->manager->getAttributeRepository()->find($data['id']);
        } else {
            return null;
        }
    }

    /**
     * Prepare data for binding to the form
     *
     * @param array $data Form data
     *
     * @return array Prepared form data
     */
    public function prepareFormData($data)
    {
        $optionTypes = array(
            'pim_product_multiselect',
            'pim_product_simpleselect'
        );

        // If the attribute type can have options but no options have been created,
        // create an empty option to render the corresponding form fields
        if (in_array($data['attributeType'], $optionTypes) && !isset($data['options'])) {
            $option = array(
                'optionValues' => array()
            );

            foreach ($this->localeManager->getActiveLocales() as $locale) {
                $option['optionValues'][] = array(
                    'locale' => $locale->getCode()
                );
            }

            $data['options'] = array($option);
        }

        return $data;
    }

    /**
     * Return an array of available attribute types
     *
     * @return array $types
     */
    public function getAttributeTypes()
    {
        $types = $this->manager->getAttributeTypes();
        $choice = array();
        foreach ($types as $type) {
            $choice[$type]= $type;
        }
        asort($choice);

        return $choice;
    }
}
