<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Domain\ValueObject\Attribute\Value;

use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\TextAreaValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\Value;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TextAreaValueSpec extends ObjectBehavior
{
    public function it_creates_text_value_from_applier()
    {
        $this->beConstructedThrough('fromApplier', [
            "Meta <p>shoes</p>",
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'seo_meta_description',
            'ecommerce',
            'en_US',
        ]);

        $this->shouldHaveType(TextAreaValue::class);
        $this->shouldHaveType(AbstractValue::class);
        $this->shouldImplement(Value::class);
    }

    public function it_creates_text_value_from_array()
    {
        $givenArray = [
            'data' => "Meta <p>shoes</p>",
            'type' => 'textarea',
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'attribute_code' => ''
        ];
        $this->beConstructedThrough('fromArray', [$givenArray]);

        $this->shouldHaveType(TextAreaValue::class);
        $this->shouldHaveType(AbstractValue::class);
        $this->shouldImplement(Value::class);
    }

    public function it_throws_invalid_argument_exception_from_array()
    {
        $givenArray = [
            'data' => "Meta <p>shoes</p>",
            'type' => 'textarea',
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'attribute_code' => ''
        ];

        $this->beConstructedThrough('fromArray', [$givenArray]);
        $this->shouldThrow(
            new \InvalidArgumentException("Cannot find code and uuid.")
        )->duringInstantiation();
    }

    public function it_normalizes(): void
    {
        $this->beConstructedThrough('fromApplier', [
            "Meta <p>shoes</p>",
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'seo_meta_description',
            'ecommerce',
            'en_US',
        ]);

        $key = 'seo_meta_description' . AbstractValue::SEPARATOR . '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s%s',
            $key,
            AbstractValue::SEPARATOR . "ecommerce",
            AbstractValue::SEPARATOR . "en_US",
        );
        $expectedValue = [
            $localeChannelKey => [
                'data' => "Meta <p>shoes</p>",
                'type' => 'textarea',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => $key
            ]
        ];

        $this->normalize()->shouldBeLike($expectedValue);
    }

    public function it_normalizes_with_no_locale(): void
    {
        $textareaValue = "Meta <p>shoes</p>";

        $this->beConstructedThrough('fromApplier', [
            $textareaValue,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'seo_meta_description',
            'ecommerce',
            null,
        ]);

        $key = 'seo_meta_description' . AbstractValue::SEPARATOR . '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s',
            $key,
            AbstractValue::SEPARATOR . "ecommerce"
        );
        $expectedValue = [
            $localeChannelKey => [
                'data' => "Meta <p>shoes</p>",
                'type' => 'textarea',
                'channel' => 'ecommerce',
                'locale' => null,
                'attribute_code' => $key
            ]
        ];

        $this->normalize()->shouldBeLike($expectedValue);
    }

    public function it_normalizes_with_no_channel(): void
    {
        $textareaValue = "Meta <p>shoes</p>";
        $this->beConstructedThrough('fromApplier', [
            $textareaValue,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'seo_meta_description',
            null,
            'en_US',
        ]);

        $key = 'seo_meta_description' . AbstractValue::SEPARATOR . '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s',
            $key,
            AbstractValue::SEPARATOR . "en_US",
        );
        $expectedValue = [
            $localeChannelKey => [
                'data' => "Meta <p>shoes</p>",
                'type' => 'textarea',
                'channel' => null,
                'locale' => 'en_US',
                'attribute_code' => $key
            ]
        ];

        $this->normalize()->shouldBeLike($expectedValue);
    }

    public function it_normalizes_with_no_value(): void
    {
        $this->beConstructedThrough('fromApplier', [
            null,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'seo_meta_description',
            null,
            'en_US',
        ]);

        $key = 'seo_meta_description' . AbstractValue::SEPARATOR . '02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s',
            $key,
            AbstractValue::SEPARATOR . "en_US",
        );
        $expectedValue = [
            $localeChannelKey => [
                'data' => null,
                'type' => 'textarea',
                'channel' => null,
                'locale' => 'en_US',
                'attribute_code' => $key
            ]
        ];

        $this->normalize()->shouldBeLike($expectedValue);
    }
}
