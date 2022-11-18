<?php

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Subscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateIndexesSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AttributeRepositoryInterface $attributeRepository,
        private Connection $connection,
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => 'updateProductIndexes',
            StorageEvents::POST_SAVE_ALL => 'updateProductsIndexes',
        ];
    }

    public function updateProductIndexes(GenericEvent $event): void
    {
        $object = $event->getSubject();
        if (!$object instanceof ProductInterface) {
            return;
        }

        $this->updateIndexes([$event->getSubject()]);
    }

    public function updateProductsIndexes(GenericEvent $event): void
    {
        if (!\is_array($event->getSubject())) {
            return;
        }
        foreach ($event->getSubject() as $subject) {
            if (!$subject instanceof ProductInterface) {
                return;
            }
        }

        $this->updateIndexes($event->getSubject());
    }

    /**
     * @param ProductInterface[] $products
     */
    private function updateIndexes(array $products): void
    {
        $deleteSql = <<<SQL
DELETE FROM pim_catalog_identifier_generator_prefixes WHERE product_uuid IN (:product_uuids)
SQL;

        $productUuidsAsBytes = \array_map(
            fn (ProductInterface $product): string => Uuid::fromString($product->getUuid())->getBytes(),
            $products
        );

        $this->connection->executeQuery($deleteSql,
            ['product_uuids' => $productUuidsAsBytes],
            ['product_uuids' => Connection::PARAM_STR_ARRAY]
        );

        $toAdd = [];
        /** @var AttributeInterface[] $identifierAttributes */
        $identifierAttributes = [$this->attributeRepository->getIdentifier()];
        foreach ($products as $product) {
            foreach ($identifierAttributes as $identifierAttribute) {
                $toAdd = \array_merge($toAdd, $this->getPrefixesAndNumbers(
                    $product->getValue($identifierAttribute->getCode())?->getData(),
                    $identifierAttribute->getId(),
                    $product->getUuid()->toString()
                ));
            }
        }

        if (\count($toAdd) === 0) {
            return;
        }

        $values = [];
        foreach ($toAdd as $line) {
            $values[] = \sprintf(
                '(UUID_TO_BIN("%s"), %d, "%s", %d)',
                $line[0],
                $line[1],
                $line[2],
                $line[3]
            );
        };

        $valuesStr = \implode(',', $values);

        $insertSql = <<<SQL
INSERT INTO pim_catalog_identifier_generator_prefixes (`product_uuid`, `attribute_id`, `prefix`, `number`) VALUES ${valuesStr}
SQL;

        $this->connection->executeQuery($insertSql);
    }

    /**
     * Returns the prefix and their associated number
     * Ex: "AKN-2012" will return ["AKN-" => 2012, "AKN-2" => 12, "AKN-20" => 12, "AKN-201" => 2]
     */
    private function getPrefixesAndNumbers(?string $identifier, int $attributeId, string $productUuid)
    {
        if (null === $identifier) {
            return [];
        }
        $results = [];
        for ($i = 0; $i < strlen($identifier); $i++) {
            $charAtI = substr($identifier, $i, 1);
            if (is_numeric($charAtI)) {
                $prefix = substr($identifier, 0, $i);
                $results[] = [$productUuid, $attributeId, $prefix, $this->getAllBeginningNumbers(substr($identifier, $i))];
            }
        }
        return $results;
    }

    /**
     * Returns all the beginning numbers from a string
     * Ex: "251-toto" will return 251
     */
    private function getAllBeginningNumbers(string $identifierFromAnInteger)
    {
        $result = '';
        $i = 0;
        while (is_numeric(substr($identifierFromAnInteger, $i, 1))) {
            $result = $result . substr($identifierFromAnInteger, $i, 1);
            $i++;
        }
        return \intval($result);
    }
}
