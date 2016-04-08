<?php

namespace Pim\Component\Connector\Reader\File\Product;

use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

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
     * Transforms a relative path to absolute
     *
     * @param array  $data
     * @param string $filePath
     *
     * @return array
     */
    public function transform(array $data, $filePath)
    {
        $mediaAttributes = $this->attributeRepository->findMediaAttributeCodes();

        foreach ($data as $code => $value) {
            if (!is_string($value)) {
                continue;
            }

            $pos = strpos($code, '-');
            $attributeCode = false !== $pos ? substr($code, 0, $pos) : $code;
            $value = trim($value);

            if (in_array($attributeCode, $mediaAttributes) && !empty($value)) {
                $data[$code] = $filePath . DIRECTORY_SEPARATOR . $value;
            }
        }

        return $data;
    }
}
