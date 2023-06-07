<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber\Install;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Sets the first inserted identifier attribute as main identifier
 *
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InitMainIdentifierSubscriber implements EventSubscriberInterface
{
    private ?bool $isMainIdentifierSet = null;

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'initMainIdentifier'
        ];
    }

    public function initMainIdentifier(GenericEvent $event): void
    {
        $attribute = $event->getSubject();

        if (!$attribute instanceof AttributeInterface || AttributeTypes::IDENTIFIER !== $attribute->getType() || $this->thereAlreadyIsAMainIdentifier()) {
            return;
        }

        $this->connection->executeStatement(
            <<<SQL
            UPDATE pim_catalog_attribute
            SET main_identifier = 1
            WHERE attribute_type = :identifierType
            LIMIT 1
            SQL,
            [
                'identifierType' => AttributeTypes::IDENTIFIER
            ]
        );

        $this->isMainIdentifierSet = true;
    }

    private function thereAlreadyIsAMainIdentifier(): bool
    {
        if (null === $this->isMainIdentifierSet) {
            $this->isMainIdentifierSet = \boolval(
                $this->connection->fetchOne(
                    <<<SQL
                    SELECT EXISTS(
                        SELECT * FROM pim_catalog_attribute WHERE main_identifier IS TRUE
                    )
                    SQL
                )
            );
        }

        return $this->isMainIdentifierSet;
    }
}
