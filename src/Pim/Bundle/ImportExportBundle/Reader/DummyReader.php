<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Pim\Bundle\BatchBundle\Item\ItemReaderInterface;
use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;

/**
 * Dummy step, can be use to do nothing until you'll have concret implementation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DummyReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function read()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }
}
