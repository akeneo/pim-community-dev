<?php

declare(strict_types=1);

namespace Akeneo\Category\Api;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type Locale string
 * @phpstan-type LocalizedLabels array<Locale, string>
 * @phpstan-type NormalizedCategoryTree array{id?:int, code?: string, labels?: LocalizedLabels}
 */
class CategoryTree
{
    public const ID = 'id';
    public const CODE = 'code';
    public const LABELS = 'labels';

    public int $id;
    public string $code;
    /**
     * @var LocalizedLabels
     */
    public array $labels = [];

    /**
     * @return NormalizedCategoryTree
     */
    public function normalize(): array
    {
        return [
            self::ID => $this->id,
            self::CODE => $this->code,
            self::LABELS => $this->labels,
        ];
    }
}
