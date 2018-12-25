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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
final class AttributeOptionMapping
{
    public const STATUS_PENDING = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2;

    /** @var string */
    private $franklinAttributeId;

    /** @var string */
    private $franklinAttributeLabel;

    /** @var string */
    private $catalogAttributeCode;

    /** @var int */
    private $status;

    /**
     * @param string $franklinAttributeId
     * @param string $franklinAttributeLabel
     * @param int $status
     * @param string $catalogAttributeCode
     */
    public function __construct(
        string $franklinAttributeId,
        string $franklinAttributeLabel,
        int $status,
        ?string $catalogAttributeCode
    ) {
        $this->franklinAttributeId = $franklinAttributeId;
        $this->franklinAttributeLabel = $franklinAttributeLabel;
        $this->status = $status;
        $this->catalogAttributeCode = $catalogAttributeCode;
    }

    /**
     * @return string
     */
    public function franklinAttributeId(): string
    {
        return $this->franklinAttributeId;
    }

    /**
     * @return string
     */
    public function franklinAttributeLabel(): string
    {
        return $this->franklinAttributeLabel;
    }

    /**
     * @return int
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function catalogAttributeCode(): ?string
    {
        return $this->catalogAttributeCode;
    }
}
