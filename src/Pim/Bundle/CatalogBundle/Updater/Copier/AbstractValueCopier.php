<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Abstract copier
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractValueCopier implements CopierInterface
{
    /** @var ProductBuilder */
    protected $productBuilder;

    /** @var array */
    protected $supportedTypes = [];

    /**
     * @param ProductBuilder $productBuilder
     * @param array          $supportedTypes
     */
    public function __construct(ProductBuilder $productBuilder, array $supportedTypes)
    {
        $this->productBuilder = $productBuilder;
        $this->supportedTypes = $supportedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(AttributeInterface $fromAttribute, AttributeInterface $toAttribute)
    {
        $supportsFrom = in_array($fromAttribute->getAttributeType(), $this->supportedTypes);
        $supportsTo   = in_array($toAttribute->getAttributeType(), $this->supportedTypes);

        return $supportsFrom && $supportsTo;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedTypes()
    {
        return $this->supportedTypes;
    }
}
