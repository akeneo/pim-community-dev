<?php

namespace Pim\Component\Enrich\Converter\EnrichToStandard;

use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueConverter implements ConverterInterface
{
    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Before:
     * {
     *     "picture": {
     *          "locale": null,
     *          "scope": null,
     *          "data": {
     *              "originalFilename": "my_picture.jpg",
     *              "filePath": "a/b/c/b/s936265s65_my_picture.jpg"
     *          }
     *      }
     * }
     *
     * After:
     * {
     *    "picture": {
     *        "locale": null,
     *        "scope": null,
     *        "data": "a/b/c/b/s936265s65_my_picture.jpg"
     *     }
     * }
     */
    public function convert(array $productValues)
    {
        $mediaAttributes = $this->attributeRepository->findMediaAttributeCodes();

        foreach ($productValues as $code => $values) {
            if (in_array($code, $mediaAttributes)) {
                foreach ($values as $index => $value) {
                    $productValues[$code][$index]['data'] = $value['data']['filePath'];
                }
            }
        }

        return $productValues;
    }
}
