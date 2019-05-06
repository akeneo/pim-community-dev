<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxValue;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MaxValueUpdater implements AttributeUpdaterInterface
{

    public function supports(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): bool
    {
        return $attribute instanceof NumberAttribute && $command instanceof EditMaxCommand;
    }

    public function __invoke(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): AbstractAttribute
    {
        if (!$this->supports($attribute, $command)) {
            throw new \RuntimeException(
                'Impossible to update the max value property of the given attribute with the given command.'
            );
        }
        $maxValue = $this->maxValue($command);
        $attribute->setMaxValue($maxValue);

        return $attribute;
    }

    private function maxValue(AbstractEditAttributeCommand $command): AttributeMaxValue
    {
        return null === $command->maxValue ?
            AttributeMaxValue::noMaximum() : AttributeMaxValue::fromString($command->maxValue);
    }
}
