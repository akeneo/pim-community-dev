<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAndNotScopableAttributeException;
use PhpSpec\ObjectBehavior;

class LocalizableAndNotScopableAttributeExceptionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('fromAttributeChannelAndLocale', ['description', 'ecommerce', 'en_US']);
    }

    public function it_is_a_domain_and_templated_error_exception(): void
    {
        $this->shouldHaveType(LocalizableAndNotScopableAttributeException::class);
        $this->shouldImplement(DomainErrorInterface::class);
        $this->shouldImplement(TemplatedErrorMessageInterface::class);
    }

    public function it_provides_a_templated_error_message(): void
    {
        $templatedMessage = $this->getTemplatedErrorMessage();
        $templatedMessage
            ->getTemplate()
            ->shouldReturn(
                'The {attribute_code} attribute does not require a value per channel' .
                ' ({channel_code} was detected) but requires a value per locale ({locale_code} was detected).',
            );
        $templatedMessage->getParameters()->shouldReturn([
            'attribute_code' => 'description',
            'channel_code' => 'ecommerce',
            'locale_code' => 'en_US',
        ]);
    }

    public function it_provides_the_attribute_code(): void
    {
        $this->getAttributeCode()->shouldReturn('description');
    }

    public function it_provides_the_property_name(): void
    {
        $this->getPropertyName()->shouldReturn('attribute');
    }

    public function it_provides_a_message_with_null_parameters(): void
    {
        $this->beConstructedThrough('fromAttributeChannelAndLocale', ['description', null, null]);
        $this->getMessage()->shouldReturn(
            'The description attribute does not require a value per channel' .
            ' (nothing was detected) but requires a value per locale (nothing was detected).'
        );
    }
}
