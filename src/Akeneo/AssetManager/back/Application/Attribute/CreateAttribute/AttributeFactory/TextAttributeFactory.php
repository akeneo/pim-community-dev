<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\AssetManager\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRichTextEditor;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Doctrine\Common\Util\ClassUtils;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class TextAttributeFactory implements AttributeFactoryInterface
{
    public function supports(AbstractCreateAttributeCommand $command): bool
    {
        return $command instanceof CreateTextAttributeCommand;
    }

    public function create(
        AbstractCreateAttributeCommand $command,
        AttributeIdentifier $identifier,
        AttributeOrder $order
    ): AbstractAttribute {
        if (!$this->supports($command)) {
            throw new \RuntimeException(
                sprintf(
                    'Expected command of type "%s", "%s" given',
                    CreateTextAttributeCommand::class,
                    ClassUtils::getClass($command)
                )
            );
        }

        $maxLength = AttributeMaxLength::NO_LIMIT === $command->maxLength ? AttributeMaxLength::noLimit() : AttributeMaxLength::fromInteger($command->maxLength);

        if ($command->isTextarea) {
            return TextAttribute::createTextarea(
                $identifier,
                AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier),
                AttributeCode::fromString($command->code),
                LabelCollection::fromArray($command->labels),
                $order,
                AttributeIsRequired::fromBoolean($command->isRequired),
                AttributeValuePerChannel::fromBoolean($command->valuePerChannel),
                AttributeValuePerLocale::fromBoolean($command->valuePerLocale),
                $maxLength,
                AttributeIsRichTextEditor::fromBoolean($command->isRichTextEditor)
            );
        }

        return TextAttribute::createText(
            $identifier,
            AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier),
            AttributeCode::fromString($command->code),
            LabelCollection::fromArray($command->labels),
            $order,
            AttributeIsRequired::fromBoolean($command->isRequired),
            AttributeValuePerChannel::fromBoolean($command->valuePerChannel),
            AttributeValuePerLocale::fromBoolean($command->valuePerLocale),
            $maxLength,
            AttributeValidationRule::NONE === $command->validationRule ? AttributeValidationRule::none() : AttributeValidationRule::fromString($command->validationRule),
            AttributeRegularExpression::EMPTY === $command->regularExpression ? AttributeRegularExpression::createEmpty() : AttributeRegularExpression::fromString($command->regularExpression)
        );
    }
}
