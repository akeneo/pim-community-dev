<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model;

use PhpSpec\ObjectBehavior;

class ColumnCollectionSpec extends ObjectBehavior
{
    public function it_returns_the_column_uuids()
    {
        $this->beConstructedThrough('createFromNormalized', [[
            [
                'index' => 0,
                'label' => 'A column',
                'uuid' => 'f9be9837-df82-4ad7-8c76-565ac274e900',
            ],
            [
                'index' => 1,
                'label' => 'Another column',
                'uuid' => 'a07b9dd7-f0ff-4d89-85a5-dee411cf53c2',
            ]
        ]]);

        $this->getColumnUuids()->shouldReturn(['f9be9837-df82-4ad7-8c76-565ac274e900', 'a07b9dd7-f0ff-4d89-85a5-dee411cf53c2']);
    }
}
