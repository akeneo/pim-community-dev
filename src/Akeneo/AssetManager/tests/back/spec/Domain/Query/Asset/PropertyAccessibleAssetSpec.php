<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Query\Asset;

use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;
use PhpSpec\ObjectBehavior;

class PropertyAccessibleAssetSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith('iphone', ['description-ecommerce-en_US' => 'The iPhone']);
        $this->shouldHaveType(PropertyAccessibleAsset::class);
    }

    public function it_has_value()
    {
        $this->beConstructedWith('iphone', ['description-ecommerce-en_US' => 'The iPhone']);
        $this->hasValue('description-ecommerce-en_US')->shouldReturn(true);
        $this->hasValue('description-ecommerce')->shouldReturn(false);
    }

    public function it_gets_value()
    {
        $this->beConstructedWith('iphone', [
            'name-ecommerce-en_US' => 'iPhone',
            'tags-ecommerce-en_US' => ['tech', 'phone', 'apple'],
        ]);
        $this->getValue('name-ecommerce-en_US')->shouldReturn('iPhone');
        $this->getValue('tags-ecommerce-en_US')->shouldReturn(['tech', 'phone', 'apple']);
    }

    public function it_has_a_code()
    {
        $this->beConstructedWith('iphone', ['description-ecommerce-en_US' => 'The iPhone']);
        $this->hasValue('code')->shouldReturn(true);
    }

    public function it_gets_a_code()
    {
        $this->beConstructedWith('iphone', [
            'name-ecommerce-en_US' => 'iPhone',
            'tags-ecommerce-en_US' => ['tech', 'phone', 'apple'],
        ]);
        $this->getValue('code')->shouldReturn('iphone');
    }
}
