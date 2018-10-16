<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Denormalizer\ProductRule;

use Akeneo\Pim\Automation\RuleEngine\Component\Denormalizer\ProductRule\ContentDenormalizer;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCondition;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCopyAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSetAction;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ContentDenormalizerSpec extends ObjectBehavior
{
    function let(DenormalizerInterface $chainedDenormalizer)
    {
        $this->beConstructedWith(
            Rule::class,
           ProductCondition::class,
            [
                'copy_value' => ProductCopyAction::class,
                'set_value' => ProductSetAction::class,
            ]
        );

        $this->setChainedDenormalizer($chainedDenormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ContentDenormalizer::class);
    }

    function it_is_a_denormalizer()
    {
        $this->shouldHaveType(DenormalizerInterface::class);
    }

    function it_denormalizes_a_product_rule_content($chainedDenormalizer)
    {
        $content = [
            'conditions' => [
                ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                ['field' => 'clothing_size', 'operator' => 'NOT LIKE', 'value' => 'XL', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
            ],
            'actions' => [
                ['type' => 'set_value', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                ['type' => 'copy_value', 'fromField' => 'description', 'toField' => 'description', 'fromLocale' => 'fr_FR', 'toLocale' => 'fr_CH']
            ]
        ];

        $chainedDenormalizer->denormalize(
            ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
           ProductCondition::class,
            Argument::cetera()
        )->shouldBeCalled();

        $chainedDenormalizer->denormalize(
            ['field' => 'clothing_size', 'operator' => 'NOT LIKE', 'value' => 'XL', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
           ProductCondition::class,
            Argument::cetera()
        )->shouldBeCalled();

        $chainedDenormalizer->denormalize(
            ['type' => 'set_value', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
            'set_value',
            Argument::cetera()
        )->shouldBeCalled();

        $chainedDenormalizer->denormalize(
            ['type' => 'copy_value', 'fromField' => 'description', 'toField' => 'description', 'fromLocale' => 'fr_FR', 'toLocale' => 'fr_CH'],
            'copy_value',
            Argument::cetera()
        )->shouldBeCalled();

        // TODO: use a custom matcher to test it
        $this->denormalize($content, Rule::class);
    }

    function it_throws_an_exception_when_deserializing_a_product_rule_content_with_no_action_type()
    {
        $content = [
            'conditions' => [
                ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                ['field' => 'clothing_size', 'operator' => 'NOT LIKE', 'value' => 'XL', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
            ],
            'actions' => [
                ['type' => 'set_value', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                ['fromField' => 'description', 'toField' => 'description', 'fromLocale' => 'fr_FR', 'toLocale' => 'fr_CH']
            ]
        ];

        $this->shouldThrow(
            new \LogicException(sprintf('Rule content "%s" has an action with no type.', json_encode($content)))
        )->during('denormalize', [$content, Rule::class]);
    }

    function it_throws_an_exception_when_deserializing_a_product_rule_content_with_an_invalid_action_type($chainedDenormalizer)
    {
        $content = [
            'conditions' => [
                ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                ['field' => 'clothing_size', 'operator' => 'NOT LIKE', 'value' => 'XL', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
            ],
            'actions' => [
                ['type' => 'unknown_action', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                ['type' => 'copy_value', 'fromField' => 'description', 'toField' => 'description', 'fromLocale' => 'fr_FR', 'toLocale' => 'fr_CH']
            ]
        ];

        $chainedDenormalizer->denormalize($content['conditions'][0], Argument::cetera())->shouldBeCalled();
        $chainedDenormalizer->denormalize($content['conditions'][1], Argument::cetera())->shouldBeCalled();
        $chainedDenormalizer->denormalize($content['actions'][0], Argument::cetera())->willThrow(new \LogicException());

        $this->shouldThrow(
            new \LogicException(sprintf('Rule content "%s" has an unknown type of action "unknown_action".', json_encode($content)))
        )->during('denormalize', [$content, Rule::class]);
    }
}
