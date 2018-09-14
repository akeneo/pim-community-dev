<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Application\Record\EditRecord\ValueUpdater;

use Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory\EditTextValueCommand;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\ValueUpdater\TextUpdater;
use Akeneo\EnrichedEntity\Application\Record\EditRecord\ValueUpdater\ValueUpdaterRegistry;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ValueUpdaterRegistrySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ValueUpdaterRegistry::class);
    }

    function it_register_a_value_updater_and_returns_it_if_it_supports()
    {
        $updater = new TextUpdater();
        $this->register($updater);
        $this->getUpdater(new EditTextValueCommand())->shouldReturn($updater);
    }

    function it_throws_if_it_does_not_finds_an_updater_that_supports(
        AttributeIdentifier $nameIdentifier
    ) {
        $name = $this->getAttribute();

        $command = new EditTextValueCommand();
        $command->attribute = $name;
        $nameIdentifier->normalize()->willReturn('designer_name_fingerprint');

        $this->shouldThrow(\RuntimeException::class)->during('getUpdater', [$command]);
    }

    private function getAttribute(): TextAttribute
    {
        $textAttribute = TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'test'),
            EnrichedEntityIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        return $textAttribute;
    }
}
