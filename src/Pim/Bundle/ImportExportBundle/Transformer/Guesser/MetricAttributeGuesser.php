<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Guesser;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface;

/**
 * Guesses transformer by inspecting linked attribute
 * and passes attribute metric family as option
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricAttributeGuesser extends AttributeGuesser
{
    /**
     * {@inheritdoc}
     */
    public function getTransformerInfo(ColumnInfoInterface $columnInfo, ClassMetadataInfo $metadata)
    {
        if ($this->class !== $metadata->getName() || !$columnInfo->getAttribute() ||
            $this->backendType !== $columnInfo->getAttribute()->getBackendType()
        ) {
            return;
        }

        return array($this->transformer, array('family' => $columnInfo->getAttribute()->getMetricFamily()));
    }
}
