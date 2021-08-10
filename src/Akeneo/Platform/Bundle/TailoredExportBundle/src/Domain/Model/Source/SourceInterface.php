<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Domain\Model\Source;

use Akeneo\Platform\TailoredExport\Domain\Model\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;

interface SourceInterface
{
    public function getOperationCollection(): OperationCollection;

    public function getSelection(): SelectionInterface;
}
