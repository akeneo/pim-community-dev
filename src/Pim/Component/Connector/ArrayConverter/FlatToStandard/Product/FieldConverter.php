<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard\Product;

use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;

/**
 * Converts a flat field to a structured one
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldConverter
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
        $this->assocFieldResolver  = $assocFieldResolver;
        $this->fieldSplitter       = $fieldSplitter;
        $this->groupTypeRepository = $groupTypeRepository;
    }

    /**
     * Converts a flat field to a structured one
     *
     * @param string $column
     * @param string $value
     *
     * @throws \LogicException
     *
     * @return array
     */
    public function convert($column, $value)
    {
        $associationFields = $this->assocFieldResolver->resolveAssociationColumns();

        if (in_array($column, $associationFields)) {
            $value = $this->fieldSplitter->splitCollection($value);
            list($associationTypeCode, $associatedWith) = $this->fieldSplitter->splitFieldName($column);

            return ['associations' => [$associationTypeCode => [$associatedWith => $value]]];
        } elseif (in_array($column, ['categories'])) {
            return [$column => $this->fieldSplitter->splitCollection($value)];
        } elseif (in_array($column, ['groups'])) {
            return $this->extractVariantGroup($value);
        } elseif ('enabled' === $column) {
            return [$column => (bool) $value];
        } elseif ('family' === $column) {
            return [$column => $value];
        }

        throw new \LogicException(sprintf('No converters found for attribute type "%s"', $column));
    }

    /**
     * @param string $column
     *
     * @return bool
     */
    public function supportsColumn($column)
    {
        $associationFields = $this->assocFieldResolver->resolveAssociationColumns();

        $fields = array_merge(['categories', 'groups', 'enabled', 'family'], $associationFields);

        return in_array($column, $fields);
    }

    /**
     * Extract a variant group from column "groups"
     *
     * @param string $value
     *
     * @return array
     */
    protected function extractVariantGroup($value)
    {
        $data = [];
        $groups = $this->fieldSplitter->splitCollection($value);

        foreach ($groups as $group) {
            $isVariant = $this->groupTypeRepository->getTypeByGroup($group);
            if ('1' === $isVariant) {
                $data['variant_group'][] = $group;
            } else {
                $data['groups'][] = $group;
            }
        }

        if (isset($data['variant_group']) && 1 < count($data['variant_group'])) {
            throw new \InvalidArgumentException(
                sprintf('The product cannot belong to many variant groups: %s', implode(', ', $data['variant_group']))
            );
        } elseif (isset($data['variant_group'])) {
            $data['variant_group'] = current($data['variant_group']);
        }

        return $data;
    }
}
