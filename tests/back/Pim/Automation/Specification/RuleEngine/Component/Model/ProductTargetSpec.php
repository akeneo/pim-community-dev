<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Model;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductTarget;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ProductTargetSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromNormalized', [[
            'field' => 'model',
            'scope' => 'ecommerce',
            'locale' => 'en_US',
        ]]);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ProductTarget::class);
    }

    function it_returns_the_field()
    {
        $this->getField()->shouldBe('model');
    }

    function it_returns_the_scope()
    {
        $this->getScope()->shouldBe('ecommerce');
    }

    function it_returns_the_locale()
    {
        $this->getLocale()->shouldBe('en_US');
    }

    function it_cannot_be_created_without_field()
    {
        $this->beConstructedThrough('fromNormalized', [['locale' => 'en_US']]);

        $this->shouldThrow(new \LogicException('Target configuration requires a "field" key.'))
            ->duringInstantiation();
    }

    function it_can_be_created_without_scope_and_locale()
    {
        $this->beConstructedThrough('fromNormalized', [['field' => 'title']]);

        $this->getField()->shouldBe('title');
        $this->getScope()->shouldBe(null);
        $this->getLocale()->shouldBe(null);
    }
}
