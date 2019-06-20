<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditMaxFileSizeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class MaxFileSizeUpdater implements AttributeUpdaterInterface
{
    private const NO_LIMIT = null;

    public function supports(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): bool
    {
        return $attribute instanceof ImageAttribute && $command instanceof EditMaxFileSizeCommand;
    }

    public function __invoke(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): AbstractAttribute
    {
        if (!$this->supports($attribute, $command)) {
            throw new \RuntimeException('Impossible to update the max file size property of the given attribute with the given command.');
        }

        if (self::NO_LIMIT === $command->maxFileSize) {
            $attribute->setMaxFileSize(AttributeMaxFileSize::noLimit());
        } else {
            $attribute->setMaxFileSize(AttributeMaxFileSize::fromString($command->maxFileSize));
        }

        return $attribute;
    }
}
