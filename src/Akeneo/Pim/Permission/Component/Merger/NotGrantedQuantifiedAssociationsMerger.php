<?php

namespace Akeneo\Pim\Permission\Component\Merger;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;

class NotGrantedQuantifiedAssociationsMerger implements NotGrantedDataMergerInterface
{
    /** @var PropertySetterInterface */
    private $propertySetter;

    public function __construct(
        PropertySetterInterface $propertySetter
    ) {
        $this->propertySetter = $propertySetter;
    }

    /**
     * {@inheritdoc}
     */
    public function merge($filteredProduct, $fullProduct = null)
    {
        if (!$filteredProduct instanceof EntityWithQuantifiedAssociationsInterface) {
            throw InvalidObjectException::objectExpected(
                get_class($filteredProduct),
                ProductInterface::class
            );
        }

        if (null === $fullProduct) {
            return $filteredProduct;
        }

        if (!$fullProduct instanceof EntityWithQuantifiedAssociationsInterface) {
            throw InvalidObjectException::objectExpected(get_class($fullProduct), ProductInterface::class);
        }

        // @todo https://akeneo.atlassian.net/browse/RAC-38

        $this->propertySetter->setData(
            $fullProduct,
            'quantified_associations',
            $filteredProduct->normalizeQuantifiedAssociations()
        );

        return $fullProduct;
    }
}
