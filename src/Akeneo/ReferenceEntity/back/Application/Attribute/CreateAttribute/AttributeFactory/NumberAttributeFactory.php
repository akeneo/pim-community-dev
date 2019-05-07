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

namespace Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AbstractCreateAttributeCommand;
use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\CreateNumberAttributeCommand;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsDecimal;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeLimit;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\NumberAttribute;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Doctrine\Common\Util\ClassUtils;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class NumberAttributeFactory implements AttributeFactoryInterface
{
    public function supports(AbstractCreateAttributeCommand $command): bool
    {
        return $command instanceof CreateNumberAttributeCommand;
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
                    CreateNumberAttributeCommand::class,
                    ClassUtils::getClass($command)
                )
            );
        }

        return NumberAttribute::create(
            $identifier,
            ReferenceEntityIdentifier::fromString($command->referenceEntityIdentifier),
            AttributeCode::fromString($command->code),
            LabelCollection::fromArray($command->labels),
            $order,
            AttributeIsRequired::fromBoolean($command->isRequired),
            AttributeValuePerChannel::fromBoolean($command->valuePerChannel),
            AttributeValuePerLocale::fromBoolean($command->valuePerLocale),
            AttributeIsDecimal::fromBoolean($command->isDecimal),
            $this->minValue($command),
            $this->maxValue($command)
        );
    }

    private function minValue(CreateNumberAttributeCommand $command): AttributeLimit
    {
       return null !== $command->minValue
           ? AttributeLimit::fromString($command->minValue) : AttributeLimit::limitLess();
    }

    private function maxValue(CreateNumberAttributeCommand $command): AttributeLimit
    {
        return null !== $command->maxValue
            ? AttributeLimit::fromString($command->maxValue) : AttributeLimit::limitLess();
    }
}
