<?php

namespace Pim\Bundle\TransformBundle\Transformer\Guesser;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\TransformBundle\Transformer\Property\PropertyTransformerInterface;

/**
 * Guesses transformer by inspecting linked attribute
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.6
 */
class AttributeGuesser implements GuesserInterface
{
    /**
     * @var PropertyTransformerInterface
     */
    protected $transformer;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $backendType;

    /**
     * Constructor
     *
     * @param PropertyTransformerInterface $transformer
     * @param string                       $class
     * @param string                       $backendType
     */
    public function __construct(
        PropertyTransformerInterface $transformer,
        $class,
        $backendType
    ) {
        $this->transformer = $transformer;
        $this->class = $class;
        $this->backendType = $backendType;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformerInfo(ColumnInfoInterface $columnInfo, ClassMetadata $metadata)
    {
        if ($this->class !== $metadata->getName() || !$columnInfo->getAttribute() ||
            $this->backendType !== $columnInfo->getAttribute()->getBackendType()
        ) {
            return;
        }

        return [$this->transformer, []];
    }
}
