<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FileValueFactory implements ValueFactory
{
    /** @var FileInfoRepositoryInterface */
    private $fileInfoRepository;

    public function __construct(FileInfoRepositoryInterface $fileInfoRepository)
    {
        $this->fileInfoRepository = $fileInfoRepository;
    }

    public function createWithoutCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data): ValueInterface
    {
        $file = $data instanceof FileInfoInterface ? $data : $this->getFile($data);

        $attributeCode = $attribute->code();

        if ($attribute->isLocalizableAndScopable()) {
            return MediaValue::scopableLocalizableValue($attributeCode, $file, $channelCode, $localeCode);
        }

        if ($attribute->isScopable()) {
            return MediaValue::scopableValue($attributeCode, $file, $channelCode);
        }

        if ($attribute->isLocalizable()) {
            return MediaValue::localizableValue($attributeCode, $file, $localeCode);
        }

        return MediaValue::value($attributeCode, $file);
    }

    public function createByCheckingData(Attribute $attribute, ?string $channelCode, ?string $localeCode, $data) : ValueInterface
    {
        $fileInfo = $this->fileInfoRepository->findOneByIdentifier($data);

        if ($fileInfo === null) {
            throw InvalidPropertyException::validEntityCodeExpected(
                $attribute->code(),
                'fileinfo key',
                'The media does not exist',
                static::class,
                $data
            );
        }

        return $this->createWithoutCheckingData($attribute, $channelCode, $localeCode, $data);
    }

    public function supportedAttributeType(): string
    {
        return AttributeTypes::FILE;
    }

    private function getFile(?string $key): ?FileInfoInterface
    {
        return $this->fileInfoRepository->findOneByIdentifier($key);
    }
}
