<?php

namespace Pim\Component\Catalog\ValuesFilter;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Pim\Component\Catalog\Exception\InvalidAttributeException;
use Pim\Component\Catalog\Exception\InvalidOptionException;
use Pim\Component\Catalog\Exception\InvalidOptionsException;
use Pim\Component\Catalog\Model\ValueCollection;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface StorageFormatFilter
{
    /**
     * Filter raw values that correspond to an non existing data.
     * For example, filter product values from a deleted attribute for a single product.
     *
     * @param array $rawValues
     *
     * @return array
     */
    public function filterSingle(array $rawValues): array;
}
