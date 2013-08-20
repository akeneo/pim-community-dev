<?php

namespace Pim\Bundle\ImportExportBundle\Writer;

use Pim\Bundle\BatchBundle\Model\Writer;

/**
 * Dummy step, can be use to do nothing until you'll have concret implementation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DummyWriter extends Writer
{
    /**
     * {@inheritdoc}
     */
    public function write($item)
    {
    }

    public function getConfigurationFields()
    {
        return array();
    }
}
