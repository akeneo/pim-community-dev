<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Updater\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;

/**
 * Abstract setter
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractValueSetter implements SetterInterface
{
    /** @var array */
    protected $supportedTypes = [];

    /** @var AttributeValidatorHelper */
    protected $attributeValidatorHelper;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    public function __construct(
        ProductBuilderInterface $productBuilder,
        AttributeValidatorHelper $attributeValidatorHelper
    ) {
        $this->productBuilder = $productBuilder;
        $this->attributeValidatorHelper = $attributeValidatorHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(AttributeInterface $attribute)
    {
        return in_array($attribute->getAttributeType(), $this->supportedTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedTypes()
    {
        return $this->supportedTypes;
    }

    /**
     * Check locale and scope are valid
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     * @param string             $type
     *
     * @throws InvalidArgumentException
     */
    protected function checkLocaleAndScope(AttributeInterface $attribute, $locale, $scope, $type)
    {
        try {
            $this->attributeValidatorHelper->validateLocale($attribute, $locale);
            $this->attributeValidatorHelper->validateScope($attribute, $scope);
        } catch (\LogicException $e) {
            throw InvalidArgumentException::expectedFromPreviousException(
                $e,
                $attribute->getCode(),
                'setter',
                $type
            );
        }
    }
}
