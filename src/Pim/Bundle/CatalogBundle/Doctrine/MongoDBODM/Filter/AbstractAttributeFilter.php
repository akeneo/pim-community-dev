<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Validator\AttributeValidatorHelper;

/**
 * Abstract MongoDB attribute filter
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeFilter extends AbstractFilter implements AttributeFilterInterface
{
    /** @var AttributeValidatorHelper */
    protected $attrValidatorHelper;

    /** @var string[] */
    protected $supportedAttributeTypes;

    /**
     * {@inheritdoc}
     */
    public function getAttributeTypes()
    {
        return $this->supportedAttributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getAttributeType(), $this->supportedAttributeTypes);
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
            $this->attrValidatorHelper->validateLocale($attribute, $locale);
            $this->attrValidatorHelper->validateScope($attribute, $scope);
        } catch (\LogicException $e) {
            throw InvalidArgumentException::expectedFromPreviousException(
                $e,
                $attribute->getCode(),
                'filter',
                $type
            );
        }
    }
}
