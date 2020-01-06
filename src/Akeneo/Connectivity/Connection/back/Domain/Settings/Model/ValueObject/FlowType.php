<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FlowType
{
    const DATA_SOURCE = 'data_source';
    const DATA_DESTINATION = 'data_destination';
    const OTHER = 'other';

    private const CONSTRAINT_KEY = 'akeneo_connectivity.connection.connection.constraint.flow_type.%s';
    private $flowType;

    public function __construct(string $flowType)
    {
        if (!in_array($flowType, [self::DATA_DESTINATION, self::DATA_SOURCE, self::OTHER])) {
            throw new \InvalidArgumentException(sprintf(self::CONSTRAINT_KEY, 'invalid'));
        }

        $this->flowType = $flowType;
    }

    public function __toString(): string
    {
        return $this->flowType;
    }
}
