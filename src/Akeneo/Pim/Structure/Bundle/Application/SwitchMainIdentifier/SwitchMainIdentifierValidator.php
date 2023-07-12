<?php

namespace Akeneo\Pim\Structure\Bundle\Application\SwitchMainIdentifier;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SwitchMainIdentifierValidator
{
    private ?Attribute $newMainIdentifier;

    public function __construct(
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly FeatureFlags $featureFlags,
    ) {
    }

    public function validate(
        SwitchMainIdentifierCommand $command
    ): void {
        $this->validateOnboarderIsDisabled();
        $this->loadNewMainIdentifier($command->getNewMainIdentifierCode());
        $this->validateAttributeExists();
        $this->validateAttributeIsAnIdentifier();
        $this->validateAttributeIsNotMainIdentifier();
    }

    private function validateAttributeExists(): void
    {
        if (null === $this->newMainIdentifier) {
            throw new \InvalidArgumentException(sprintf(
                '%s attribute does not exist',
                $this->newMainIdentifier
            ));
        }
    }

    private function validateAttributeIsAnIdentifier(): void
    {
        Assert::isInstanceOf($this->newMainIdentifier, Attribute::class);
        if ($this->newMainIdentifier->getType() !== AttributeTypes::IDENTIFIER) {
            throw new \InvalidArgumentException(sprintf(
                '%s attribute is not an identifier',
                $this->newMainIdentifier
            ));
        }
    }

    private function validateAttributeIsNotMainIdentifier(): void
    {
        Assert::isInstanceOf($this->newMainIdentifier, Attribute::class);
        if ($this->newMainIdentifier->isMainIdentifier()) {
            throw new \InvalidArgumentException(sprintf(
                '%s attribute is already the main identifier',
                $this->newMainIdentifier
            ));
        }
    }

    private function loadNewMainIdentifier(
        string $attributeCode
    ): void {
        $this->newMainIdentifier = $this->attributeRepository->findOneByIdentifier($attributeCode);
    }

    private function validateOnboarderIsDisabled(): void
    {
        $enabled = false;
        try {
            $enabled = $this->featureFlags->isEnabled('onboarder');
        } catch (\InvalidArgumentException) {
        }

        if ($enabled) {
            throw new \InvalidArgumentException('You cannot set another identifier attribute as the main identifier because this feature is not compatible with Akeneo Onboarder.');
        }
    }
}
