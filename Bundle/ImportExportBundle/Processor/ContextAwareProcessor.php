<?php

namespace Oro\Bundle\ImportExportBundle\Processor;

use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;

interface ContextAwareProcessor extends ItemProcessorInterface, ContextAwareInterface
{
}
