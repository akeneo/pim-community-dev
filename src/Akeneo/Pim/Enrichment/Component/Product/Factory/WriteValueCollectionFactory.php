<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WriteValueCollectionFactory
{
    /** @var ReadValueCollectionFactory */
    private $readValueCollectionFactory;

    public function __construct(
        ReadValueCollectionFactory $readValueCollectionFactory
    ) {
        $this->readValueCollectionFactory = $readValueCollectionFactory;
    }

    public function createFromStorageFormat(array $rawValues): WriteValueCollection
    {
        $valueCollection = $this->readValueCollectionFactory->createFromStorageFormat($rawValues);

        return WriteValueCollection::fromReadValueCollection($valueCollection);
    }

    public function createMultipleFromStorageFormat(array $rawValueCollections): array
    {
        $valueCollectionsList = $this->readValueCollectionFactory->createMultipleFromStorageFormat($rawValueCollections);

        $writeValueCollectionList = [];
        foreach ($valueCollectionsList as $identifier => $valueCollection) {
            $writeValueCollectionList[$identifier] = WriteValueCollection::fromReadValueCollection($valueCollection);
        }

        return $writeValueCollectionList;
    }
}
