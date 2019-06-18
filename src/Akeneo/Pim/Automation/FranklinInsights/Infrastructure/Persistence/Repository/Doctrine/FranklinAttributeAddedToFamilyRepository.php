<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeAddedToFamily;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeAddedToFamilyRepositoryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FranklinAttributeAddedToFamilyRepository implements FranklinAttributeAddedToFamilyRepositoryInterface
{
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function save(FranklinAttributeAddedToFamily $franklinAttributeAddedToFamily): void
    {
        $sqlQuery = <<<'SQL'
INSERT INTO pimee_franklin_insights_attribute_added_to_family
(attribute_code, family_code)
VALUES (:attribute_code, :family_code)
SQL;

        $bindParams = [
            'attribute_code' => (string) $franklinAttributeAddedToFamily->getAttributeCode(),
            'family_code' => (string) $franklinAttributeAddedToFamily->getFamilyCode(),
        ];

        $this->dbalConnection->executeUpdate($sqlQuery, $bindParams);
    }
}
