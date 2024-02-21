<?php
declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\spec\Step;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Item\StatefulInterface;

interface StatefulWriterInterface extends ItemWriterInterface, StatefulInterface
{
}
