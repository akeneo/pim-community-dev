<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemReaderInterface;

/**
 * File reader
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function read()
    {
        throw new \Exception('Not implemented yet.');
    }
}
