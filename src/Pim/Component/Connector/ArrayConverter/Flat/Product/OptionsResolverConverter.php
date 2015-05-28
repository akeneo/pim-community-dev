<?php

namespace Pim\Component\Connector\ArrayConverter\Flat\Product;

use Pim\Component\Connector\ArrayConverter\Flat\Product\Resolver\ProductAssociationFieldResolver;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Resolver\ProductOptionalAttributeFieldResolver;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Resolve product saving options for converter
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: missing specs
 */
class OptionsResolverConverter
{
    /** @var ProductAssociationFieldResolver */
    protected $assocFieldResolver;

    /** @var ProductOptionalAttributeFieldResolver */
    protected $optAttrFieldExtractor;

    /**
     * @param ProductAssociationFieldResolver       $assocFieldResolver
     * @param ProductOptionalAttributeFieldResolver $optAttrFieldExtractor
     */
    public function __construct(
        ProductAssociationFieldResolver $assocFieldResolver,
        ProductOptionalAttributeFieldResolver $optAttrFieldExtractor
    ) {
        $this->assocFieldResolver    = $assocFieldResolver;
        $this->optAttrFieldExtractor = $optAttrFieldExtractor;
    }

    /**
     * @param array $item
     * @param array $options
     *
     * @return array
     */
    public function resolveConverterOptions(array $item, array $options = [])
    {
        $enabled = (isset($options['default_values']['enabled'])) ? $options['default_values']['enabled'] : true;
        $resolver = $this->createOptionsResolver();
        $resolver->setDefaults(['enabled' => $enabled]);
        $resolvedItem = $resolver->resolve($item);

        return $resolvedItem;
    }

    /**
     * @return OptionsResolverInterface
     */
    protected function createOptionsResolver()
    {
        $resolver = new OptionsResolver();

        $required = [];
        $allowedTypes = [
            'family'     => 'string',
            'enabled'    => 'bool',
            'categories' => 'string',
            'groups'     => 'string'
        ];
        $optional = array_merge(
            ['family', 'enabled', 'categories', 'groups'],
            $this->optAttrFieldExtractor->resolveOptionalAttributeFields(),
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
