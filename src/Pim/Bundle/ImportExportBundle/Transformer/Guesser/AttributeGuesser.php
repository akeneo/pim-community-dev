<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Guesser;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface;
use Pim\Bundle\ImportExportBundle\Cache\AttributeCache;
use Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface;

/**
 * Description of AttributeBackendTypeGuesser
 *
 * @author Antoine Guigan <aguigan@qimnet.com>
 */
class AttributeGuesser implements GuesserInterface
{
    /**
     * @var PropertyTransformerInterface
     */
    protected $transformer;

    /**
     * @var AttributeCache
     */
    protected $attributeCache;

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
     * @param AttributeCache               $attributeCache
     * @param string                       $class
     * @param string                       $backendType
     */
    public function __construct(
        PropertyTransformerInterface $transformer,
        AttributeCache $attributeCache,
        $class,
        $backendType
    ) {
        $this->transformer = $transformer;
        $this->attributeCache = $attributeCache;
        $this->class = $class;
        $this->backendType = $backendType;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformerInfo(array $columnInfo, ClassMetadataInfo $metadata)
    {
        if ($this->class !== $metadata->getName()) {
            return;
        }

        $attribute = $this->attributeCache->getAttribute($columnInfo['name']);
        if ($this->backendType !== $attribute->getBackendType()) {
            return;
        }

        return array($this->transformer, array('attribute' => $attribute));
    }
}
