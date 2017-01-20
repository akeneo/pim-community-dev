<?php

namespace Pim\Component\Catalog\Factory\ProductValue;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
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
    protected $fileInfoRepository;

    /** @var string */
    protected $mediaProductValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /**
     * @param FileInfoRepositoryInterface $fileInfoRepository
     * @param string                      $mediaProductValueClass
     * @param string                      $supportedAttributeType
     */
    public function __construct(
        FileInfoRepositoryInterface $fileInfoRepository,
        $mediaProductValueClass,
        $supportedAttributeType
    ) {
        if (!class_exists($mediaProductValueClass)) {
            throw new \InvalidArgumentException(
                sprintf('The product value class "%s" does not exist.', $mediaProductValueClass)
            );
        }

        $this->fileInfoRepository = $fileInfoRepository;
        $this->mediaProductValueClass = $mediaProductValueClass;
        $this->supportedAttributeType = $supportedAttributeType;
    }

    /**
     * @inheritdoc
     */
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data)
    {
        $this->checkData($attribute, $data);

        $value = new $this->mediaProductValueClass();
        $value->setAttribute($attribute);
        $value->setScope($channelCode);
        $value->setLocale($localeCode);

        if (null !== $data) {
            $value->setMedia($this->getFileInfo($attribute, $data));
        }

        return $value;
    }

    /**
     * @inheritdoc
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
     * @throws InvalidArgumentException
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data) {
            return;
        }

        if (!is_string($data)) {
            throw InvalidArgumentException::stringExpected(
                $attribute->getCode(),
                'media',
                'factory',
                gettype($data)
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
                'media',
                'factory',
                $data
            );
        }

        return $file;
    }
}
