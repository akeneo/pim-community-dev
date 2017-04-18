<?php

namespace Pim\Component\Catalog\Factory;

use Akeneo\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Pim\Component\Catalog\Model\ProductValueCollection;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;

/**
 * Create a product value collection.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueCollectionFactory
{
    /** @var ProductValueFactory */
    private $valueFactory;

    /** @var CachedObjectRepositoryInterface */
    private $attributeRepository;

    /**
     * @param ProductValueFactory             $valueFactory
     * @param CachedObjectRepositoryInterface $attributeRepository
     */
    public function __construct(ProductValueFactory $valueFactory, CachedObjectRepositoryInterface $attributeRepository)
    {
        $this->valueFactory = $valueFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Create product values from raw values described in the storage format.
     * @see Pim\Component\Catalog\Normalizer\Storage\Product\ProductValuesNormalizer.php
     *
     * @param array $rawValues
     *
     * @return ProductValueCollectionInterface
     */
    public function createFromStorageFormat(array $rawValues)
    {
        $values = [];

        foreach ($rawValues as $attributeCode => $channelRawValue) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            //TODO: TIP-673 what to do in case the attribute does not exist?

            foreach ($channelRawValue as $channelCode => $localeRawValue) {
                if ('<all_channels>' === $channelCode) {
                    $channelCode = null;
                }

                foreach ($localeRawValue as $localeCode => $data) {
                    if ('<all_locales>' === $localeCode) {
                        $localeCode = null;
                    }

                    $values[] = $this->valueFactory->create($attribute, $channelCode, $localeCode, $data);
                }
            }
        }

        return new ProductValueCollection($values);
    }
}
