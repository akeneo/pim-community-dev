<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater\MediaLink;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater\AttributeUpdaterInterface;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\MediaLink\EditMediaTypeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Doctrine\Common\Util\ClassUtils;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class MediaTypeUpdater implements AttributeUpdaterInterface
{
    public function supports(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): bool
    {
        return $attribute instanceof MediaLinkAttribute && $command instanceof EditMediaTypeCommand;
    }

    public function __invoke(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): AbstractAttribute
    {
        if (!$command instanceof EditMediaTypeCommand) {
            throw new \RuntimeException(
                sprintf(
                    'Expected command of type "%s", "%s" given',
                    EditMediaTypeCommand::class,
                    ClassUtils::getClass($command)
                )
            );
        }

        $attribute->setMediaType(MediaType::fromString($command->mediaType));

        return $attribute;
    }
}
