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

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductClearAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductClearActionInterface;
use PhpSpec\ObjectBehavior;

class ProductClearActionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            'field' => 'name',
            'locale' => 'en_US',
            'scope' => 'ecommerce',
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ProductClearAction::class);
    }

    function it_implements_product_clear_action_interface()
    {
        $this->shouldBeAnInstanceOf(ProductClearActionInterface::class);
    }

    function it_can_be_initialized_without_locale()
    {
        $this->beConstructedWith(['field' => 'name', 'scope' => 'ecommerce']);
        $this->getLocale()->shouldBe(null);
    }

    function it_can_be_initialized_without_scope()
    {
        $this->beConstructedWith(['field' => 'name', 'locale' => 'en_US']);
        $this->getScope()->shouldBe(null);
    }

    function it_can_be_initialized_without_locale_and_scope()
    {
        $this->beConstructedWith(['field' => 'name']);
        $this->getLocale()->shouldBe(null);
        $this->getScope()->shouldBe(null);
    }

    function it_returns_the_field()
    {
        $this->getField()->shouldBe('name');
    }

    function it_returns_the_locale()
    {
        $this->getLocale()->shouldBe('en_US');
    }
    function it_returns_the_scope()
    {
        $this->getScope()->shouldBe('ecommerce');
    }
}
