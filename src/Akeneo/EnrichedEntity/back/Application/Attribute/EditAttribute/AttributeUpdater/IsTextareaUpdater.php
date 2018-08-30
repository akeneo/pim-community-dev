<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\EditAttribute\CommandFactory\EditIsTextareaCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIsTextarea;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Doctrine\Common\Util\ClassUtils;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IsTextareaUpdater implements AttributeUpdaterInterface
{
    public function supports(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): bool
    {
        return $command instanceof EditIsTextareaCommand && $attribute instanceof TextAttribute;
    }

    public function __invoke(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): AbstractAttribute
    {
        if (!$command instanceof EditIsTextareaCommand) {
            throw new \RuntimeException(
                sprintf(
                    'Expected command of type "%s", "%s" given',
                    EditIsTextareaCommand::class,
                    ClassUtils::getClass($command)
                )
            );
        }

        $attribute->setIsTextarea(AttributeIsTextarea::fromBoolean($command->isTextarea));

        return $attribute;
    }
}
