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

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConcatenateAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConcatenateActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSource;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSourceCollection;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductTarget;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ProductConcatenateActionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            'from' => [
                ['field' => 'model', 'scope' => 'ecommerce', 'locale' => 'en_US'],
                ['field' => 'color'],
                ['new_line' => null],
                ['text' => 'this is a text'],
            ],
            'to' => ['field' => 'title', 'scope' => 'ecommerce', 'locale' => 'en_US'],
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ProductConcatenateAction::class);
    }

    function it_implements_the_correct_action_interface()
    {
        $this->shouldBeAnInstanceOf(ProductConcatenateActionInterface::class);
    }

    function it_cannot_be_created_with_no_from()
    {
        $this->beConstructedWith([
            'to' => ['field' => 'title', 'scope' => 'ecommerce', 'locale' => 'en_US'],
        ]);

        $this->shouldThrow(new \InvalidArgumentException('Concatenate configuration requires a "from" key.'))
            ->duringInstantiation();
    }

    function it_cannot_be_created_with_no_to()
    {
        $this->beConstructedWith([
            'from' => [
                ['field' => 'model', 'scope' => 'ecommerce', 'locale' => 'en_US'],
                ['field' => 'color'],
            ],
        ]);

        $this->shouldThrow(new \InvalidArgumentException('Concatenate configuration requires a "to" key.'))
            ->duringInstantiation();
    }

    function it_returns_the_source_collection()
    {
        $this->getSourceCollection()->shouldBeAnInstanceOf(ProductSourceCollection::class);
    }

    function it_returns_the_target()
    {
        $this->getTarget()->shouldBeAnInstanceOf(ProductTarget::class);
    }

    function it_returns_the_impacted_fields()
    {
        $this->getImpactedFields()->shouldReturn(['title']);
    }
}
