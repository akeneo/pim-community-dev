<?php
declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditUrlValueCommand;
use Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory\EditUrlValueCommandFactory;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;
use PhpSpec\ObjectBehavior;

class EditUrlValueCommandFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EditUrlValueCommandFactory::class);
    }

    function it_only_supports_create_value_of_url_attribute(
        ImageAttribute $image,
        UrlAttribute $url
    ) {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'data'    => 'A description'
        ];

        $this->supports($image, $normalizedValue)->shouldReturn(false);
        $this->supports($url, $normalizedValue)->shouldReturn(true);
    }

    function it_creates_text_value(UrlAttribute $textAttribute)
    {
        $normalizedValue = [
            'channel' => 'ecommerce',
            'locale'  => 'en_US',
            'data'    => 'house_255311'
        ];
        $command = $this->create($textAttribute, $normalizedValue);

        $command->shouldBeAnInstanceOf(EditUrlValueCommand::class);
        $command->attribute->shouldBeEqualTo($textAttribute);
        $command->channel->shouldBeEqualTo('ecommerce');
        $command->locale->shouldBeEqualTo('en_US');
        $command->url->shouldBeEqualTo('house_255311');
    }
}
