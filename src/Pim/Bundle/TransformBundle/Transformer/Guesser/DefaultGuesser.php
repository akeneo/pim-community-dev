<?php

namespace Pim\Bundle\TransformBundle\Transformer\Guesser;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Pim\Bundle\TransformBundle\Transformer\Property\PropertyTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;

/**
 * Default guesser
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DefaultGuesser implements GuesserInterface
{
    /**
     * @var PropertyTransformerInterface
     */
    protected $transformer;

    /**
     * Constructor
     *
     * @param PropertyTransformerInterface $transformer
     */
    public function __construct(PropertyTransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformerInfo(ColumnInfoInterface $columnInfo, ClassMetadataInfo $metadata)
    {
        if (!$metadata->hasField($columnInfo->getPropertyPath())) {
            return;
        }

        return array($this->transformer, array());
    }
}
