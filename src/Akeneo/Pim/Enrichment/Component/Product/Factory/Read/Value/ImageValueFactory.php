<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ImageValueFactory implements ReadValueFactory
{
    /** @var FileInfoRepositoryInterface */
    private $fileInfoRepository;

    public function __construct(FileInfoRepositoryInterface $fileInfoRepository)
    {
        $this->fileInfoRepository = $fileInfoRepository;
    }

    public function create(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        if (!\is_string($data)) {
            throw InvalidPropertyTypeException::stringExpected(
                $attribute->code(),
                static::class,
                $data
            );
        }

        $fileInfo = $this->fileInfoRepository->findOneByIdentifier($data);

        if (null === $fileInfo) {
            throw InvalidPropertyException::validEntityCodeExpected(
                $attribute->code(),
                'fileinfo key',
                'The media does not exist',
                static::class,
                $data
            );
        }

        $attributeCode = $attribute->code();

        if ($attribute->isLocalizableAndScopable()) {
            return MediaValue::scopableLocalizableValue($attributeCode, $fileInfo, $channelCode, $localeCode);
        }

        if ($attribute->isScopable()) {
            return MediaValue::scopableValue($attributeCode, $fileInfo, $channelCode);
        }

        if ($attribute->isLocalizable()) {
            return MediaValue::localizableValue($attributeCode, $fileInfo, $localeCode);
        }

        return MediaValue::value($attributeCode, $fileInfo);
    }

    public function supportedAttributeType(): string
    {
        return AttributeTypes::IMAGE;
    }
}
