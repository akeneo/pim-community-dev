<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;

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
     * @param ProductBuilderInterface $productBuilder
     * @param string[]                $supportedTypes
     */
    public function __construct(ProductBuilderInterface $productBuilder, array $supportedTypes)
    {
        parent::__construct($productBuilder);

        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeData(
        ProductInterface $product,
        AttributeInterface $attribute,
        $data,
        array $options = []
    ) {
        $options = $this->resolver->resolve($options);

        $this->productBuilder->addOrReplaceProductValue(
            $product,
            $attribute,
            $options['locale'],
            $options['scope'],
            $data
        );
    }
}
