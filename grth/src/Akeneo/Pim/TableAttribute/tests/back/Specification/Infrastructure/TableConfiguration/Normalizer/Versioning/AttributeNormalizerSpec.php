<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Normalizer\Versioning;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Normalizer\Versioning\AttributeNormalizer;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

class AttributeNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $baseNormalizer,
        SelectOptionCollectionRepository $selectOptionCollectionRepository
    ) {
        $baseNormalizer->supportsNormalization(Argument::type(AttributeInterface::class), 'flat')->willReturn(true);
        $baseNormalizer->supportsNormalization(Argument::cetera())->willReturn(false);
        $this->beConstructedWith($baseNormalizer, $selectOptionCollectionRepository);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldHaveType(AttributeNormalizer::class);
    }

    function it_only_normalizes_attributes_in_flat_format(AttributeInterface $attribute)
    {
        $this->supportsNormalization(new \stdClass(), 'flat')->shouldBe(false);
        $this->supportsNormalization($attribute, 'indexing')->shouldBe(false);
        $this->supportsNormalization($attribute, 'flat')->shouldBe(true);
    }

    function it_normalizes_a_non_table_attribute_to_flat_format(
        NormalizerInterface $baseNormalizer,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn('pim_catalog_text');
        $baseNormalizer->normalize($attribute, 'flat', [])->shouldBeCalled()->willReturn(['code' => 'foo']);
        $this->normalize($attribute, 'flat', [])->shouldReturn(['code' => 'foo']);
    }

    function it_normalizes_a_table_attribute_to_flat_format(
        NormalizerInterface $baseNormalizer,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn('pim_catalog_table');
        $attribute->getRawTableConfiguration()->willReturn(
            [
                [
                    'id' => ColumnIdGenerator::ingredient(),
                    'data_type' => 'select',
                    'code' => 'ingredient',
                    'labels' => ['en_US' => 'Ingredient'],
                    'validations' => (object)[],
                    'options' => [['code' => 'salt'],['code' => 'sugar']],
                ],
                [
                    'id' => ColumnIdGenerator::quantity(),
                    'data_type' => 'number',
                    'code' => 'quantity',
                    'labels' => (object)[],
                    'validations' => ['decimals_allowed' => true]
                ],
            ]
        );
        $baseNormalizer->normalize($attribute, 'flat', [])->shouldBeCalled()->willReturn(['code' => 'nutrition']);
        $this->normalize($attribute, 'flat', [])->shouldReturn(
            [
                'code' => 'nutrition',
                'table_configuration' => '[{"data_type":"select","code":"ingredient","labels":{"en_US":"Ingredient"},"validations":{},"options":[{"code":"salt"},{"code":"sugar"}]},{"data_type":"number","code":"quantity","labels":{},"validations":{"decimals_allowed":true}}]',
            ]
        );
    }

    function it_fetches_select_options_if_they_are_not_in_the_raw_table_configuration(
        NormalizerInterface $baseNormalizer,
        SelectOptionCollectionRepository $selectOptionCollectionRepository,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn('pim_catalog_table');
        $attribute->getCode()->willReturn('nutrition');
        $attribute->getRawTableConfiguration()->willReturn(
            [
                [
                    'id' => ColumnIdGenerator::ingredient(),
                    'data_type' => 'select',
                    'code' => 'ingredient',
                    'labels' => ['en_US' => 'Ingredient'],
                    'validations' => (object)[],
                ],
                [
                    'id' => ColumnIdGenerator::quantity(),
                    'data_type' => 'number',
                    'code' => 'quantity',
                    'labels' => (object)[],
                    'validations' => ['decimals_allowed' => true]
                ],
            ]
        );

        $selectOptionCollectionRepository->getByColumn('nutrition', ColumnCode::fromString('ingredient'))
        ->shouldBeCalled()->willReturn(SelectOptionCollection::fromNormalized(
           [
               ['code' => 'salt'],
               ['code' => 'sugar'],
           ]
        ));

        $baseNormalizer->normalize($attribute, 'flat', [])->shouldBeCalled()->willReturn(['code' => 'nutrition']);
        $this->normalize($attribute, 'flat', [])->shouldReturn(
            [
                'code' => 'nutrition',
                'table_configuration' => '[{"data_type":"select","code":"ingredient","labels":{"en_US":"Ingredient"},"validations":{},"options":[{"code":"salt"},{"code":"sugar"}]},{"data_type":"number","code":"quantity","labels":{},"validations":{"decimals_allowed":true}}]',
            ]
        );
    }

    function it_does_not_normalize_select_option_labels(
        NormalizerInterface $baseNormalizer,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn('pim_catalog_table');
        $attribute->getRawTableConfiguration()->willReturn(
            [
                [
                    'id' => ColumnIdGenerator::ingredient(),
                    'data_type' => 'select',
                    'code' => 'ingredient',
                    'labels' => (object)[],
                    'validations' => (object)[],
                    'options' => [['code' => 'sugar', 'labels' => ['en_US' => 'Sugar']]],
                ],
                [
                    'id' => ColumnIdGenerator::quantity(),
                    'data_type' => 'number',
                    'code' => 'quantity',
                    'labels' => (object)[],
                    'validations' => (object)[]
                ],
            ]
        );
        $baseNormalizer->normalize($attribute, 'flat', [])->shouldBeCalled()->willReturn(['code' => 'nutrition']);
        $this->normalize($attribute, 'flat', [])->shouldReturn(
            [
                'code' => 'nutrition',
                'table_configuration' => '[{"data_type":"select","code":"ingredient","labels":{},"validations":{},"options":[{"code":"sugar"}]},{"data_type":"number","code":"quantity","labels":{},"validations":{}}]',
            ]
        );
    }

    function it_only_normalizes_the_10000_first_options(
        NormalizerInterface $baseNormalizer,
        AttributeInterface $attribute
    ) {
        $attribute->getType()->willReturn('pim_catalog_table');

        $options = \array_map(
            fn (int $i): array => ['code' => \sprintf('option_%d',  $i)],
            \range(0, 20000)
        );
        $attribute->getRawTableConfiguration()->willReturn(
            [
                [
                    'id' => ColumnIdGenerator::ingredient(),
                    'data_type' => 'select',
                    'code' => 'ingredient',
                    'labels' => (object)[],
                    'validations' => (object)[],
                    'options' => $options,
                ],
                [
                    'id' => ColumnIdGenerator::quantity(),
                    'data_type' => 'number',
                    'code' => 'quantity',
                    'labels' => (object)[],
                    'validations' => (object)[]
                ],
            ]
        );
        $baseNormalizer->normalize($attribute, 'flat', [])->shouldBeCalled()->willReturn(['code' => 'nutrition']);

        $normalized = $this->normalize($attribute, 'flat', []);
        $normalized->shouldBeArray();
        $normalized->shouldHaveKey('table_configuration');

        $normalizedOptions = \json_decode($normalized['table_configuration']->getWrappedObject(), true)[0]['options'];
        Assert::isArray($normalizedOptions);
        Assert::count($normalizedOptions, 10000);
    }
}
