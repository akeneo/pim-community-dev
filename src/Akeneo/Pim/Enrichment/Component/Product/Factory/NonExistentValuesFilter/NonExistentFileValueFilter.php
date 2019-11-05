<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class NonExistentFileValueFilter implements NonExistentValuesFilter
{
    /** @var FileInfoRepositoryInterface */
    private $fileInfoRepository;

    public function __construct(FileInfoRepositoryInterface $fileInfoRepository)
    {
        $this->fileInfoRepository = $fileInfoRepository;
    }

    /**
     * This filter remove all non existing value with file keys that does not exist anymore.
     *
     * Also, this filter replaces the keys "file/key" by the corresponding FileInfoInterface entity.
     * This is done to improve the performance of the creation of the values later on in ImageValueFactory and FileValueFactory.
     * As we execute here only one request, this replacement avoids 1 + n requests in these factories.
     *
     * @return OnGoingFilteredRawValues
     */
    public function filter(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $filteredFiles = $this->filterByType($onGoingFilteredRawValues, AttributeTypes::FILE);
        $filteredImagesAndFiles = $this->filterByType($filteredFiles, AttributeTypes::IMAGE);

        return $filteredImagesAndFiles;
    }

    private function filterByType(OnGoingFilteredRawValues $onGoingFilteredRawValues, string $type): OnGoingFilteredRawValues
    {
        $fileAndImageValues = $onGoingFilteredRawValues->notFilteredValuesOfTypes($type);
        if (empty($fileAndImageValues)) {
            return $onGoingFilteredRawValues;
        }

        $existingEntities = $this->getFilesIndexedByKey($fileAndImageValues);
        $filteredValues = [];

        foreach ($fileAndImageValues as $attributeCode => $productValueCollection) {
            foreach ($productValueCollection as $productValues) {
                $fileValues = [];
                foreach ($productValues['values'] as $channel => $channelValues) {
                    foreach ($channelValues as $locale => $value) {
                        if (!is_array($value)) {
                            $fileValues[$channel][$locale] = $existingEntities[$value] ?? null;
                        }
                    }
                }
                if ($fileValues !== []) {
                    $filteredValues[$type][$attributeCode][] = [
                        'identifier' => $productValues['identifier'],
                        'values' => $fileValues,
                    ];
                }
            }
        }

        return $onGoingFilteredRawValues->addFilteredValuesIndexedByType($filteredValues);
    }

    private function getFilesIndexedByKey(array $fileAndImageValues): array
    {
        $fileKeys = [];
        foreach ($fileAndImageValues as $attributeCode => $valueCollection) {
            foreach ($valueCollection as $values) {
                foreach ($values['values'] as $channel => $channelValues) {
                    foreach ($channelValues as $locale => $value) {
                        if (!is_array($value)) {
                            $fileKeys[] = $value;
                        }
                    }
                }
            }
        }

        $files = $this->fileInfoRepository->findBy(['key' => array_unique($fileKeys)]);
        $filesIndexedByKey = [];

        /** @var FileInfoInterface $file */
        foreach ($files as $file) {
            $filesIndexedByKey[$file->getKey()] = $file;
        }

        return $filesIndexedByKey;
    }
}
