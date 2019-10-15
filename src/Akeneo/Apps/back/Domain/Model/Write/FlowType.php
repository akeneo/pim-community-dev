<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Model\Write;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FlowType
{
    private $flowType;

    const DATA_SOURCE = 'data_source';
    const DATA_DESTINATION = 'data_destination';
    const OTHERS = 'others';

    public function __construct(string $flowType)
    {
        $this->flowType = $flowType;
    }

    public static function create(string $flowType): self
    {
        if (!in_array($flowType, [self::DATA_DESTINATION, self::DATA_SOURCE, self::OTHERS])) {
            throw new \InvalidArgumentException('invalid_flow_type');
        }

        return new self($flowType);
    }

    public static function createDefault(): self
    {
        return new self(self::OTHERS);
    }

    public function __toString(): string
    {
        return $this->flowType;
    }
}
