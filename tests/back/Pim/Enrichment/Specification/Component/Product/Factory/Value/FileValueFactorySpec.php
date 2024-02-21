<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\Repository\FileInfoRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FileValueFactorySpec extends ObjectBehavior
{
    public function let(FileInfoRepositoryInterface $fileInfoRepository)
    {
        $this->beConstructedWith($fileInfoRepository);
    }

    public function it_is_a_read_value_factory()
    {
        $this->shouldBeAnInstanceOf(ValueFactory::class);
    }

    public function it_supports_file_attribute_types()
    {
        $this->supportedAttributeType()->shouldReturn(AttributeTypes::FILE);
    }

    public function it_does_not_support_null(FileInfoRepositoryInterface $fileInfoRepository)
    {
        $fileInfoRepository->findOneByIdentifier('foo')->willReturn(null);
        $this->shouldThrow(InvalidPropertyException::class)->during('createByCheckingData', [
            $this->getAttribute(true, true),
            'ecommerce',
            'fr_FR',
            'foo'
        ]);
    }

    public function it_creates_a_localizable_and_scopable_value(FileInfoRepositoryInterface $fileInfoRepository)
    {
        $fileInfo = new FileInfo();
        $fileInfoRepository->findOneByIdentifier('a_file')->willReturn($fileInfo);
        $attribute = $this->getAttribute(true, true);
        $value = $this->createByCheckingData($attribute, 'ecommerce', 'fr_FR', 'a_file');
        $value->shouldBeLike(MediaValue::scopableLocalizableValue('an_attribute', $fileInfo, 'ecommerce', 'fr_FR'));
    }

    public function it_creates_a_localizable_value(FileInfoRepositoryInterface $fileInfoRepository)
    {
        $fileInfo = new FileInfo();
        $fileInfoRepository->findOneByIdentifier('a_file')->willReturn($fileInfo);
        $attribute = $this->getAttribute(true, false);
        $value = $this->createByCheckingData($attribute, null, 'fr_FR', 'a_file');
        $value->shouldBeLike(MediaValue::localizableValue('an_attribute', $fileInfo, 'fr_FR'));

    }

    public function it_creates_a_scopable_value(FileInfoRepositoryInterface $fileInfoRepository)
    {
        $fileInfo = new FileInfo();
        $fileInfoRepository->findOneByIdentifier('a_file')->willReturn($fileInfo);
        $attribute = $this->getAttribute(false, true);
        $value = $this->createByCheckingData($attribute, 'ecommerce', null, 'a_file');
        $value->shouldBeLike(MediaValue::scopableValue('an_attribute', $fileInfo, 'ecommerce'));
    }

    public function it_creates_a_non_localizable_and_non_scopable_value(FileInfoRepositoryInterface $fileInfoRepository)
    {
        $fileInfo = new FileInfo();
        $fileInfoRepository->findOneByIdentifier('a_file')->willReturn($fileInfo);
        $attribute = $this->getAttribute(false, false);
        $value = $this->createByCheckingData($attribute, null, null, 'a_file');
        $value->shouldBeLike(MediaValue::value('an_attribute', $fileInfo));
    }


    public function it_creates_a_value_without_checking_data(FileInfoRepositoryInterface $fileInfoRepository)
    {
        $fileInfo = new FileInfo();
        $fileInfoRepository->findOneByIdentifier('a_file')->willReturn($fileInfo);
        $attribute = $this->getAttribute(false, false);
        $value = $this->createWithoutCheckingData($attribute, null, null, 'a_file');
        $value->shouldBeLike(MediaValue::value('an_attribute', $fileInfo));
    }

    public function it_throws_an_exception_if_provided_data_is_not_a_string()
    {
        $attribute = $this->getAttribute(false, false);
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('createByCheckingData', [$attribute, null, null, ['an_array']]);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
        return new Attribute('an_attribute', AttributeTypes::FILE, [], $isLocalizable, $isScopable, null, null, false, 'file', []);
    }
}
