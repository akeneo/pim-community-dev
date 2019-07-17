<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action\ItemCollection;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ItemCollectionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('createFromNormalized', [['{{code}}']]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ItemCollection::class);
    }

    public function it_cannot_create_an_empty_string()
    {
        $this->beConstructedThrough('createFromNormalized', [['']]);
        $this->shouldThrow(new \InvalidArgumentException('All the item values should be a string not empty'))->duringInstantiation();
    }

    public function it_can_normalize_itself()
    {
        $this->normalize()->shouldReturn(['{{code}}']);
    }
}
