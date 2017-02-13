<?php

namespace Pim\Component\Catalog\Factory\ProductValue;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Factory that creates media product values.
 *
 * @internal  Please, do not use this class directly. You must use \Pim\Component\Catalog\Factory\ProductValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MediaProductValueFactory implements ProductValueFactoryInterface
{
    /** @var FileInfoRepositoryInterface */
    protected $fileInfoRepository;

    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /**
     * @param FileInfoRepositoryInterface $fileInfoRepository
     * @param string                      $productValueClass
     * @param string                      $supportedAttributeType
     */
    public function __construct(
        FileInfoRepositoryInterface $fileInfoRepository,
        $productValueClass,
        $supportedAttributeType
    ) {
        $this->fileInfoRepository = $fileInfoRepository;
        $this->productValueClass = $productValueClass;
        $this->supportedAttributeType = $supportedAttributeType;
    }

    /**
     * {@inheritdoc}
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data)
    {
        $this->checkData($attribute, $data);

        if (null !== $data) {
            $data = $this->getFileInfo($attribute, $data);
        }

        $value = new $this->productValueClass($attribute, $channelCode, $localeCode, $data);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return $attributeType === $this->supportedAttributeType;
    }

    /**
     * Checks that data is a valid file path.
     *
     * @param AttributeInterface $attribute
     * @param string             $data
     *
     * @throws InvalidPropertyException
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data) {
            return;
        }

        if (!is_string($data)) {
            throw InvalidPropertyTypeException::stringExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $data
     *
     * @throws InvalidPropertyException
     * @return FileInfoInterface
     */
    protected function getFileInfo(AttributeInterface $attribute, $data)
    {
        $file = $this->fileInfoRepository->findOneByIdentifier($data);

        if (null === $file) {
            throw InvalidPropertyException::validEntityCodeExpected(
                $attribute->getCode(),
                'fileinfo key',
                'The media does not exist',
                static::class,
                $data
            );
        }

        return $file;
    }
}
