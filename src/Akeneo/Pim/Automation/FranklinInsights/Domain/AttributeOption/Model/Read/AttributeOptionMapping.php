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
    private $franklinAttributeOptionId;

    /** @var string */
    private $franklinAttributeOptionLabel;

    /** @var string */
    private $catalogAttributeOptionCode;

    /** @var int */
    private $status;

    /**
     * @param string $franklinAttributeOptionId
     * @param string $franklinAttributeOptionLabel
     * @param int $status
     * @param null|string $catalogAttributeOptionCode
     */
    public function __construct(
        string $franklinAttributeOptionId,
        string $franklinAttributeOptionLabel,
        int $status,
        ?string $catalogAttributeOptionCode
    ) {
        $this->franklinAttributeOptionId = $franklinAttributeOptionId;
        $this->franklinAttributeOptionLabel = $franklinAttributeOptionLabel;
        $this->status = $status;
        $this->catalogAttributeOptionCode = $catalogAttributeOptionCode;
    }

    /**
     * @return string
     */
    public function franklinAttributeOptionId(): string
    {
        return $this->franklinAttributeOptionId;
    }

    /**
     * @return string
     */
    public function franklinAttributeOptionLabel(): string
    {
        return $this->franklinAttributeOptionLabel;
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
    public function catalogAttributeOptionCode(): ?string
    {
        return $this->catalogAttributeOptionCode;
    }
}
