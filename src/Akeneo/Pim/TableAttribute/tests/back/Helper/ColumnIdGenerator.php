<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\tests\back\Helper;

use Ramsey\Uuid\Uuid;

final class ColumnIdGenerator
{
    public static function generateAsString(string $code): string
    {
        return sprintf('%s_%s', $code, (Uuid::uuid4())->toString());
    }

    public static function ingredient(): string
    {
        return 'ingredient_f6492fb4-d815-4d30-a912-8db321a3e38a';
    }

    public static function quantity(): string
    {
        return 'quantity_f967d82a-b54c-41da-959e-1fa43124afee';
    }

    public static function isAllergenic(): string
    {
        return 'is_allergenic_c8ef6a66-cca8-49c6-9448-b71a48f3636b';
    }

    public static function description(): string
    {
        return 'description_8bb00280-d04f-4c19-a6cf-46b83ad9553d';
    }

    public static function supplier(): string
    {
        return 'supplier_d39d3c48-46e6-4744-8196-56e08563fd46';
    }
}
