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

namespace Akeneo\EnrichedEntity\tests\back\Acceptance\Context;

use Akeneo\EnrichedEntity\Application\Attribute\DeleteAttribute\DeleteAttributeHandler;
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
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditAttributeContext implements Context
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var DeleteAttributeHandler */
    private $handler;

    /** @var ExceptionContext */
    private $exceptionContext;

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        DeleteAttributeHandler $handler,
        ExceptionContext $exceptionContext
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->handler = $handler;
        $this->exceptionContext = $exceptionContext;
    }

    /**
     * @Given /^the following text attributes:$/
     */
    public function theFollowingTextAttributes(TableNode $attributesTable)
    {
        foreach ($attributesTable->getHash() as $attribute) {
            $this->attributeRepository->create(TextAttribute::create(
                AttributeIdentifier::create($attribute['entity_identifier'], $attribute['code']),
                EnrichedEntityIdentifier::fromString($attribute['entity_identifier']),
                AttributeCode::fromString($attribute['code']),
                LabelCollection::fromArray(json_decode($attribute['labels'], true)),
                AttributeOrder::fromInteger((int) $attribute['order']),
                AttributeRequired::fromBoolean((bool) $attribute['required']),
                AttributeValuePerChannel::fromBoolean((bool) $attribute['value_per_channel']),
                AttributeValuePerLocale::fromBoolean((bool) $attribute['value_per_locale']),
                AttributeMaxLength::fromInteger((int) $attribute['max_length'])
            ));
        }
    }

    /**
     * @When /^the user deletes the attribute "(.+)" linked to the enriched entity "(.+)"$/
     */
    public function theUserDeletesTheAttribute(string $attributeIdentifier, string $entityIdentifier)
    {
        $identifier = AttributeIdentifier::create($entityIdentifier, $attributeIdentifier);
        $this->attributeRepository->deleteByIdentifier($identifier);
    }
}
