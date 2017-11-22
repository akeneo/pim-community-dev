<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Factory\Value;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;

/**
 * Factory that creates media product values.
 *
 * @internal  Please, do not use this class directly. You must use \Pim\Component\Catalog\Factory\ValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MediaValueFactory implements ValueFactoryInterface
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
    public function create(AttributeInterface $attribute, $channelCode, $localeCode, $data): ValueInterface
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
    public function supports($attributeType): bool
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
    protected function checkData(AttributeInterface $attribute, $data): void
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
     * @return FileInfoInterface|null
     */
    protected function getFileInfo(AttributeInterface $attribute, $data): ?FileInfoInterface
    {
        return $this->fileInfoRepository->findOneByIdentifier($data);
    }
}
