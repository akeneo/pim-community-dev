<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Converter\InternalApiToStandard;

use Akeneo\Pim\Enrichment\Bundle\Sql\GetMediaAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueConverter implements ConverterInterface
{
    /** @var GetMediaAttributeCodes */
    protected $getMediaAttributeCodes;

    /**
     * @param GetMediaAttributeCodes $getMediaAttributeCodes
     */
    public function __construct(GetMediaAttributeCodes $getMediaAttributeCodes)
    {
        $this->getMediaAttributeCodes = $getMediaAttributeCodes;
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
        $mediaAttributes = $this->getMediaAttributeCodes->execute();

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
