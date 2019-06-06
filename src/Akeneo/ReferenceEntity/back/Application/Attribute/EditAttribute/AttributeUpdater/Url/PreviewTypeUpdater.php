<?php
declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater\Url;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater\AttributeUpdaterInterface;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\Url\EditPreviewTypeCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\PreviewType;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;
use Doctrine\Common\Util\ClassUtils;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class PreviewTypeUpdater implements AttributeUpdaterInterface
{
    public function supports(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): bool
    {
        return $attribute instanceof UrlAttribute && $command instanceof EditPreviewTypeCommand;
    }

    public function __invoke(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): AbstractAttribute
    {
        if (!$command instanceof EditPreviewTypeCommand) {
            throw new \RuntimeException(
                sprintf(
                    'Expected command of type "%s", "%s" given',
                    EditPreviewTypeCommand::class,
                    ClassUtils::getClass($command)
                )
            );
        }

        $attribute->setPreviewType(PreviewType::fromString($command->previewType));

        return $attribute;
    }
}
