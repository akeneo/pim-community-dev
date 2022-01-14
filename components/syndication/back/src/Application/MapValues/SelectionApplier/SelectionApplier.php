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

namespace Akeneo\Platform\Syndication\Application\MapValues\SelectionApplier;

use Akeneo\Platform\Syndication\Application\Common\Selection\SelectionInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\Target\Target;

class SelectionApplier
{
    /** @var iterable<SelectionApplierInterface> */
    private iterable $selectionAppliers;

    public function __construct(iterable $selectionAppliers)
    {
        $this->selectionAppliers = $selectionAppliers;
    }

    public function applySelection(SelectionInterface $selection, Target $target, SourceValueInterface $value)
    {
        foreach ($this->selectionAppliers as $selectionApplier) {
            if ($selectionApplier->supports($selection, $target, $value)) {
                return $selectionApplier->applySelection($selection, $target, $value);
            }
        }

        throw new \LogicException(sprintf('No selection available for "%s" and "%s"', get_class($value), get_class($selection)));
    }
}
