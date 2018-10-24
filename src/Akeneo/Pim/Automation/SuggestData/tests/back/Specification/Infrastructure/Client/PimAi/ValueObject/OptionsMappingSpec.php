<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\OptionMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\OptionsMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class OptionsMappingSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith([]);
    }

    public function it_is_an_attribute_options_mapping(): void
    {
        $this->shouldHaveType(OptionsMapping::class);
    }

    public function it_is_traversable(): void
    {
        $this->shouldImplement(\Traversable::class);
    }

    public function it_returns_an_iterator(): void
    {
        $this->beConstructedWith([]);
        $this->getIterator()->shouldReturnAnInstanceOf(\ArrayIterator::class);
    }

    public function it_returns_an_iterator_containing_attribute_options_mapping(): void
    {
        $directory = realpath(__DIR__ . '/../../../../../Resources/fake/franklin-api/attribute-options-mapping/');
        $filepath = sprintf('%s/%s', $directory, 'get_family_router_attribute_color.json');
        $content = json_decode(file_get_contents($filepath), true);
        $this->beConstructedWith($content);

        $this->getIterator()->shouldReturnAnInstanceOf(\ArrayIterator::class);
        foreach ($this->getIterator() as $item) {
            $item->shouldBeanInstanceOf(OptionMapping::class);
        }
    }
}
