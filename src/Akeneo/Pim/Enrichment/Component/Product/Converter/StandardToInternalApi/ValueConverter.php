<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Converter\StandardToInternalApi;

use Akeneo\Pim\Enrichment\Bundle\Sql\GetAttributeTypeByCodes;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueConverter implements ConverterInterface
{
    /** @var GetAttributeTypeByCodes */
    protected $getAttributeTypeByCodes;

    /** @var FileInfoRepositoryInterface */
    protected $fileInfoRepository;

    /**
     * @param GetAttributeTypeByCodes $getAttributeTypeByCodes
     * @param FileInfoRepositoryInterface $fileInfoRepository
     */
    public function __construct(
        GetAttributeTypeByCodes $getAttributeTypeByCodes,
        FileInfoRepositoryInterface $fileInfoRepository
    ) {
        $this->getAttributeTypeByCodes = $getAttributeTypeByCodes;
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
        $attributeTypes = $this->getAttributeTypeByCodes->execute(array_keys($productValues));

        foreach ($productValues as $code => $values) {
            if ($attributeTypes[$code] === AttributeTypes::IMAGE || $attributeTypes[$code] === AttributeTypes::FILE) {
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
