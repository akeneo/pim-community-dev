<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class MysqlSyncReport
{
    public function __construct(
        public array $missingLines=[],
        public array $lines2Delete=[],
        public array $obsoleteLines=[]
    ) {
    }
}
