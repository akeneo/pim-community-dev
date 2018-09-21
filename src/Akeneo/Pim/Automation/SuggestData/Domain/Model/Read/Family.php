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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Model\Read;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class Family
{
    /** @var string */
    private $code;

    /** @var array */
    private $labels;

    /** @var int */
    private $mappingStatus;

    /**
     * @param string $code
     * @param array $labels
     */
    public function __construct(string $code, array $labels)
    {
        $this->code = $code;
        $this->labels = $labels;
        $this->mappingStatus = 0;
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
