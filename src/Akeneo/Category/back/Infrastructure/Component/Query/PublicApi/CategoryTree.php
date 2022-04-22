<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Component\Query\PublicApi;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTree
{
    public const CODE = 'code';
    public const LABELS = 'labels';

    public string $code;
    public array $labels = [];

    public function normalize(): array
    {
        return [
            self::CODE => $this->code,
            self::LABELS => $this->labels
        ];
    }
}
