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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeOptionCode;

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

    /** @var AttributeOptionCode */
    private $catalogAttributeOptionCode;

    /** @var int */
    private $status;

    public function __construct(
        string $franklinAttributeOptionId,
        string $franklinAttributeOptionLabel,
        int $status,
        ?AttributeOptionCode $catalogAttributeOptionCode
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
     * @return AttributeOptionCode
     */
    public function catalogAttributeOptionCode(): ?AttributeOptionCode
    {
        return $this->catalogAttributeOptionCode;
    }
}
