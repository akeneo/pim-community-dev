<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Factory that creates media product values.
 *
 * @internal  Please, do not use this class directly. You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MediaValueFactory extends AbstractValueFactory
{
    /** @var FileInfoRepositoryInterface */
    protected $fileInfoRepository;

    public function __construct(
        FileInfoRepositoryInterface $fileInfoRepository,
        string $productValueClass,
        string $supportedAttributeType
    ) {
        parent::__construct($productValueClass, $supportedAttributeType);

        $this->fileInfoRepository = $fileInfoRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareData(AttributeInterface $attribute, $data, bool $ignoreUnknownData)
    {
        if (null === $data) {
            return;
        }

        if (!\is_string($data)) {
            throw InvalidPropertyTypeException::stringExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        $fileInfo = $this->fileInfoRepository->findOneByIdentifier($data);

        if (null === $fileInfo) {
            throw InvalidPropertyException::validEntityCodeExpected(
                $attribute->getCode(),
                'fileinfo key',
                'The media does not exist',
                static::class,
                $data
            );
        }

        return $fileInfo;
    }
}
