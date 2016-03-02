<?php

namespace Pim\Bundle\TransformBundle\Transformer\Guesser;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadataInfo as ODMMongoDBClassMetadataInfo;
use Doctrine\ORM\Mapping\ClassMetadataInfo as ORMClassMetadataInfo;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;
use Pim\Bundle\TransformBundle\Transformer\Property\PropertyTransformerInterface;

/**
 * Guesser for entity transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.6
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
     * @param ManagerRegistry              $doctrine
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
        if (!$this->isRepositoryEligible($this->doctrine->getRepository($mapping['targetEntity']))) {
            return;
        }

        return [
            $this->transformer,
            [
                'class'    => $mapping['targetEntity'],
                'multiple' => (ORMClassMetadataInfo::MANY_TO_MANY === $mapping['type'])
            ]
        ];
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

            if (!$this->isRepositoryEligible($this->doctrine->getRepository($target))) {
                return;
            }

            return [
                $this->transformer,
                [
                    'class'    => $target,
                    'multiple' => 'entities' === $metadata->getTypeOfField($fieldName)
                ]
            ];
        }

        if (in_array($metadata->getTypeOfField($fieldName), ['one', 'many'])) {
            $mapping = $metadata->getFieldMapping($fieldName);
            $target = $mapping['targetDocument'];

            // TODO Remove this hack
            switch ($target) {
                case 'Pim\Component\Catalog\Model\ProductPrice':
                case 'Pim\Component\Catalog\Model\Metric':
                    return;
            }

            if (!$this->isRepositoryEligible($this->doctrine->getRepository($target))) {
                return;
            }

            return [
                $this->transformer,
                [
                    'class'    => $mapping['targetDocument'],
                    'multiple' => 'many' === $metadata->getTypeOfField($fieldName)
                ]
            ];
        }
    }

    /**
     * @param $repository
     *
     * @return bool
     */
    protected function isRepositoryEligible($repository)
    {
        if ($repository instanceof IdentifiableObjectRepositoryInterface) {
            return true;
        }

        return false;
    }
}
