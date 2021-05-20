<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\AbstractEditAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditOptionsCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class OptionsUpdater implements AttributeUpdaterInterface
{
    public function supports(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): bool
    {
        return $command instanceof EditOptionsCommand
            && ($attribute instanceof OptionCollectionAttribute || $attribute instanceof OptionAttribute);
    }

    public function __invoke(AbstractAttribute $attribute, AbstractEditAttributeCommand $command): AbstractAttribute
    {
        if (!$this->supports($attribute, $command)) {
            throw new \RuntimeException('Impossible to update the options of the given attribute with the given command.');
        }

        $options = $this->createOptions($command);
        $attribute->setOptions($options);

        return $attribute;
    }

    /**
     * @return AttributeOption[]
     */
    private function createOptions(EditOptionsCommand $command): array
    {
        return array_map(fn(array $attributeOption) => AttributeOption::create(
            OptionCode::fromString($attributeOption['code']),
            LabelCollection::fromArray($attributeOption['labels'])
        ), $command->options);
    }
}
