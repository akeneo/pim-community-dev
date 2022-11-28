<?php

declare(strict_types=1);

namespace Specification\Akeneo\Category\Domain\ValueObject\Attribute\Value;

use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\ImageValue;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\Value;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ImageValueSpec extends ObjectBehavior
{

    public function it_creates_text_value_from_applier()
    {
        $givenImageDataValue = [
            'size' => 12,
            'extension' => 'jpg',
            'file_path' => 'file/path/logo.jpg',
            'mime_type' => 'image/jpeg',
            'original_filename' => 'logo',
        ];
        $this->beConstructedThrough('fromApplier',[
            $givenImageDataValue,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            'ecommerce',
            'en_US',
        ]);

        $this->shouldHaveType(ImageValue::class);
        $this->shouldHaveType(AbstractValue::class);
        $this->shouldImplement(Value::class);
    }

    public function it_creates_text_value_from_array()
    {
        $givenImageDataValue = [
            'size' => 12,
            'extension' => 'jpg',
            'file_path' => 'file/path/logo.jpg',
            'mime_type' => 'image/jpeg',
            'original_filename' => 'logo',
        ];

        $givenArray = [
            'data' => $givenImageDataValue,
            'type' => 'image',
            'channel' => 'ecommerce',
            'locale' => 'en_US',
            'attribute_code' => 'hero_banner|02274dac-e99a-4e1d-8f9b-794d4c3ba330'
        ];
        $this->beConstructedThrough('fromArray',[$givenArray]);

        $this->shouldHaveType(ImageValue::class);
        $this->shouldHaveType(AbstractValue::class);
        $this->shouldImplement(Value::class);
    }

    public function it_throws_invalid_argument_exception_from_array()
    {
        $givenImageDataValue = [
            'size' => 12,
            'extension' => 'jpg',
            'file_path' => 'file/path/logo.jpg',
            'mime_type' => 'image/jpeg',
            'original_filename' => 'logo',
        ];

        $givenArray = [
            'data' => $givenImageDataValue,
            'type' => 'image',
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
        $givenImageDataValue = [
            'size' => 12,
            'extension' => 'jpg',
            'file_path' => 'file/path/logo.jpg',
            'mime_type' => 'image/jpeg',
            'original_filename' => 'logo',
        ];

        $this->beConstructedThrough('fromApplier',[
            $givenImageDataValue,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            'ecommerce',
            'en_US',
        ]);

        $key = 'hero_banner'.AbstractValue::SEPARATOR.'02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s%s',
            $key,
            AbstractValue::SEPARATOR."ecommerce",
            AbstractValue::SEPARATOR."en_US",
        );
        $expectedValue = [
            $localeChannelKey => [
                'data' => $givenImageDataValue,
                'type' => 'image',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'attribute_code' => $key
            ]
        ];

        $this->normalize()->shouldBeLike($expectedValue);
    }

    public function it_normalizes_with_no_locale(): void
    {
        $givenImageDataValue = [
            'size' => 12,
            'extension' => 'jpg',
            'file_path' => 'file/path/logo.jpg',
            'mime_type' => 'image/jpeg',
            'original_filename' => 'logo',
        ];

        $this->beConstructedThrough('fromApplier',[
            $givenImageDataValue,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            'ecommerce',
            null,
        ]);

        $key = 'hero_banner'.AbstractValue::SEPARATOR.'02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s',
            $key,
            AbstractValue::SEPARATOR."ecommerce"
        );
        $expectedValue = [
            $localeChannelKey => [
                'data' => $givenImageDataValue,
                'type' => 'image',
                'channel' => 'ecommerce',
                'locale' => null,
                'attribute_code' => $key
            ]
        ];

        $this->normalize()->shouldBeLike($expectedValue);
    }

    public function it_normalizes_with_no_channel(): void
    {
        $givenImageDataValue = [
            'size' => 12,
            'extension' => 'jpg',
            'file_path' => 'file/path/logo.jpg',
            'mime_type' => 'image/jpeg',
            'original_filename' => 'logo',
        ];

        $this->beConstructedThrough('fromApplier',[
            $givenImageDataValue,
            '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            'hero_banner',
            null,
            'en_US',
        ]);

        $key = 'hero_banner'.AbstractValue::SEPARATOR.'02274dac-e99a-4e1d-8f9b-794d4c3ba330';
        $localeChannelKey = sprintf(
            '%s%s',
            $key,
            AbstractValue::SEPARATOR."en_US",
        );
        $expectedValue = [
            $localeChannelKey => [
                'data' => $givenImageDataValue,
                'type' => 'image',
                'channel' => null,
                'locale' => 'en_US',
                'attribute_code' => $key
            ]
        ];

        $this->normalize()->shouldBeLike($expectedValue);
    }

}
