<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Subscriber\AttributeGroup;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeGroupActivationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeGroupActivationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateAttributeGroupActivationSubscriber implements EventSubscriberInterface
{
    /** @var FeatureFlag */
    private $dataQualityInsightsFeature;

    /** @var AttributeGroupActivationRepositoryInterface */
    private $attributeGroupActivationRepository;

    /** @var GetAttributeGroupActivationQueryInterface */
    private $getAttributeGroupActivationQuery;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        FeatureFlag $dataQualityInsightsFeature,
        AttributeGroupActivationRepositoryInterface $attributeGroupActivationRepository,
        GetAttributeGroupActivationQueryInterface $getAttributeGroupActivationQuery,
        LoggerInterface $logger
    ) {
        $this->dataQualityInsightsFeature = $dataQualityInsightsFeature;
        $this->attributeGroupActivationRepository = $attributeGroupActivationRepository;
        $this->getAttributeGroupActivationQuery = $getAttributeGroupActivationQuery;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => ['createAttributeGroupActivation'],
            StorageEvents::POST_REMOVE => ['removeAttributeGroupActivation'],
        ];
    }

    public function createAttributeGroupActivation(GenericEvent $event): void
    {
        $attributeGroup = $event->getSubject();
        if (! $attributeGroup instanceof AttributeGroupInterface) {
            return;
        }

        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        try {
            $attributeGroupCode = new AttributeGroupCode($attributeGroup->getCode());
            $attributeGroupActivation = $this->getAttributeGroupActivationQuery->byCode($attributeGroupCode);

            if (null === $attributeGroupActivation) {
                $attributeGroupActivation = new AttributeGroupActivation($attributeGroupCode, true);
                $this->attributeGroupActivationRepository->save($attributeGroupActivation);
            }
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Unable to create attribute group activation for "{attribute_group_code}"',
                [
                    'attribute_group_code' => $attributeGroup->getCode(),
                    'error_code' => 'unable_to_remove_attribute_group_activation',
                    'error_message' => $exception->getMessage(),
                ]
            );
        }
    }

    public function removeAttributeGroupActivation(GenericEvent $event): void
    {
        $attributeGroup = $event->getSubject();
        if (! $attributeGroup instanceof AttributeGroupInterface) {
            return;
        }

        if (! $this->dataQualityInsightsFeature->isEnabled()) {
            return;
        }

        try {
            $attributeGroupCode = new AttributeGroupCode($attributeGroup->getCode());
            $this->attributeGroupActivationRepository->remove($attributeGroupCode);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Unable to remove attribute group activation for "{attribute_group_code}"',
                [
                    'attribute_group_code' => $attributeGroup->getCode(),
                    'error_code' => 'unable_to_remove_attribute_group_activation',
                    'error_message' => $exception->getMessage(),
                ]
            );
        }
    }
}
