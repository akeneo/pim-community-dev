<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber\Install;

use Akeneo\Pim\Structure\Bundle\MainIdentifier\ChangeMainIdentifier;
use Akeneo\Pim\Structure\Bundle\MainIdentifier\ChangeMainIdentifierHandler;
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
    public function __construct(
        private readonly ChangeMainIdentifierHandler $handler,
        private readonly Connection $connection,
        private ?bool $mainIdentifierSet = null
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
        if ($this->thereAlreadyIsAMainIdentifier()) {
            return;
        }
        $attribute = $event->getSubject();
        if (!$attribute instanceof AttributeInterface || AttributeTypes::IDENTIFIER !== $attribute->getType()) {
            return;
        }

        ($this->handler)(new ChangeMainIdentifier((string) $attribute->getCode()));
        $this->mainIdentifierSet = true;
    }

    private function thereAlreadyIsAMainIdentifier(): bool
    {
        if (null === $this->mainIdentifierSet) {
            $this->mainIdentifierSet = (bool) $this->connection->fetchOne(
                <<<SQL
                SELECT EXISTS(
                    SELECT * FROM pim_catalog_attribute WHERE main_identifier IS TRUE
                )
                SQL
            );
        }

        return $this->mainIdentifierSet;
    }
}
