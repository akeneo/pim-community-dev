<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class EntityIndexConfigurationPair
{
    public function __construct(
        private EntityIndexConfiguration $mySql,
        private EntityIndexConfiguration $elasticsearch
    ) {
    }
}
