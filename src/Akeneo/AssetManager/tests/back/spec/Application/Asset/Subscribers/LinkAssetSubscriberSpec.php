<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\Subscribers;

use Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates\RuleTemplateExecutor;
use Akeneo\AssetManager\Application\Asset\Subscribers\LinkAssetSubscriber;
use Akeneo\AssetManager\Domain\Event\AssetCreatedEvent;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class LinkAssetSubscriberSpec extends ObjectBehavior
{
    function let(RuleTemplateExecutor $ruleTemplateExecutor)
    {
        $this->beConstructedWith($ruleTemplateExecutor);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LinkAssetSubscriber::class);
    }

    function it_subscribes_to_events()
    {
        $this::getSubscribedEvents()->shouldReturn(
            [
                AssetCreatedEvent::class => 'whenAssetCreated',
            ]
        );
    }

    function it_triggers_the_rule_template_executor(RuleTemplateExecutor $ruleTemplateExecutor)
    {
        $expectedAssetCode = 'starck';
        $expectedAssetFamilyIdentifier = 'designer';

        $ruleTemplateExecutor->execute(
            Argument::that(
                function (AssetFamilyIdentifier $actualFamilyIdentifier) use ($expectedAssetFamilyIdentifier)
                {
                    return $actualFamilyIdentifier->equals(
                        AssetFamilyIdentifier::fromString($expectedAssetFamilyIdentifier)
                    );
                }
            ),
            Argument::that(
                function (AssetCode $actualAssetCode) use ($expectedAssetCode)
                {
                    return $actualAssetCode->equals(AssetCode::fromString($expectedAssetCode));
                }
            )
        )->shouldBeCalled();

        $this->whenAssetCreated(
            new AssetCreatedEvent(
                AssetIdentifier::fromString('asset_identifier'),
                AssetCode::fromString($expectedAssetCode),
                AssetFamilyIdentifier::fromString($expectedAssetFamilyIdentifier)
            )
        );
    }
}

