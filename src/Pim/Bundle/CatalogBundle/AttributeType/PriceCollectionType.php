<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Pim\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;

/**
 * Price attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionType extends AbstractAttributeType
{
    /**
     * @var CurrencyManager
     */
    protected $currencyManager;

    /**
     * Constructor
     *
     * @param string                     $backendType       the backend type
     * @param string                     $formType          the form type
     * @param ConstraintGuesserInterface $constraintGuesser the constraint guesser
     * @param CurrencyManager            $manager           the currency manager
     */
    public function __construct(
        $backendType,
        $formType,
        ConstraintGuesserInterface $constraintGuesser,
        CurrencyManager $manager
    ) {
        parent::__construct($backendType, $formType, $constraintGuesser);

        $this->currencyManager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        $options = parent::prepareValueFormOptions($value);
        $options['type']         = 'pim_enrich_price';
        $options['allow_add']    = true;
        $options['allow_delete'] = true;
        $options['by_reference'] = false;

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AbstractAttribute $attribute)
    {
        $properties = array(
            array(
                'name'      => 'numberMin',
                'fieldType' => 'number'
            ),
            array(
                'name'      => 'numberMax',
                'fieldType' => 'number'
            ),
            array(
                'name'      => 'decimalsAllowed',
                'fieldType' => 'switch',
                'options'   => array(
                    'attr' => $attribute->getId() ? array() : array('checked' => 'checked')
                )
            ),
            array(
                'name'      => 'searchable',
                'fieldType' => 'switch'
            ),
            array(
                'name'      => 'translatable',
                'fieldType' => 'switch',
                'options'   => array(
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                )
            ),
            array(
                'name'      => 'availableLocales',
                'fieldType' => 'pim_enrich_available_locales'
            ),
            array(
                'name'      => 'scopable',
                'fieldType' => 'pim_enrich_scopable',
                'options'   => array(
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                )
            ),
            array(
                'name'      => 'unique',
                'fieldType' => 'switch',
                'options'   => array(
                    'disabled'  => true,
                    'read_only' => true
                )
            )
        );

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_price_collection';
    }
}
