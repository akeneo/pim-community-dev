<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTree
{
    public const ID = 'id';
    public const CODE = 'code';
    public const LABELS = 'labels';

    public int $id;
    public string $code;
    public array $labels = [];

    public function normalize(): array
    {
        return [
            self::ID => $this->id,
            self::CODE => $this->code,
            self::LABELS => $this->labels
        ];
    }
}
