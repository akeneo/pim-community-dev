<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\AssetManager\Infrastructure\Validation\Asset;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaLinkValueCommand;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Infrastructure\Network\DnsLookup;
use Akeneo\AssetManager\Infrastructure\Network\IpMatcher;
use Akeneo\AssetManager\Infrastructure\Validation\Asset\EditMediaLinkValueCommand as Constraint;
use Akeneo\AssetManager\Infrastructure\Validation\Asset\EditMediaLinkValueCommandValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class EditMediaLinkValueCommandValidatorSpec extends ObjectBehavior
{
    public function let(ExecutionContextInterface $context, DnsLookup $dnsLookup, IpMatcher $ipMatcher)
    {
        $this->beConstructedWith(['http', 'https'], $dnsLookup, $ipMatcher, '127.0.0.1');
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditMediaLinkValueCommandValidator::class);
    }

    function it_allows_a_whitelisted_domain(ExecutionContextInterface $context, DnsLookup $dnsLookup, IpMatcher $ipMatcher)
    {
        $dnsLookup->ip('example.com')->shouldBeCalled()->willReturn('127.0.0.1');
        $ipMatcher->match('127.0.0.1', ['127.0.0.1'])->shouldBeCalled()->willReturn(true);
        $mediaLinkAttribute = $this->mediaLinkAttribute();
        $command = new EditMediaLinkValueCommand($mediaLinkAttribute, null, null, 'https://example.com/an_image.png');
        $context->buildViolation(Argument::cetera())->shouldNotBeCalled();
        $this->validate($command, new Constraint());
    }

    function it_denies_an_ip_in_private_range(
        ExecutionContextInterface $context,
        DnsLookup $dnsLookup,
        IpMatcher $ipMatcher,
        ConstraintViolationBuilderInterface $violationBuilder
    ) {
        $dnsLookup->ip('example.com')->shouldBeCalled()->willReturn('192.168.15.5');
        $ipMatcher->match('192.168.15.5', ['127.0.0.1'])->shouldBeCalled()->willReturn(false);
        $mediaLinkAttribute = $this->mediaLinkAttribute();
        $command = new EditMediaLinkValueCommand($mediaLinkAttribute, null, null, 'https://example.com/an_image.png');
        $context->buildViolation(Constraint::DOMAIN_NOT_ALLOWED)->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->atPath('name')->shouldBeCalled()->willReturn($violationBuilder);
        $violationBuilder->addViolation()->shouldBeCalled();
        $this->validate($command, new Constraint());
    }

    private function mediaLinkAttribute(): MediaLinkAttribute
    {
        return MediaLinkAttribute::create(
            AttributeIdentifier::create('designer', 'name', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            Prefix::createEmpty(),
            Suffix::createEmpty(),
            MediaType::fromString('image')
        );
    }
}
