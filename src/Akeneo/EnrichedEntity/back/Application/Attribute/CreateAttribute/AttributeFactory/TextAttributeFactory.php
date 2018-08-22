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

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\CreateTextAttributeCommand;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
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

    public function create(AbstractCreateAttributeCommand $command): AbstractAttribute
    {
        if (!$this->supports($command)) {
            throw new \RuntimeException(
                sprintf(
                    'Expected command of type "%s", "%s" given',
                    CreateTextAttributeCommand::class,
                    ClassUtils::getClass($command)
                )
            );
        }

        return TextAttribute::create(
            AttributeIdentifier::create(
                $command->identifier['enriched_entity_identifier'],
                $command->identifier['identifier']
            ),
            EnrichedEntityIdentifier::fromString($command->enrichedEntityIdentifier),
            AttributeCode::fromString($command->code),
            LabelCollection::fromArray($command->labels),
            AttributeOrder::fromInteger($command->order),
            AttributeRequired::fromBoolean($command->required),
            AttributeValuePerChannel::fromBoolean($command->valuePerChannel),
            AttributeValuePerLocale::fromBoolean($command->valuePerLocale),
            AttributeMaxLength::NO_LIMIT === $command->maxLength ? AttributeMaxLength::infinite() : AttributeMaxLength::fromInteger($command->maxLength)
        );
    }
}
