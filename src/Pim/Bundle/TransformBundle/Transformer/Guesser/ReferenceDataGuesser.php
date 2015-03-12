<?php

namespace Pim\Bundle\TransformBundle\Transformer\Guesser;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\TransformBundle\Transformer\Property\PropertyTransformerInterface;

/**
 * Reference data guesser
 *
 * @author    Julien Janvier <jjanvier@gmail.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataGuesser extends RelationGuesser
{
    /** @var string */
    protected $valueClass;

    /**
     * Constructor
     *
     * @param PropertyTransformerInterface $transformer
     * @param ManagerRegistry              $doctrine
     * @param string                       $valueClass
     */
    public function __construct(PropertyTransformerInterface $transformer, ManagerRegistry $doctrine, $valueClass)
    {
        $this->valueClass = $valueClass;
        parent::__construct($transformer, $doctrine);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformerInfo(ColumnInfoInterface $columnInfo, ClassMetadata $metadata)
    {
        if ($this->valueClass !== $metadata->getName() ||
            !in_array($columnInfo->getPropertyPath(), ['reference_data_option', 'reference_data_options'])
        ) {
            return;
        }

        $referenceDataName = $columnInfo->getAttribute()->getReferenceDataName();
        $columnInfo->setPropertyPath($referenceDataName);

        return parent::getTransformerInfo($columnInfo, $metadata);
    }
}
