<?php

namespace Pim\Bundle\InstallerBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Fixture loader event
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FixtureLoaderEvent extends Event
{
    /**
     * @var string
     */
    protected $file;

    /**
     * Constructor
     * 
     * @param string $file
     * @param boolean $completed
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Returns the path of the loaded fixture file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }
}
