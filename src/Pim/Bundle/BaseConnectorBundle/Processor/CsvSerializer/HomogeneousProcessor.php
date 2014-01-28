<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer;

/**
 * Serialize homogeneous data into csv
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HomogeneousProcessor extends Processor
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $nbItems = count($item) - ($this->isWithHeader() ? 1 : 0);
        $this->stepExecution->addSummaryInfo('write', $nbItems);

        return $this->serializer->serialize(
            $item,
            'csv',
            array(
                'delimiter'     => $this->delimiter,
                'enclosure'     => $this->enclosure,
                'withHeader'    => $this->withHeader,
                'heterogeneous' => false,
                'locales'       => $this->localeManager->getActiveCodes(),
            )
        );
    }
}
