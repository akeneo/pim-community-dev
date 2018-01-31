<?php

namespace Pim\Component\Enrich\Converter\StandardToEnrich;

use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueConverter implements ConverterInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var FileInfoRepositoryInterface */
    protected $fileInfoRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param FileInfoRepositoryInterface  $fileInfoRepository
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FileInfoRepositoryInterface $fileInfoRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->fileInfoRepository = $fileInfoRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Convert media attributes to have "originalFilename" in addition to "filePath"
     * Before:
     * {
     *     "picture": {
     *          "locale": null,
     *          "scope": null,
     *          "data": "a/b/c/b/s936265s65_my_picture.jpg"
     *      }
     * }
     *
     * After:
     * {
     *    "picture": {
     *         "locale": null,
     *         "scope": null,
     *         "data": {
     *             "originalFilename": "my_picture.jpg",
     *             "filePath": "a/b/c/b/s936265s65_my_picture.jpg"
     *         }
     *     }
     * }
     */
    public function convert(array $productValues)
    {
        $mediaAttributes = $this->attributeRepository->findMediaAttributeCodes();

        foreach ($productValues as $code => $values) {
            if (in_array($code, $mediaAttributes)) {
                foreach ($values as $index => $value) {
                    $file = $this->fileInfoRepository->findOneByIdentifier($value['data']);
                    $data = [
                        'filePath'         => $value['data'],
                        'originalFilename' => null,
                    ];

                    if (null !== $file) {
                        $data['originalFilename'] = $file->getOriginalFilename();
                    }

                    $productValues[$code][$index]['data'] = $data;
                }
            }
        }

        return $productValues;
    }
}
