<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RuleTemplateSpec extends ObjectBehavior
{
    public function let()
    {
        $content = [
            'conditions' => [
                [
                    'field' => 'sku',
                    'operator' => 'EQUALS',
                    'value' => '{{product_sku}}',
                ]
            ],
            'actions' => [
                [
                    'type' => 'add',
                    'field' => '{{target_attribute}}',
                    'value' => '{{ code }}'
                ]
            ]
        ];
        $this->beConstructedThrough('createFromNormalized', [$content]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RuleTemplate::class);
    }

    public function it_should_contain_conditions()
    {
        $content = [
            'actions' => [
                [
                    'type' => 'add',
                    'field' => '{{target_attribute}}',
                    'value' => '{{ code }}'
                ]
            ]
        ];
        $this->beConstructedThrough('createFromNormalized', $content);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_should_contain_actions()
    {
        $content = [
            'conditions' => [
                [
                    'field' => 'sku',
                    'operator' => 'EQUALS',
                    'value' => '{{product_sku}}',
                ]
            ]
        ];
        $this->beConstructedThrough('createFromNormalized', $content);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }


    public function it_can_get_the_conditions()
    {
        $conditions = [
            [
                'field' => 'sku',
                'operator' => 'EQUALS',
                'value' => '{{product_sku}}',
            ]
        ];
        $this->getConditions()->shouldBeLike($conditions);
    }

    public function it_can_get_the_actions()
    {
        $actions = [
            [
                'type' => 'add',
                'field' => '{{target_attribute}}',
                'value' => '{{ code }}'
            ]
        ];
        $this->getActions()->shouldBeLike($actions);
    }
}
