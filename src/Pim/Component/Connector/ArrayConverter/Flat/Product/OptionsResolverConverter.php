<?php

namespace Pim\Component\Connector\ArrayConverter\Flat\Product;

use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Pim\Bundle\CatalogBundle\Manager\AttributeValuesResolver;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\Flat\ProductAssociationFieldResolver;
use Pim\Component\Connector\ArrayConverter\Flat\ProductOptionalAttributeFieldExtractor;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Resolve product saving options for converter
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsResolverConverter
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /** @var AttributeValuesResolver */
    protected $valuesResolver;

    /** @var array */
    protected $optionalAttributeFields;

    /** @var ProductAssociationFieldResolver */
    protected $assocFieldResolver;

    /** @var ProductOptionalAttributeFieldExtractor */
    protected $optAttrFieldExtractor;

    /**
     * @param AttributeRepositoryInterface           $attributeRepository
     * @param ProductAssociationFieldResolver        $assocFieldResolver
     * @param ProductOptionalAttributeFieldExtractor $optAttrFieldExtractor
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ProductAssociationFieldResolver $assocFieldResolver,
        ProductOptionalAttributeFieldExtractor $optAttrFieldExtractor
    ) {
        $this->attributeRepository   = $attributeRepository;
        $this->assocFieldResolver    = $assocFieldResolver;
        $this->optAttrFieldExtractor = $optAttrFieldExtractor;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveConverterOptions(array $options)
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setDefaults(['enabled' => 1]);
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * @return OptionsResolverInterface
     */
    protected function createOptionsResolver()
    {
        $resolver = new OptionsResolver();

        $required = ['family', 'enabled', 'categories', 'groups'];
        $allowedTypes = [
            'family'     => 'string',
            'enabled'    => 'bool',
            'categories' => 'string',
            'groups'     => 'string'
        ];
        $optional = array_merge(
            $this->optAttrFieldExtractor->getOptionalAttributeFields(),
            $this->getOptionalAssociationFields()
        );

        $resolver->setRequired($required);
        $resolver->setOptional($optional);
        $resolver->setAllowedTypes($allowedTypes);
        $booleanNormalizer = function ($options, $value) {
            return (bool) $value;
        };
        $resolver->setNormalizers(['enabled' => $booleanNormalizer]);

        return $resolver;
    }

    /**
     * @return array
     */
    protected function getOptionalAssociationFields()
    {
        if (empty($this->optionalAssociationFields)) {
            $this->optionalAssociationFields = $this->assocFieldResolver->resolveAssociationFields();
        }

        return $this->optionalAssociationFields;
    }
}
