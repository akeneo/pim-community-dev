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

namespace Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributeMapping
{
    /* The attribute is not mapped yet */
    public const ATTRIBUTE_PENDING = 0;

    /** The attribute is mapped */
    public const ATTRIBUTE_MAPPED = 1;

    /** The attribute was registered to not be mapped */
    public const ATTRIBUTE_UNMAPPED = 2;

    private const UNKNOWN_ATTRIBUTE_TYPE = 'unknown';

    /** @var string */
    private $targetAttributeCode;

    /** @var string|null */
    private $targetAttributeLabel;

    /** @var string|null */
    private $pimAttributeCode;

    /** @var string */
    private $status;

    /** @var string|null */
    private $targetAttributeType;

    /** @var null|string[] */
    private $summary;

    /**
     * @param string $targetAttributeCode
     * @param null|string $targetAttributeLabel
     * @param null|string $targetAttributeType
     * @param null|string $pimAttributeCode
     * @param int $status
     * @param null|string[] $summary
     */
    public function __construct(
        string $targetAttributeCode,
        ?string $targetAttributeLabel,
        ?string $targetAttributeType,
        ?string $pimAttributeCode,
        int $status,
        ?array $summary
    ) {
        $this->targetAttributeCode = $targetAttributeCode;
        $this->targetAttributeLabel = $targetAttributeLabel;
        $this->targetAttributeType = $targetAttributeType;
        $this->pimAttributeCode = $pimAttributeCode;
        $this->status = $status;
        $this->summary = $summary;
    }

    /**
     * @return string
     */
    public function getTargetAttributeCode(): string
    {
        return $this->targetAttributeCode;
    }

    /**
     * @return null|string
     */
    public function getTargetAttributeLabel(): ?string
    {
        return $this->targetAttributeLabel;
    }

    /**
     * @return null|string
     */
    public function getPimAttributeCode(): ?string
    {
        return $this->pimAttributeCode;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getTargetAttributeType(): string
    {
        if (null === $this->targetAttributeType) {
            return self::UNKNOWN_ATTRIBUTE_TYPE;
        }

        return $this->targetAttributeType;
    }

    /**
     * @return null|string[]
     */
    public function getSummary(): ?array
    {
        return $this->summary;
    }
}
