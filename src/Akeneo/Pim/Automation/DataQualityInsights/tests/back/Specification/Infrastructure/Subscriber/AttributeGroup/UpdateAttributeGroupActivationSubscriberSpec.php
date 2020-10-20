<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\AttributeGroup;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeGroupActivationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeGroupActivationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateAttributeGroupActivationSubscriberSpec extends ObjectBehavior
{
    public function let(
        FeatureFlag $dataQualityInsightsFeature,
        AttributeGroupActivationRepositoryInterface $attributeGroupActivationRepository,
        GetAttributeGroupActivationQueryInterface $getAttributeGroupActivationQuery,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($dataQualityInsightsFeature, $attributeGroupActivationRepository, $getAttributeGroupActivationQuery, $logger);
    }

    public function it_does_nothing_if_the_subject_is_not_an_attribute_group(
        $attributeGroupActivationRepository,
        $getAttributeGroupActivationQuery
    ) {
        $event = new GenericEvent(new \stdClass());

        $attributeGroupActivationRepository->save(Argument::any())->shouldNotBeCalled();
        $attributeGroupActivationRepository->remove(Argument::any())->shouldNotBeCalled();

        $getAttributeGroupActivationQuery->byCode(Argument::any())->shouldNotBeCalled();

        $this->createAttributeGroupActivation($event);
        $this->removeAttributeGroupActivation($event);
    }

    public function it_does_nothing_if_the_feature_is_not_enabled(
        $dataQualityInsightsFeature,
        $attributeGroupActivationRepository,
        $getAttributeGroupActivationQuery,
        AttributeGroupInterface $attributeGroup
    ) {
        $event = new GenericEvent($attributeGroup->getWrappedObject());

        $dataQualityInsightsFeature->isEnabled()->willReturn(false);

        $attributeGroupActivationRepository->save(Argument::any())->shouldNotBeCalled();
        $attributeGroupActivationRepository->remove(Argument::any())->shouldNotBeCalled();
        $getAttributeGroupActivationQuery->byCode(Argument::any())->shouldNotBeCalled();

        $this->createAttributeGroupActivation($event);
        $this->removeAttributeGroupActivation($event);
    }

    public function it_does_not_create_attribute_group_activation_if_there_is_already_one(
        $dataQualityInsightsFeature,
        $attributeGroupActivationRepository,
        $getAttributeGroupActivationQuery,
        AttributeGroupInterface $attributeGroup
    ) {
        $event = new GenericEvent($attributeGroup->getWrappedObject());
        $attributeGroupCode = new AttributeGroupCode('marketing');

        $attributeGroup->getCode()->willReturn('marketing');
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $getAttributeGroupActivationQuery->byCode($attributeGroupCode)->willReturn(new AttributeGroupActivation($attributeGroupCode, false));

        $attributeGroupActivationRepository->save(Argument::any())->shouldNotBeCalled();

        $this->createAttributeGroupActivation($event);
    }

    public function it_creates_new_attribute_group_activation(
        $dataQualityInsightsFeature,
        $attributeGroupActivationRepository,
        $getAttributeGroupActivationQuery,
        AttributeGroupInterface $attributeGroup
    ) {
        $event = new GenericEvent($attributeGroup->getWrappedObject());

        $attributeGroup->getCode()->willReturn('marketing');
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $getAttributeGroupActivationQuery->byCode(new AttributeGroupCode('marketing'))->willReturn(null);

        $attributeGroupActivationRepository->save(Argument::that(function (AttributeGroupActivation $attributeGroupActivation) {
            return 'marketing' === strval($attributeGroupActivation->getAttributeGroupCode())
                && true === $attributeGroupActivation->isActivated();
        }))->shouldBeCalled();

        $this->createAttributeGroupActivation($event);
    }

    public function it_removes_attribute_group_activation(
        $dataQualityInsightsFeature,
        $attributeGroupActivationRepository,
        AttributeGroupInterface $attributeGroup
    ) {
        $event = new GenericEvent($attributeGroup->getWrappedObject());

        $attributeGroup->getCode()->willReturn('marketing');
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);

        $attributeGroupActivationRepository->remove(new AttributeGroupCode('marketing'))->shouldBeCalled();

        $this->removeAttributeGroupActivation($event);
    }

    public function it_does_not_crash_if_the_repository_fails(
        $dataQualityInsightsFeature,
        $attributeGroupActivationRepository,
        $getAttributeGroupActivationQuery,
        $logger,
        AttributeGroupInterface $attributeGroup
    ) {
        $event = new GenericEvent($attributeGroup->getWrappedObject());

        $attributeGroup->getCode()->willReturn('marketing');
        $dataQualityInsightsFeature->isEnabled()->willReturn(true);
        $getAttributeGroupActivationQuery->byCode(new AttributeGroupCode('marketing'))->willReturn(null);

        $attributeGroupActivationRepository->save(Argument::any())->willThrow(new \Exception('failed'));
        $attributeGroupActivationRepository->remove(Argument::any())->willThrow(new \Exception('failed'));

        $logger->error(Argument::cetera())->shouldBeCalled();

        $this->createAttributeGroupActivation($event);
        $this->removeAttributeGroupActivation($event);
    }
}
