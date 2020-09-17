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

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSource;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductSourceCollection;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ProductSourceCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromNormalized', [[
            ['field' => 'model'],
            ['field' => 'title'],
            ['text' => 'a text'],
            ['new_line' => null],
        ]]);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ProductSourceCollection::class);
    }

    function it_is_an_iterator()
    {
        $this->shouldBeAnInstanceOf(\IteratorAggregate::class);
    }

    function it_returns_the_product_sources()
    {
        $productSources = $this->getIterator();
        $productSources->shouldHaveCount(4);
        $productSources[0]->shouldBeAnInstanceOf(ProductSource::class);
        $productSources[1]->shouldBeAnInstanceOf(ProductSource::class);
        $productSources[2]->shouldBeAnInstanceOf(ProductSource::class);
        $productSources[3]->shouldBeAnInstanceOf(ProductSource::class);
    }

    function it_cannot_be_created_with_less_than_two_sources()
    {
        $this->shouldThrow(new \LogicException('At least two sources must be defined.'))
            ->during('fromNormalized', [[
                ['field' => 'model'],
            ]]);
    }

    function it_cannot_be_created_with_empty_data()
    {
        $this->shouldThrow(new \LogicException('At least two sources must be defined.'))
            ->during('fromNormalized', [[]]);
    }
}
