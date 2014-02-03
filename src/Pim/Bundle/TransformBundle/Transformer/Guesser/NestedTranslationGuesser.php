<?php

namespace Pim\Bundle\TransformBundle\Transformer\Guesser;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Pim\Bundle\TransformBundle\Transformer\Property\PropertyTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;

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

        return array($this->transformer, array('propertyPath' => $mapping[$columnInfo->getName()]));
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
        return array(
            'labels' => 'label'
        );
    }
}
