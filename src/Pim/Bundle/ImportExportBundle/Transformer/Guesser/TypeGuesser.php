<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Guesser;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface;

/**
 * Guesses the property transformer by inspecting the ORM column type
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TypeGuesser implements GuesserInterface
{
    /**
     * @var PropertyTransformerInterface
     */
    protected $transformer;

    /**
     * @var string
     */
    protected $type;

    /**
     * Constructor
     *
     * @param PropertyTransformerInterface $transformer
     * @param string                       $type
     */
    public function __construct(PropertyTransformerInterface $transformer, $type)
    {
        $this->transformer = $transformer;
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformerInfo(ColumnInfoInterface $columnInfo, ClassMetadataInfo $metadata)
    {
        if (!$metadata->hasField($columnInfo->getPropertyPath()) ||
            $this->type != $metadata->getTypeOfField($columnInfo->getPropertyPath())) {
            return;
        }

        return [$this->transformer, []];
    }
}
