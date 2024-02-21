<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Attribute;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql\IsThereAtLeastOneAttributeConfiguredWithMeasurementFamily;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryIsThereAtLeastOneAttributeConfiguredWithMeasurementFamilyStub extends IsThereAtLeastOneAttributeConfiguredWithMeasurementFamily
{
    private $result = false;

    public function __construct(Connection $connection = null)
    {
    }

    public function execute(string $metricFamilyCode): bool
    {
        return $this->result;
    }

    public function setStub(bool $result): void
    {
        $this->result = $result;
    }
}
