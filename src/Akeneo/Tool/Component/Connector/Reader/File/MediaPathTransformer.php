<?php

namespace Akeneo\Tool\Component\Connector\Reader\File;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

/**
 * Transforms media relative path to absolute path
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaPathTransformer
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
     * Transforms a relative path to absolute. Data must be provided in the pivot format.
     *
     * $item exemple:
     * [
     *   'side_view' => [
     *     [
     *       'locale' => null,
     *       'scope'  => null,
     *       'data'   => 'cat_003.png'
     *     ]
     *   ]
     * ]
     *
     * @param array  $attributeValues An associative array (attribute_code => values)
     * @param string $filePath        The absolute path
     *
     * @return array
     */
    public function transform(array $attributeValues, $filePath)
    {
        $mediaAttributes = $this->attributeRepository->findMediaAttributeCodes();

        foreach ($attributeValues as $code => $values) {
            if (in_array($code, $mediaAttributes)) {
                foreach ($values as $index => $value) {
                    if (isset($value['data'])) {
                        $dataFilePath = $value['data'];
                        $attributeValues[$code][$index]['data'] = sprintf(
                            '%s%s%s',
                            $filePath,
                            DIRECTORY_SEPARATOR,
                            $dataFilePath
                        );
                    }
                }
            }
        }

        return $attributeValues;
    }
}
