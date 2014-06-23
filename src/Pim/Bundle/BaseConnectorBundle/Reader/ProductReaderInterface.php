<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader;

use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

/**
 * Product reader interface
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductReaderInterface extends ItemReaderInterface, StepExecutionAwareInterface
{
    /**
     * Set channel
     *
     * @param string $channel
     */
    public function setChannel($channel);

    /**
     * Get channel
     *
     * @return string
     */
    public function getChannel();
}
