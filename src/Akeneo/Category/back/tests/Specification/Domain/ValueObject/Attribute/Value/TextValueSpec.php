<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Domain\ValueObject\Attribute\Value;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\Value;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\ValueChannel;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\ValueLocale;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TextValueSpec extends ObjectBehavior
{
    public function it_normalizes(): void
    {
        $this->beConstructedWith(
            'Meta shoes',
            AttributeUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
            new AttributeCode('seo_meta_description'),
            new ValueLocale('en_US'),
            new ValueChannel('ecommerce')
        );

        $this->shouldHaveType(TextValue::class);
        $this->shouldHaveType(AbstractValue::class);
        $this->shouldImplement(Value::class);

        $key = 'seo_meta_description'.AbstractValue::SEPARATOR.'02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $expectedValue = [
            'data' => 'Meta shoes',
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'attribute_code' => $key
        ];

        $this->normalize()->shouldBeLike($expectedValue);
    }

    public function it_normalizes_with_no_locale(): void
    {
        $textValue = 'Meta shoes';
        $this->beConstructedWith(
            $textValue,
            AttributeUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
            new AttributeCode('seo_meta_description'),
            null,
            new ValueChannel('ecommerce')
        );

        $this->shouldHaveType(TextValue::class);
        $this->shouldHaveType(AbstractValue::class);
        $this->shouldImplement(Value::class);

        $key = 'seo_meta_description'.AbstractValue::SEPARATOR.'02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $expectedValue = [
            'data' => 'Meta shoes',
            'channel' => 'ecommerce',
            'locale' => null,
            'attribute_code' => $key
        ];

        $this->normalize()->shouldBeLike($expectedValue);
    }

    public function it_normalizes_with_no_channel(): void
    {
        $textValue = 'Meta shoes';
        $this->beConstructedWith(
            $textValue,
            AttributeUuid::fromString('02274dac-e99a-4e1d-8f9b-794d4c3ba330'),
            new AttributeCode('seo_meta_description'),
            new ValueLocale('en_US'),
            null
        );

        $this->shouldHaveType(TextValue::class);
        $this->shouldHaveType(AbstractValue::class);
        $this->shouldImplement(Value::class);

        $key = 'seo_meta_description'.AbstractValue::SEPARATOR.'02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $expectedValue = [
            'data' => 'Meta shoes',
            'channel' => null,
            'locale' => 'en_US',
            'attribute_code' => $key
        ];

        $this->normalize()->shouldBeLike($expectedValue);
    }
}
