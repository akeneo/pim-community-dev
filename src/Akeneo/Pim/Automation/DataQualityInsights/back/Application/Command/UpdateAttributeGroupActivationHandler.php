<?php
declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\AttributeGroupActivationHasChanged;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeGroupActivationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Symfony\Component\Messenger\MessageBusInterface;

final class UpdateAttributeGroupActivationHandler
{
    public function __construct(
        private readonly AttributeGroupActivationRepositoryInterface $attributeGroupActivationRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly Clock $clock,
        private readonly FeatureFlag $dqiUcsEventFeatureFlag,
    ) {
    }

    public function __invoke(UpdateAttributeGroupActivationCommand $command): void
    {
        $attributeGroupCode = new AttributeGroupCode($command->attributeGroupCode);
        $attributeGroupActivation = $this->attributeGroupActivationRepository->getForAttributeGroupCode($attributeGroupCode);

        $currentActivation = null === $attributeGroupActivation ? false : $attributeGroupActivation->isActivated();
        if ($currentActivation !== $command->isActivated) {
            $this->attributeGroupActivationRepository->save(
                new AttributeGroupActivation($attributeGroupCode, $command->isActivated)
            );

            if ($this->dqiUcsEventFeatureFlag->isEnabled()) {
                $this->messageBus->dispatch(new AttributeGroupActivationHasChanged(
                    $command->attributeGroupCode,
                    $command->isActivated,
                    $this->clock->getCurrentTime()
                ));
            }
        }
    }
}
