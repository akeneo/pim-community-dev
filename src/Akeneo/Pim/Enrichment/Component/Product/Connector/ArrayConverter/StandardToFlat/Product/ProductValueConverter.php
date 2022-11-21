<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter\ValueConverterRegistry;
use Akeneo\Tool\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;

/**
 * Standard to flat array converter for product value
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductValueConverter
{
    /** @var ValueConverterRegistry */
    protected $converterRegistry;

    /** @var CachedObjectRepositoryInterface */
    protected $attributeRepo;

    /**
     * @param ValueConverterRegistry          $converterRegistry
     * @param CachedObjectRepositoryInterface $attributeRepo
     */
    public function __construct(
        ValueConverterRegistry $converterRegistry,
        CachedObjectRepositoryInterface $attributeRepo
    ) {
        $this->converterRegistry = $converterRegistry;
        $this->attributeRepo = $attributeRepo;
    }

    /**
     * @param string $attributeCode
     * @param mixed  $data
     *
     * @return array
     */
    public function convertAttribute($attributeCode, $data)
    {
        $attribute = $this->attributeRepo->findOneByIdentifier($attributeCode);
        $converter = $this->converterRegistry->getConverter($attribute);

        if (null === $converter) {
            throw new \LogicException(
                sprintf(
                    'No standard to flat array converter found for attribute type "%s"',
                    $attribute->getType()
                )
            );
        }

        return $converter->convert($attributeCode, $data);
    }
}
