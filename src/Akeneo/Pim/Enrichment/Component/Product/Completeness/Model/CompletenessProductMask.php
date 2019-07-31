<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Model;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessProductMask
{
    /** @var int */
    private $id;

    /** @var string */
    private $identifier;

    /** @var string */
    private $familyCode;

    /** @var array */
    private $mask;

    public function __construct(
        int $id,
        string $identifier,
        string $familyCode,
        array $mask
    ) {
        $this->id = $id;
        $this->identifier = $identifier;
        $this->familyCode = $familyCode;
        $this->mask = $mask;
    }

    public function familyCode(): string
    {
        return $this->familyCode;
    }

    public function mask(): array
    {
        return $this->mask;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
