<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\IdentifierValueInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductIdentifiersSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => 'fillProductIdentifiers',
        ];
    }

    public function fillProductIdentifiers(GenericEvent $ev): void
    {
        $product = $ev->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $identifiers = \array_map(
            static fn (IdentifierValueInterface $value): string => \sprintf(
                '%s#%s',
                $value->getAttributeCode(),
                $value->getData()
            ),
            $product->getValues()->filter(
                static fn (ValueInterface $value): bool => $value instanceof IdentifierValueInterface
            )->getValues()
        );

        $this->connection->executeStatement(
            <<<SQL
            REPLACE INTO pim_catalog_product_identifiers (uuid, identifiers)
            VALUES (:uuid, JSON_ARRAY(:identifiers));
            SQL,
            ['uuid' => $product->getUuid()->getBytes(), 'identifiers' => $identifiers],
            ['uuid' => Types::BINARY, 'identifiers' => Connection::PARAM_STR_ARRAY]
        );
    }
}
