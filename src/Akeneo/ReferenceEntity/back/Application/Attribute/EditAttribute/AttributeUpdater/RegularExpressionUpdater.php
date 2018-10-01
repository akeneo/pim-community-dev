<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\EditAttribute\CommandFactory\EditRegularExpressionCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class RegularExpressionUpdater implements AttributeUpdaterInterface
{
    public function supports(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): bool
    {
        return $command instanceof EditRegularExpressionCommand && $attribute instanceof TextAttribute;
    }

    public function __invoke(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): AbstractAttribute
    {
        if (!$this->supports($attribute, $command)) {
            throw new \RuntimeException('Impossible to update the regular expression of the given attribute with the given command.');
        }

        $regularExpression = $this->getRegularExpression($command);
        $attribute->setRegularExpression($regularExpression);

        return $attribute;
    }

    private function getRegularExpression(AbstractEditAttributeCommand $command): AttributeRegularExpression
    {
        if (null === $command->regularExpression) {
            return AttributeRegularExpression::createEmpty();
        }

        return AttributeRegularExpression::fromString($command->regularExpression);
    }
}
