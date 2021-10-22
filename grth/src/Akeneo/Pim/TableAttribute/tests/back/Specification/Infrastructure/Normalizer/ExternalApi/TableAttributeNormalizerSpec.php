<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\ExternalApi;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\ExternalApi\TableAttributeNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TableAttributeNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        SelectOptionCollectionRepository $optionCollectionRepository
    ) {
        $this->beConstructedWith($normalizer, $optionCollectionRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableAttributeNormalizer::class);
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_supports_only_attributes_and_external_api_format()
    {
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization(new Attribute(), 'standard')->shouldReturn(false);
        $this->supportsNormalization(new Attribute(), 'external_api')->shouldReturn(true);
    }

    function it_does_nothing_when_attribute_is_not_a_table(NormalizerInterface $normalizer)
    {
        $attribute = new Attribute();
        $attribute->setType(AttributeTypes::TEXT);

        $normalizer->normalize($attribute, 'format', ['option'])->willReturn(['a_result']);

        $this->normalize($attribute, 'format', ['option'])->shouldReturn(['a_result']);
    }

    function it_normalizes_a_table_attribute_with_table_select_options(
        NormalizerInterface $normalizer,
        SelectOptionCollectionRepository $optionCollectionRepository
    ) {
        $attribute = new Attribute();
        $attribute->setCode('nutrition');
        $attribute->setType(AttributeTypes::TABLE);
        $attribute->setRawTableConfiguration([
            ['code' => 'ingredients', 'data_type' => 'select', 'labels' => (object) [], 'validations' => (object) [], 'options' => [
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt']],
                ['code' => 'pepper'],
            ]],
            ['code' => 'quantity', 'data_type' => 'text', 'labels' => (object) [], 'validations' => (object) []],
        ]);

        $normalizer->normalize($attribute, 'format', ['with_table_select_options' => true])->willReturn([
            'key' => 'a_result',
            'table_configuration' => [
                ['code' => 'ingredients', 'data_type' => 'select', 'labels' => (object) [], 'validations' => (object) []],
                ['code' => 'quantity', 'data_type' => 'text', 'labels' => (object) [], 'validations' => (object) []],
            ],
        ]);

        $optionCollectionRepository->getByColumn('nutrition', ColumnCode::fromString('ingredients'))
            ->shouldBeCalledOnce()
            ->willReturn(SelectOptionCollection::fromNormalized([
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt']],
                ['code' => 'pepper'],
            ]));

        $this->normalize($attribute, 'format', ['with_table_select_options' => true])->shouldBeLike([
            'key' => 'a_result',
            'table_configuration' => [
                ['code' => 'ingredients', 'data_type' => 'select', 'labels' => (object) [], 'validations' => (object) [], 'options' => [
                    ['code' => 'salt', 'labels' => ['en_US' => 'Salt']],
                    ['code' => 'pepper', 'labels' => (object) []],
                ]],
                ['code' => 'quantity', 'data_type' => 'text', 'labels' => (object) [], 'validations' => (object) []],
            ],
        ]);
    }

    function it_normalizes_a_table_attribute_without_table_select_options(
        NormalizerInterface $normalizer,
        SelectOptionCollectionRepository $optionCollectionRepository
    ) {
        $attribute = new Attribute();
        $attribute->setCode('nutrition');
        $attribute->setType(AttributeTypes::TABLE);
        $attribute->setRawTableConfiguration([
            ['code' => 'ingredients', 'data_type' => 'select', 'labels' => (object) [], 'validations' => (object) [], 'options' => [
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt']],
                ['code' => 'pepper'],
            ]],
            ['code' => 'quantity', 'data_type' => 'text', 'labels' => (object) [], 'validations' => (object) []],
        ]);

        $normalizer->normalize($attribute, 'format', ['option'])->willReturn([
            'key' => 'a_result',
            'table_configuration' => [
                ['code' => 'ingredients', 'data_type' => 'select', 'labels' => (object) [], 'validations' => (object) []],
                ['code' => 'quantity', 'data_type' => 'text', 'labels' => (object) [], 'validations' => (object) []],
            ],
        ]);

        $optionCollectionRepository->getByColumn(Argument::cetera())->shouldNotBeCalled();

        $this->normalize($attribute, 'format', ['option'])->shouldBeLike([
            'key' => 'a_result',
            'table_configuration' => [
                ['code' => 'ingredients', 'data_type' => 'select', 'labels' => (object) [], 'validations' => (object) []],
                ['code' => 'quantity', 'data_type' => 'text', 'labels' => (object) [], 'validations' => (object) []],
            ],
        ]);
    }
}
