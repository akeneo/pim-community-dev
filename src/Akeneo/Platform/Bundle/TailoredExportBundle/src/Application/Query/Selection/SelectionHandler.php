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

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection;

use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;

class SelectionHandler
{
    /** @var iterable<SelectionHandlerInterface> */
    private iterable $selectionHandlers;

    public function __construct(iterable $selectionHandlers)
    {
        $this->selectionHandlers = $selectionHandlers;
    }

    public function applySelection(SelectionInterface $selection, SourceValueInterface $value): string
    {
        foreach ($this->selectionHandlers as $selectionHandler) {
            if ($selectionHandler->supports($selection, $value)) {
                return $selectionHandler->applySelection($selection, $value);
            }
        }

        throw new \LogicException(sprintf('No selection available for "%s" and "%s"', get_class($value), get_class($selection)));
    }
}
