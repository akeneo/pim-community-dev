<?php

namespace spec\PimEnterprise\Component\CatalogRule\Denormalizer\ProductRule;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ContentDenormalizerSpec extends ObjectBehavior
{
    function let(DenormalizerInterface $chainedDenormalizer)
    {
        $this->beConstructedWith(
            'Akeneo\Bundle\RuleEngineBundle\Model\Rule',
            'PimEnterprise\Component\CatalogRule\Model\ProductCondition',
            [
                'copy_value' => 'PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueAction',
                'set_value' => 'PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueAction',
            ]
        );

        $this->setChainedDenormalizer($chainedDenormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Component\CatalogRule\Denormalizer\ProductRule\ContentDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
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
            'PimEnterprise\Component\CatalogRule\Model\ProductCondition',
            Argument::cetera()
        )->shouldBeCalled();

        $chainedDenormalizer->denormalize(
            ['field' => 'clothing_size', 'operator' => 'NOT LIKE', 'value' => 'XL', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
            'PimEnterprise\Component\CatalogRule\Model\ProductCondition',
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
        $this->denormalize($content, 'Akeneo\Bundle\RuleEngineBundle\Model\Rule');
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
        )->during('denormalize', [$content, 'Akeneo\Bundle\RuleEngineBundle\Model\Rule']);
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
        )->during('denormalize', [$content, 'Akeneo\Bundle\RuleEngineBundle\Model\Rule']);
    }
}
