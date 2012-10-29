<?php
namespace Pim\Bundle\IcecatConnectorBundle\Extract;

/**
 *
 * Interface readable
 * TODO: should be moved in dataflow ?
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
interface ReadInterface
{
    /**
     * Download file in defined path
     * @param string $url
     */
    public function read($url);
    
    /**
     * Get the read content
     * @return string
     */
    public function getReadContent();
}