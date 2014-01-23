<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Guesser;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Attribute option guesser
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionGuesser extends RelationGuesser
{
    /**
     * @var string
     */
    protected $valueClass = '';

    /**
     * Constructor
     *
     * @param PropertyTransformerInterface $transformer
     * @param RegistryInterface            $doctrine
     * @param string                       $valueClass
     */
    public function __construct(PropertyTransformerInterface $transformer, RegistryInterface $doctrine, $valueClass)
    {
        $this->valueClass = $valueClass;
        parent::__construct($transformer, $doctrine);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformerInfo(ColumnInfoInterface $columnInfo, ClassMetadataInfo $metadata)
    {
        if ($this->valueClass != $metadata->getName() ||
            !in_array($columnInfo->getPropertyPath(), array('option', 'options'))
        ) {
            return;
        }
        $info = parent::getTransformerInfo($columnInfo, $metadata);

        if ($info) {
            list($transformer, $options) = $info;
            $options['reference_prefix'] = $columnInfo->getName();

            return array($transformer, $options);
        }

        return null;
    }
}
