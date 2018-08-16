<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Sets a data in a product.
 * It handles almost every data type of the PIM except media ones.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeSetter extends AbstractAttributeSetter
{
    /**
     * @param EntityWithValuesBuilderInterface $entityWithValuesBuilder
     * @param string[]                         $supportedTypes
     */
    public function __construct(EntityWithValuesBuilderInterface $entityWithValuesBuilder, array $supportedTypes)
    {
        parent::__construct($entityWithValuesBuilder);

        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeData(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);

        $this->entityWithValuesBuilder->addOrReplaceValue(
            $entityWithValues,
            $attribute,
            $options['locale'],
            $options['scope'],
            $data
        );
    }
}
