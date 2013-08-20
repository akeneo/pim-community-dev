<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\BatchBundle\Model\Reader;

/**
 * ORM reader
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMReader extends Reader
{
    protected $query;
    private $executed = false;

    /**
     * Set query used by the reader
     * @param AbstractQuery $query
     */
    public function setQuery(AbstractQuery $query)
    {
        $this->query = $query;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        if (!$this->executed) {
            $this->executed = true;

            return $this->query->execute();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }
}
