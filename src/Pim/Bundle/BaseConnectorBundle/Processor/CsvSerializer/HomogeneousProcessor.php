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
        $parameters = $this->stepExecution->getJobParameters();
        return $this->serializer->serialize(
            $item,
            'csv',
            [
                'delimiter'     => $parameters->get('delimiter'),
                'enclosure'     => $parameters->get('enclosure'),
                'withHeader'    => $parameters->get('withHeader'),
                'heterogeneous' => false,
                'locales'       => $this->localeRepository->getActivatedLocaleCodes(),
            ]
        );
    }
}
