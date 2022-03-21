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
class IndexDatasResults
{
    public static function initDiffBlock(array $old, array $new): string
    {
        $lineResult = '[[{"tag": 4,
                    "old": {
                        "offset": 4,
                        "lines":';
        $lineResult .= json_encode($old);
        $lineResult .= '},
                    "new": {
                        "offset": 4,
                        "lines": ';
        $lineResult .= json_encode($new);
        $lineResult .= '}
              }]]';

        return $lineResult;
    }
}