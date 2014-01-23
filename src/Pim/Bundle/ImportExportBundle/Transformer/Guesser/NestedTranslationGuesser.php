<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Guesser;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Pim\Bundle\ImportExportBundle\Transformer\Property\PropertyTransformerInterface;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface;

/**
 * Translation guesser
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NestedTranslationGuesser implements GuesserInterface
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
        $mapping = $this->getMapping();
        if (!$metadata->hasAssociation('translations') || !isset($mapping[$columnInfo->getName()])) {
            return null;
        }

        return [$this->transformer, ['propertyPath' => $mapping[$columnInfo->getName()]]];
    }

    /**
     * Returns an array of translated fields
     *
     * The keys of the array correspond to the name of the collection (plural).
     * The values of the array correspond to the name of the property
     *
     * @return array
     */
    protected function getMapping()
    {
        return [
            'labels' => 'label'
        ];
    }
}
