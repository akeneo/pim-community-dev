<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\ReadValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
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
        $this->shouldBeAnInstanceOf(ReadValueFactory::class);
    }

    public function it_supports_file_attribute_types()
    {
        $this->supportedAttributeType()->shouldReturn(AttributeTypes::FILE);
    }

    public function it_creates_a_localizable_and_scopable_value(FileInfoRepositoryInterface $fileInfoRepository)
    {
        $fileInfo = new FileInfo();
        $fileInfoRepository->findOneByIdentifier('a_file')->willReturn($fileInfo);
        $attribute = $this->getAttribute(true, true);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, 'ecommerce', 'fr_FR', 'a_file');
        $value->isLocalizable()->shouldBe(true);
        $value->isScopable()->shouldBe(true);
        $value->getData()->shouldBeLike($fileInfo);
    }

    public function it_creates_a_localizable_value(FileInfoRepositoryInterface $fileInfoRepository)
    {
        $fileInfo = new FileInfo();
        $fileInfoRepository->findOneByIdentifier('a_file')->willReturn($fileInfo);
        $attribute = $this->getAttribute(true, false);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, null, 'fr_FR', 'a_file');
        $value->isLocalizable()->shouldBe(true);
        $value->isScopable()->shouldBe(false);
        $value->getData()->shouldBeLike($fileInfo);
    }

    public function it_creates_a_scopable_value(FileInfoRepositoryInterface $fileInfoRepository)
    {
        $fileInfo = new FileInfo();
        $fileInfoRepository->findOneByIdentifier('a_file')->willReturn($fileInfo);
        $attribute = $this->getAttribute(false, true);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, 'ecommerce', null, 'a_file');
        $value->isLocalizable()->shouldBe(false);
        $value->isScopable()->shouldBe(true);
        $value->getData()->shouldBeLike($fileInfo);
    }

    public function it_creates_a_non_localizable_and_non_scopable_value(FileInfoRepositoryInterface $fileInfoRepository)
    {
        $fileInfo = new FileInfo();
        $fileInfoRepository->findOneByIdentifier('a_file')->willReturn($fileInfo);
        $attribute = $this->getAttribute(false, false);
        /** @var ScalarValue $value */
        $value = $this->create($attribute, null, null, 'a_file');
        $value->isLocalizable()->shouldBe(false);
        $value->isScopable()->shouldBe(false);
        $value->getData()->shouldBeLike($fileInfo);
    }

    public function it_throws_an_exception_if_the_file_does_not_exist(FileInfoRepositoryInterface $fileInfoRepository)
    {
        $fileInfoRepository->findOneByIdentifier('a_file')->willReturn(null);
        $attribute = $this->getAttribute(false, false);
        $this->shouldThrow(InvalidPropertyException::class)->during('create', [$attribute, null, null, 'a_file']);
    }

    public function it_throws_an_exception_if_provided_data_is_not_a_string()
    {
        $attribute = $this->getAttribute(false, false);
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('create', [$attribute, null, null, ['an_array']]);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
        return new Attribute('an_attribute', AttributeTypes::FILE, [], $isLocalizable, $isScopable, null, false);
    }
}
