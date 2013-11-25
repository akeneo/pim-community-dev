<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Guesser;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Interface for property guesser
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GuesserInterface
{
    /**
     * Guess the transformer for a column label
     *
     * Returns an array containing the PropertyTransformer at first position and its options at second position
     * or null if the column is not supported.
     *
     * @param array             $columnInfo
     * @param ClassMetadataInfo $metadata
     *
     * @return array
     */
    public function getTransformerInfo(array $columnInfo, ClassMetadataInfo $metadata);
}
