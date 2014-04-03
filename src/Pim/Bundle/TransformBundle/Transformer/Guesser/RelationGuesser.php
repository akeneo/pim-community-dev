<?php

namespace Pim\Bundle\TransformBundle\Transformer\Guesser;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Entity\Repository\ReferableEntityRepositoryInterface;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\TransformBundle\Transformer\Property\PropertyTransformerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo as ORMClassMetadataInfo;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo as ODMMongoDBClassMetadataInfo;

/**
 * Guesser for entity transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RelationGuesser implements GuesserInterface
{
    /**
     * @var PropertyTransformerInterface
     */
    protected $transformer;

    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * Constructor
     *
     * @param PropertyTransformerInterface $transformer
     * @param RegistryInterface            $doctrine
     */
    public function __construct(PropertyTransformerInterface $transformer, ManagerRegistry $doctrine)
    {
        $this->transformer = $transformer;
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformerInfo(ColumnInfoInterface $columnInfo, ClassMetadata $metadata)
    {
        if ($metadata instanceof ORMClassMetadataInfo) {
            return $this->getORMTransformerInfo($columnInfo, $metadata);
        }

        if ($metadata instanceof ODMMongoDBClassMetadataInfo) {
            return $this->getODMTransformerInfo($columnInfo, $metadata);
        }
    }

    /**
     * @param ColumnInfoInterface  $columnInfo
     * @param ORMClassMetadataInfo $metadata
     *
     * @return array
     */
    private function getORMTransformerInfo(ColumnInfoInterface $columnInfo, ORMClassMetadataInfo $metadata)
    {
        if (!$metadata->hasAssociation($columnInfo->getPropertyPath())) {
            return;
        }

        $mapping = $metadata->getAssociationMapping($columnInfo->getPropertyPath());
        if (!($this->doctrine->getRepository($mapping['targetEntity']) instanceof ReferableEntityRepositoryInterface)) {
            return;
        }

        return array(
            $this->transformer,
            array(
                'class'    => $mapping['targetEntity'],
                'multiple' => (ORMClassMetadataInfo::MANY_TO_MANY === $mapping['type'])
            )
        );
    }

    /**
     * @param ColumnInfoInterface         $columnInfo
     * @param ODMMongoDBClassMetadataInfo $metadata
     *
     * @return array
     */
    private function getODMTransformerInfo(ColumnInfoInterface $columnInfo, ODMMongoDBClassMetadataInfo $metadata)
    {
        $fieldName = $columnInfo->getPropertyPath();

        if (in_array($metadata->getTypeOfField($fieldName), ['entity', 'entities'])) {
            $mapping = $metadata->getFieldMapping($fieldName);
            $target = $mapping['targetEntity'];

            if (!$this->doctrine->getRepository($target) instanceof ReferableEntityRepositoryInterface) {
                return;
            }

            return array(
                $this->transformer,
                array(
                    'class'    => $target,
                    'multiple' => 'entities' === $metadata->getTypeOfField($fieldName)
                )
            );
        }

        if (in_array($metadata->getTypeOfField($fieldName), ['one', 'many'])) {
            $mapping = $metadata->getFieldMapping($fieldName);
            $target = $mapping['targetDocument'];

            // TODO Remove this hack
            switch ($target) {
                case 'Pim\Bundle\CatalogBundle\Model\ProductPrice':
                case 'Pim\Bundle\CatalogBundle\Model\Metric':
                    return;
            }

            if (!$this->doctrine->getRepository($target) instanceof ReferableEntityRepositoryInterface) {
                return;
            }

            return array(
                $this->transformer,
                array(
                    'class'    => $mapping['targetDocument'],
                    'multiple' => 'many' === $metadata->getTypeOfField($fieldName)
                )
            );
        }
    }
}
