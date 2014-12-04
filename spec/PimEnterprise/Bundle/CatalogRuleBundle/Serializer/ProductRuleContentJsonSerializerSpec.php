<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Serializer;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use Prophecy\Argument;

class ProductRuleContentJsonSerializerSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            '\PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCondition',
            '\PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueAction',
            '\PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueAction'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleContentJsonSerializer');
    }

    function it_is_a_product_rule_content_serializer()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleContentSerializerInterface');
    }

    function it_serializes_a_product_rule_content(
        RuleInterface $rule,
        ProductConditionInterface $condition1,
        ProductConditionInterface $condition2,
        ProductSetValueActionInterface $setAction,
        ProductCopyValueActionInterface $copyAction
    ) {
        $condition1->getField()->willReturn('sku');
        $condition1->getOperator()->willReturn('LIKE');
        $condition1->getValue()->willReturn('foo');
        $condition1->getLocale()->willReturn(null);
        $condition1->getScope()->willReturn(null);

        $condition2->getField()->willReturn('clothing_size');
        $condition2->getOperator()->willReturn('NOT LIKE');
        $condition2->getValue()->willReturn('XL');
        $condition2->getLocale()->willReturn('fr_FR');
        $condition2->getScope()->willReturn('ecommerce');

        $setAction->getField()->willReturn('name');
        $setAction->getValue()->willReturn('awesome-jacket');
        $setAction->getScope()->willReturn('tablet');
        $setAction->getLocale()->willReturn('en_US');

        $copyAction->getFromField()->willReturn('description');
        $copyAction->getToField()->willReturn('description');
        $copyAction->getFromLocale()->willReturn('fr_FR');
        $copyAction->getToLocale()->willReturn('fr_CH');
        $copyAction->getFromScope()->willReturn(null);
        $copyAction->getToScope()->willReturn(null);

        $conditions = [$condition1, $condition2];
        $actions = [$setAction, $copyAction];

        $rule->getConditions()->willReturn($conditions);
        $rule->getActions()->willReturn($actions);

        $expected = <<<EXPECTED
{"conditions":[{"field":"sku","operator":"LIKE","value":"foo"},{"field":"clothing_size","operator":"NOT LIKE","value":"XL","locale":"fr_FR","scope":"ecommerce"}],"actions":[{"type":"set_value","field":"name","value":"awesome-jacket","locale":"en_US","scope":"tablet"},{"type":"copy_value","from_field":"description","to_field":"description","from_locale":"fr_FR","to_locale":"fr_CH"}]}
EXPECTED;

        $this->serialize($rule)->shouldReturn($expected);
    }

    function it_deserializes_a_product_rule_content()
    {
        $content = <<<CONTENT
{"conditions":[{"field":"sku","operator":"LIKE","value":"foo"},{"field":"clothing_size","operator":"NOT LIKE","value":"XL","locale":"fr_FR","scope":"ecommerce"}],"actions":[{"type":"set_value","field":"name","value":"awesome-jacket","locale":"en_US","scope":"tablet"},{"type":"copy_value","from_field":"description","to_field":"description","from_locale":"fr_FR","to_locale":"fr_CH"}]}
CONTENT;

        // impossible to spec it properly (test the result of the method) as we create objects in this object
        $this->deserialize($content);
    }

    function it_throws_an_exception_when_deserializing_a_product_rule_content_with_no_conditions_key()
    {
        $content = <<<CONTENT
{"actions":[{"type":"set_value","field":"name","value":"awesome-jacket","locale":"en_US","scope":"tablet"},{"type":"copy_value","from_field":"description","to_field":"description","from_locale":"fr_FR","to_locale":"fr_CH"}]}
CONTENT;
        $this->shouldThrow(
            new \LogicException(sprintf('Rule content "%s" should have a "conditions" key.', $content))
        )->during('deserialize', [$content]);
    }

    function it_throws_an_exception_when_deserializing_a_product_rule_content_with_no_actions_key()
    {
        $content = <<<CONTENT
{"conditions":[{"field":"sku","operator":"LIKE","value":"foo"},{"field":"clothing_size","operator":"NOT LIKE","value":"XL","locale":"fr_FR","scope":"ecommerce"}]}
CONTENT;

        $this->shouldThrow(
            new \LogicException(sprintf('Rule content "%s" should have a "actions" key.', $content))
        )->during('deserialize', [$content]);
    }

    function it_throws_an_exception_when_deserializing_a_product_rule_content_with_no_action_type()
    {
        $content = <<<CONTENT
{"conditions":[{"field":"sku","operator":"LIKE","value":"foo"},{"field":"clothing_size","operator":"NOT LIKE","value":"XL","locale":"fr_FR","scope":"ecommerce"}],"actions":[{"field":"name","value":"awesome-jacket","locale":"en_US","scope":"tablet"},{"type":"copy_value","from_field":"description","to_field":"description","from_locale":"fr_FR","to_locale":"fr_CH"}]}
CONTENT;

        $this->shouldThrow(
            new \LogicException(sprintf('Rule content "%s" has an action with no type.', $content))
        )->during('deserialize', [$content]);
    }

    function it_throws_an_exception_when_deserializing_a_product_rule_content_with_an_invalid_action_type()
    {
        $content = <<<CONTENT
{"conditions":[{"field":"sku","operator":"LIKE","value":"foo"},{"field":"clothing_size","operator":"NOT LIKE","value":"XL","locale":"fr_FR","scope":"ecommerce"}],"actions":[{"type":"unknown_action","field":"name","value":"awesome-jacket","locale":"en_US","scope":"tablet"},{"type":"copy_value","from_field":"description","to_field":"description","from_locale":"fr_FR","to_locale":"fr_CH"}]}
CONTENT;

        $this->shouldThrow(
            new \LogicException(sprintf('Rule content "%s" has an unknown type of action "unknown_action".', $content))
        )->during('deserialize', [$content]);
    }

    /**
     * Do not delete it, this method is used to easily build the rule content that is
     * used in those specs.
     * In case we need to modify the specs, it will be useful.
     *
     * @param bool $encode
     *
     * @return string
     */
    private function buildRuleContent($encode = false)
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

        if (true === $encode) {
            $content = json_encode($content);
        }

        return $content;
    }
}
