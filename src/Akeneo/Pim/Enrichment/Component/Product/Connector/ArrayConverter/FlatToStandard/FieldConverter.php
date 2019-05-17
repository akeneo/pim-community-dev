<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface;

/**
 * Converts a flat product field to a structured format
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldConverter implements FieldConverterInterface
{
    /** @var AssociationColumnsResolver */
    protected $assocFieldResolver;

    /** @var FieldSplitter */
    protected $fieldSplitter;

    /** @var GroupTypeRepositoryInterface */
    protected $groupTypeRepository;

    /**
     * @param FieldSplitter                $fieldSplitter
     * @param AssociationColumnsResolver   $assocFieldResolver
     * @param GroupTypeRepositoryInterface $groupTypeRepository
     */
    public function __construct(
        FieldSplitter $fieldSplitter,
        AssociationColumnsResolver $assocFieldResolver,
        GroupTypeRepositoryInterface $groupTypeRepository
    ) {
        $this->assocFieldResolver = $assocFieldResolver;
        $this->fieldSplitter = $fieldSplitter;
        $this->groupTypeRepository = $groupTypeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(string $fieldName, $value): ConvertedField
    {
        $associationFields = $this->assocFieldResolver->resolveAssociationColumns();

        if (in_array($fieldName, $associationFields)) {
            $value = $this->fieldSplitter->splitCollection($value);
            list($associationTypeCode, $associatedWith) = $this->fieldSplitter->splitFieldName($fieldName);

            return new ConvertedField('associations', [$associationTypeCode => [$associatedWith => $value]]);
        } elseif (in_array($fieldName, ['categories'])) {
            $categories = $this->fieldSplitter->splitCollection($value);

            return new ConvertedField($fieldName, $categories);
        } elseif (in_array($fieldName, ['groups'])) {
            return $this->extractGroup($value);
        } elseif ('enabled' === $fieldName) {
            return new ConvertedField($fieldName, (bool) $value);
        } elseif (in_array($fieldName, ['family', 'parent'])) {
            return new ConvertedField($fieldName, $value);
        }

        throw new \LogicException(sprintf('No converters found for attribute type "%s"', $fieldName));
    }

    /**
     * @param string $column
     *
     * @return bool
     */
    public function supportsColumn($column): bool
    {
        $associationFields = $this->assocFieldResolver->resolveAssociationColumns();

        $fields = array_merge(['categories', 'groups', 'enabled', 'family', 'parent'], $associationFields);

        return in_array($column, $fields);
    }

    /**
     * Extract a variant group from column "groups"
     *
     * @param string $value
     *
     * @return ConvertedField
     */
    protected function extractGroup($value): ConvertedField
    {
        $productGroups = $this->fieldSplitter->splitCollection($value);

        return new ConvertedField('groups', $productGroups);
    }
}
