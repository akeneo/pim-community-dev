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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class Family
{
    /* Some of the attributes are still pending */
    public const MAPPING_PENDING = 0;

    /** All attributes are either mapped or unmapped */
    public const MAPPING_FULL = 1;

    /** There is no attributes to be mapped */
    public const MAPPING_EMPTY = 2;

    /** @var string */
    private $code;

    /** @var array */
    private $labels;

    /** @var int */
    private $mappingStatus;

    /**
     * @param string $code
     * @param array $labels
     * @param int $mappingStatus
     */
    public function __construct(string $code, array $labels, int $mappingStatus)
    {
        $this->code = $code;
        $this->labels = $labels;
        $this->mappingStatus = $mappingStatus;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return array
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * @return int
     */
    public function getMappingStatus(): int
    {
        return $this->mappingStatus;
    }
}
