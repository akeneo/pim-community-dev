<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\NamingUtility;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Option value updated query generator
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultipleOptionValueUpdatedQueryGenerator extends AbstractQueryGenerator
{
    protected $attributeOptionNormalizer;

    public function __construct(
        NamingUtility $namingUtility,
        $entityClass,
        $field,
        NormalizerInterface $attributeOptionNormalizer
    ) {
        $this->attributeOptionNormalizer = $attributeOptionNormalizer;

        parent::__construct($namingUtility, $entityClass, $field);
    }

    /**
     * {@inheritdoc}
     */
    public function generateQuery($entity, $field, $oldValue, $newValue)
    {
        $attributeNormFields = $this->namingUtility->getAttributeNormFields(
            $entity->getOption()->getAttribute()
        );

        $queries = [];
        $attributeOptionNormalized = $this->attributeOptionNormalizer->normalize($entity->getOption());

        foreach ($attributeNormFields as $attributeNormField) {
            $optionId = (int) $entity->getOption()->getId();
            $optionCode = $entity->getOption()->getCode();

            $queries[] = [
                [
                    '$and' => [
                        ['values.optionIds' => $optionId],
                        [$attributeNormField => ['$elemMatch' => ['code' => $optionCode]]]
                    ]
                ],
                ['$push' => [$attributeNormField => $attributeOptionNormalized]],
                ['multiple' => true],
            ];

            $queries[] = [
                ['values.optionIds' => $optionId],
                [
                    '$pull' => [
                        $attributeNormField => [
                            '$and' => [
                                ['code' => $optionCode],
                                [sprintf('optionValues.%s.value', $entity->getLocale()) => $oldValue]
                            ]
                        ]
                    ]
                ],
                ['multiple' => true],
            ];
        }

        return $queries;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity, $field)
    {
        return parent::supports($entity, $field) &&
            AttributeTypes::OPTION_MULTI_SELECT === $entity->getOption()->getAttribute()->getAttributeType();
    }
}
