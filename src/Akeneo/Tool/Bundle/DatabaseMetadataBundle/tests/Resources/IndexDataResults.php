<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\tests\Resources;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class IndexDataResults
{
    public static function initDiffBlock(array $old, array $new): string
    {
        return sprintf('[[{"tag": 4,
                    "old": {
                        "offset": 4,
                        "lines":%s},
                    "new": {
                        "offset": 4,
                        "lines": %s}
                    }]]',json_encode($old),json_encode($new));
    }
}
