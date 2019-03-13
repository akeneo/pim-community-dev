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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Attribute;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveIdentifiersMappingCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveIdentifiersMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Service\RemoveAttributesFromMappingInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Query\SelectFamilyCodesByAttributeQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifierMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeRemoveSubscriber implements EventSubscriberInterface
{
    /** @var SelectFamilyCodesByAttributeQueryInterface */
    private $familyCodesByAttributeQuery;

    /** @var RemoveAttributesFromMappingInterface */
    private $removeAttributesFromMapping;

    /** @var array */
    private $familyCodes = [];

    /** @var GetConnectionStatusHandler */
    private $connectionStatusHandler;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /** @var SaveIdentifiersMappingHandler */
    private $saveIdentifiersMappingHandler;

    /**
     * @param SelectFamilyCodesByAttributeQueryInterface $familyCodesByAttributeQuery
     * @param RemoveAttributesFromMappingInterface $removeAttributesFromMapping
     * @param GetConnectionStatusHandler $connectionStatusHandler
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     * @param SaveIdentifiersMappingHandler $saveIdentifiersMappingHandler
     */
    public function __construct(
        SelectFamilyCodesByAttributeQueryInterface $familyCodesByAttributeQuery,
        RemoveAttributesFromMappingInterface $removeAttributesFromMapping,
        GetConnectionStatusHandler $connectionStatusHandler,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        SaveIdentifiersMappingHandler $saveIdentifiersMappingHandler
    ) {
        $this->familyCodesByAttributeQuery = $familyCodesByAttributeQuery;
        $this->removeAttributesFromMapping = $removeAttributesFromMapping;
        $this->connectionStatusHandler = $connectionStatusHandler;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
        $this->saveIdentifiersMappingHandler = $saveIdentifiersMappingHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_REMOVE => 'onPreRemove',
            StorageEvents::POST_REMOVE => 'onPostRemove',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function onPreRemove(GenericEvent $event): void
    {
        $attribute = $event->getSubject();
        if (!$attribute instanceof AttributeInterface) {
            return;
        }

        if (!$this->isFranklinInsightsActivated()) {
            return;
        }

        $this->familyCodes = $this->familyCodesByAttributeQuery->execute($attribute->getCode());

        $identifiersMapping = $this->identifiersMappingRepository->find();
        if ($identifiersMapping->isMappedTo(new AttributeCode($attribute->getCode()))) {
            $this->updateIdentifiersMapping($identifiersMapping, $attribute);
        }
    }

    /**
     * @param GenericEvent $event
     */
    public function onPostRemove(GenericEvent $event): void
    {
        $attribute = $event->getSubject();
        if (!$attribute instanceof AttributeInterface || empty($this->familyCodes)) {
            return;
        }

        $this->removeAttributesFromMapping->process($this->familyCodes, [$attribute->getCode()]);
    }

    /**
     * @return bool
     */
    private function isFranklinInsightsActivated(): bool
    {
        $connectionStatus = $this->connectionStatusHandler->handle(new GetConnectionStatusQuery(false));

        return $connectionStatus->isActive();
    }

    /**
     * @param IdentifiersMapping $identifiersMapping
     * @param AttributeInterface $removedAttribute
     */
    private function updateIdentifiersMapping(
        IdentifiersMapping $identifiersMapping,
        AttributeInterface $removedAttribute
    ): void {
        foreach ($identifiersMapping as $identifier => $identifierMapping) {
            $attribute = $identifierMapping->getAttribute();
            $attributeCode = null !== $attribute ? (string) $attribute->getCode() : null;
            if (null !== $attribute && $removedAttribute->getCode() === $attributeCode) {
                $mapping = $this->computeNewMapping($identifiersMapping, $identifier);

                $command = new SaveIdentifiersMappingCommand($mapping);
                $this->saveIdentifiersMappingHandler->handle($command);

                return;
            }
        }
    }

    /**
     * @param IdentifiersMapping $identifiersMapping
     * @param string $removedIdentifier
     *
     * @return array
     */
    private function computeNewMapping(IdentifiersMapping $identifiersMapping, string $removedIdentifier): array
    {
        $mapping = array_map(function (IdentifierMapping $identifierMapping) {
            $attribute = $identifierMapping->getAttribute();

            return null !== $attribute ? (string) $attribute->getCode() : null;
        }, $identifiersMapping->getMapping());

        $mapping[$removedIdentifier] = null;

        return $mapping;
    }
}
