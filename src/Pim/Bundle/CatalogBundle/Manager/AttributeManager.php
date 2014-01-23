<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Attribute manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeManager implements AttributeManagerInterface
{
    /**
     * @var string
     */
    protected $attributeClass;

    /**
     * @var string
     */
    protected $optionClass;

    /**
     * @var string
     */
    protected $optionValueClass;

    /**
     * @var string
     */
    protected $productClass;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

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
     * @param string               $attributeClass   Attribute class
     * @param string               $optionClass      Option class
     * @param string               $optionValueClass Option value class
     * @param string               $productClass     Product class
     * @param ObjectManager        $objectManager    Object manager
     * @param LocaleManager        $localeManager    Locale manager
     * @param AttributeTypeFactory $factory          Attribute type factory
     */
    public function __construct(
        $attributeClass,
        $optionClass,
        $optionValueClass,
        $productClass,
        ObjectManager $objectManager,
        LocaleManager $localeManager,
        AttributeTypeFactory $factory
    ) {
        $this->attributeClass   = $attributeClass;
        $this->optionClass      = $optionClass;
        $this->optionValueClass = $optionValueClass;
        $this->productClass     = $productClass;
        $this->objectManager    = $objectManager;
        $this->localeManager    = $localeManager;
        $this->factory          = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function createAttribute($type = null)
    {
        $class = $this->getAttributeClass();
        $attribute = new $class();
        $attribute->setEntityType($this->productClass);

        $attribute->setBackendStorage(AbstractAttributeType::BACKEND_STORAGE_ATTRIBUTE_VALUE);
        if ($type) {
            $attributeType = $this->factory->get($type);
            $attribute->setBackendType($attributeType->getBackendType());
            $attribute->setAttributeType($attributeType->getName());
        }

        return $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function createAttributeOption()
    {
        $class = $this->optionClass;

        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    public function createAttributeOptionValue()
    {
        $class = $this->optionValueClass;

        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeClass()
    {
        return $this->attributeClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeOptionClass()
    {
        return $this->optionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function createAttributeFromFormData($data)
    {
        if ($data instanceof AttributeInterface) {
            return $data;
        }

        if (gettype($data) === 'array' && isset($data['attributeType'])) {
            return $this->createAttribute($data['attributeType']);
        } elseif (gettype($data) === 'array' && isset($data['id'])) {
            return $this->objectManager->getRepository($this->attributeClass)->find($data['id']);
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareFormData($data)
    {
        $optionTypes = [
            'pim_catalog_multiselect',
            'pim_catalog_simpleselect'
        ];

        // If the attribute type can have options but no options have been created,
        // create an empty option to render the corresponding form fields
        if (in_array($data['attributeType'], $optionTypes) && !isset($data['options'])) {
            $option = [
                'optionValues' => []
            ];

            foreach ($this->localeManager->getActiveLocales() as $locale) {
                $option['optionValues'][] = [
                    'locale' => $locale->getCode()
                ];
            }

            $data['options'] = [$option];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeTypes()
    {
        $types = $this->factory->getAttributeTypes($this->productClass);
        $choices = [];
        foreach ($types as $type) {
            $choices[$type] = $type;
        }
        asort($choices);

        return $choices;
    }

    /**
     * Get the attribute group choices
     *
     * @return array
     */
    public function getAttributeGroupChoices()
    {
        $groups = $this->objectManager->getRepository('PimCatalogBundle:AttributeGroup')->findAllWithTranslations();
        $choices = [];
        foreach ($groups as $group) {
            $choices[$group->getCode()] = $group->getLabel();
        }
        asort($choices);

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareBackendProperties(AttributeInterface $attribute)
    {
        $baseAttribute = $this->createAttribute($attribute->getAttributeType());

        $attribute->setBackendType($baseAttribute->getBackendType());
        $attribute->setBackendStorage($baseAttribute->getBackendStorage());

        return $attribute;
    }
}
