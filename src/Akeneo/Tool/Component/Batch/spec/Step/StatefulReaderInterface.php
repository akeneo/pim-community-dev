<?php
declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\spec\Step;

use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\StatefulInterface;

interface StatefulReaderInterface extends ItemReaderInterface, StatefulInterface
{
}
