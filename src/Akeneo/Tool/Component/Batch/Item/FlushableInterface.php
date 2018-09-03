<?php

namespace Akeneo\Tool\Component\Batch\Item;

/**
 * A StepElement, for instance, reader, processor, writer can be defined as singleton, for instance, when declared as
 * default Symfony services in the DIC (this is how the BatchBundle works today).
 *
 * In this case, a StepElement can require to be "reset", before (InitializableInterface) or after (FlushableInterface)
 * a step completion to be used twice in a same process, for instance, reset an internal iterator once a file has been
 * read
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface FlushableInterface
{
    /**
     * Custom logic on step completion.
     */
    public function flush();
}
