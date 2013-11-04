<?php

namespace Pim\Bundle\ImportExportBundle\Writer;

use Pim\Bundle\VersioningBundle\EventListener\AddVersionListener;

/**
 * Configures ORM Writer services
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmWriterConfigurator
{
    /**
     * @var AddVersionListener
     */
    protected $addVersionListener;

    /**
     * Constructor
     *
     * @param AddVersionListener $addVersionListener
     */
    public function __construct(AddVersionListener $addVersionListener)
    {
        $this->addVersionListener = $addVersionListener;
    }

    /**
     * Configures PIM form imports
     */
    public function configure()
    {
        $this->addVersionListener->setRealTimeVersioning(false);
    }
}
