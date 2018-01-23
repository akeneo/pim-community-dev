<?php

declare(strict_types=1);

namespace Pim\Component\Connector\Reader\File;

use Akeneo\Component\Batch\Item\FlushableInterface;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * Contract for a file reader used in a Batch item step. It must be flushable and must accept a step execution object.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface FileReaderInterface extends ItemReaderInterface, FlushableInterface, StepExecutionAwareInterface
{
}
