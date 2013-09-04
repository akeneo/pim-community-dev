<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Pim\Bundle\BatchBundle\Item\ItemReaderInterface;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;
use Pim\Bundle\BatchBundle\Entity\StepExecution;

/**
 * ORM reader
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMReader extends AbstractConfigurableStepElement implements ItemReaderInterface
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
    public function read(StepExecution $stepExecution)
    {
        if (!$this->executed) {
            $this->executed = true;

            $result = $this->query->execute();
            $stepExecution->setReadCount(count($result));

            return $result;
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
