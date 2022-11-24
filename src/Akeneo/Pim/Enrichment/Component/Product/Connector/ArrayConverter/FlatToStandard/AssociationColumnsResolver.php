<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;

/**
 * Resolves association columns
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationColumnsResolver
{
    /** @var string */
    public const GROUP_ASSOCIATION_SUFFIX = '-groups';

    /** @var string */
    public const PRODUCT_ASSOCIATION_SUFFIX = '-products';

    /** @var string */
    public const PRODUCT_UUID_ASSOCIATION_SUFFIX = '-product_uuids';

    /** @var string */
    public const PRODUCT_MODEL_ASSOCIATION_SUFFIX = '-product_models';

    /** @var string */
    public const QUANTITY_SUFFIX = '-quantity';

    /** @var AssociationTypeRepositoryInterface */
    protected $assocTypeRepository;

    /** @var array */
    protected $assocFieldsCache;

    /** @var array */
    protected $associationTypesCache = null;

    /** @var array */
    protected $quantifiedAssocIdentifierFieldsCache;

    /** @var array */
    protected $quantifiedAssocQuantityFieldsCache;

    /**
     * @param AssociationTypeRepositoryInterface $repository
     */
    public function __construct(AssociationTypeRepositoryInterface $repository)
    {
        $this->assocTypeRepository = $repository;
    }

    /**
     * Get the association field names
     */
    public function resolveAssociationColumns(): array
    {
        if (null === $this->assocFieldsCache) {
            $fieldNames = [];
            $assocTypes = $this->getAllAssociationTypes();
            foreach ($assocTypes as $assocType) {
                if (!$assocType->isQuantified()) {
                    $fieldNames[] = $assocType->getCode() . self::GROUP_ASSOCIATION_SUFFIX;
                    $fieldNames[] = $assocType->getCode() . self::PRODUCT_ASSOCIATION_SUFFIX;
                    $fieldNames[] = $assocType->getCode() . self::PRODUCT_UUID_ASSOCIATION_SUFFIX;
                    $fieldNames[] = $assocType->getCode() . self::PRODUCT_MODEL_ASSOCIATION_SUFFIX;
                }
            }
            $this->assocFieldsCache = $fieldNames;
        }

        return $this->assocFieldsCache;
    }

    /**
     * Get the quantified association field names
     */
    public function resolveQuantifiedAssociationColumns(): array
    {
        return array_merge($this->resolveQuantifiedIdentifierAssociationColumns(), $this->resolveQuantifiedQuantityAssociationColumns());
    }

    /**
     * Get the quantified association quantity field names
     */
    public function resolveQuantifiedQuantityAssociationColumns(): array
    {
        if (null === $this->quantifiedAssocQuantityFieldsCache) {
            $fieldNames = [];
            $assocTypes = $this->getAllAssociationTypes();
            foreach ($assocTypes as $assocType) {
                if ($assocType->isQuantified()) {
                    $fieldNames[] = $assocType->getCode() . self::PRODUCT_ASSOCIATION_SUFFIX . self::QUANTITY_SUFFIX;
                    $fieldNames[] = $assocType->getCode() . self::PRODUCT_MODEL_ASSOCIATION_SUFFIX . self::QUANTITY_SUFFIX;
                }
            }
            $this->quantifiedAssocQuantityFieldsCache = $fieldNames;
        }

        return $this->quantifiedAssocQuantityFieldsCache;
    }

    /**
     * Get the quantified association identifier field names
     */
    public function resolveQuantifiedIdentifierAssociationColumns(): array
    {
        if (null === $this->quantifiedAssocIdentifierFieldsCache) {
            $fieldNames = [];
            $assocTypes = $this->getAllAssociationTypes();
            foreach ($assocTypes as $assocType) {
                if ($assocType->isQuantified()) {
                    $fieldNames[] = $assocType->getCode() . self::PRODUCT_ASSOCIATION_SUFFIX;
                    $fieldNames[] = $assocType->getCode() . self::PRODUCT_MODEL_ASSOCIATION_SUFFIX;
                }
            }
            $this->quantifiedAssocIdentifierFieldsCache = $fieldNames;
        }

        return $this->quantifiedAssocIdentifierFieldsCache;
    }

    private function getAllAssociationTypes(): array
    {
        if (null === $this->associationTypesCache) {
            $this->associationTypesCache = $this->assocTypeRepository->findAll();
        }

        return $this->associationTypesCache;
    }
}
