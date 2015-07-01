<?php

namespace Pim\Component\Catalog\Updater\Remover;

use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeValidatorHelper;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Abstract attribute remover
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeRemover implements AttributeRemoverInterface
{
    /** @var array */
    protected $supportedTypes = [];

    /** @var AttributeValidatorHelper */
    protected $attrValidatorHelper;

    /** @var OptionsResolver */
    protected $resolver;

    /**
     * @param AttributeValidatorHelper $attrValidatorHelper
     */
    public function __construct(AttributeValidatorHelper $attrValidatorHelper)
    {
        $this->attrValidatorHelper = $attrValidatorHelper;
        $this->resolver = new OptionsResolver();
        $this->configureOptions($this->resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getAttributeType(), $this->supportedTypes);
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
                'setter',
                $type
            );
        }
    }

    /**
     * Configure the option resolver
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale', 'scope']);
    }
}
