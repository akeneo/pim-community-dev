<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditMaxFileSizeCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class MaxFileSizeUpdater implements AttributeUpdaterInterface
{
    public function supports(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): bool
    {
        return $attribute instanceof ImageAttribute && $command instanceof EditMaxFileSizeCommand;
    }

    public function __invoke(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): AbstractAttribute
    {
        if (!$this->supports($attribute, $command)) {
            throw new \RuntimeException('Impossible to update the max file size property of the given attribute with the given command.');
        }

        if (AttributeMaxFileSize::NO_LIMIT === $command->maxFileSize) {
            return $attribute->setMaxFileSize(AttributeMaxFileSize::infinite());
        }

        return $attribute->setMaxFileSize(AttributeMaxFileSize::fromString($command->maxFileSize));
    }
}
