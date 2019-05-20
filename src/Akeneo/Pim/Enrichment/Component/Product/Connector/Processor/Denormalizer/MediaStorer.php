<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Exception\InvalidFile;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

class MediaStorer
{
    /** @var FileStorerInterface */
    private $fileStorer;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(FileStorerInterface $fileStorer, AttributeRepositoryInterface $attributeRepository)
    {
        $this->fileStorer = $fileStorer;
        $this->attributeRepository = $attributeRepository;
    }

    public function store(array $rawProductValues): array
    {
        $mediaAttributes = $this->attributeRepository->findMediaAttributeCodes();

        foreach ($rawProductValues as $attributeCode => $values) {
            if (in_array($attributeCode, $mediaAttributes)) {
                foreach ($values as $index => $value) {
                    if (empty($value['data'])) {
                        continue;
                    }
                    try {
                        $file = $this->fileStorer->store(
                            new \SplFileInfo($value['data']),
                            FileStorage::CATALOG_STORAGE_ALIAS
                        );
                    } catch (InvalidFile $e) {
                        throw InvalidPropertyException::validPathExpected($attributeCode, self::class, $value['data']);
                    }
                    $rawProductValues[$attributeCode][$index]['data'] = $file->getKey();
                }
            }
        }

        return $rawProductValues;
    }
}
