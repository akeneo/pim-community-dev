<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\MainIdentifier;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChangeMainIdentifierHandler
{
    public function __construct(
        private readonly AttributeRepositoryInterface $attributeRepository,
        private readonly Connection $connection
    ) {
    }

    public function __invoke(ChangeMainIdentifier $changeMainIdentifier): void
    {
        $newMainIdentifier = $this->attributeRepository->findOneByIdentifier($changeMainIdentifier->mainIdentifierCode);
        Assert::isInstanceOf($newMainIdentifier, AttributeInterface::class);
        Assert::same($newMainIdentifier->getType(), AttributeTypes::IDENTIFIER);

        if ($newMainIdentifier->isMainIdentifier()) {
            return;
        }
        $this->updateMainIdentifier($newMainIdentifier);
    }

    private function updateMainIdentifier(AttributeInterface $attribute): void
    {
        $this->connection->executeStatement(
            'UPDATE pim_catalog_attribute SET main_identifier = (code = :code)',
            ['code' => $attribute->getCode()]
        );
    }
}
