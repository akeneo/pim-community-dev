<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Structure\Component\Model\AssociationType;
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
    const GROUP_ASSOCIATION_SUFFIX = '-groups';

    /** @var string */
    const PRODUCT_ASSOCIATION_SUFFIX = '-products';

    /** @var string */
    const PRODUCT_MODEL_ASSOCIATION_SUFFIX = '-product_models';

    /** @var string */
    const QUANTITY_SUFFIX = '-quantity';

    /** @var AssociationTypeRepositoryInterface */
    protected $assocTypeRepository;

    /** @var array */
    protected $assocFieldsCache;

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
            $assocTypes = $this->assocTypeRepository->findAll();
            /** @var AssociationType $assocType */
            foreach ($assocTypes as $assocType) {
                if (!$assocType->isQuantified()) {
                    $fieldNames[] = $assocType->getCode() . self::GROUP_ASSOCIATION_SUFFIX;
                    $fieldNames[] = $assocType->getCode() . self::PRODUCT_ASSOCIATION_SUFFIX;
                    $fieldNames[] = $assocType->getCode() . self::PRODUCT_MODEL_ASSOCIATION_SUFFIX;
                }
            }
            $this->assocFieldsCache = $fieldNames;
        }

        return $this->assocFieldsCache;
    }

    /**
     * Get the association field names
     */
    public function resolveQuantifiedAssociationColumns(): array
    {
        if (null === $this->assocFieldsCache) {
            $fieldNames = [];
            $assocTypes = $this->assocTypeRepository->findAll();
            /** @var AssociationType $assocType */
            foreach ($assocTypes as $assocType) {
                if ($assocType->isQuantified()) {
                    $fieldNames[] = $assocType->getCode() . self::PRODUCT_ASSOCIATION_SUFFIX;
                    $fieldNames[] = $assocType->getCode() . self::PRODUCT_ASSOCIATION_SUFFIX . self::QUANTITY_SUFFIX;
                    $fieldNames[] = $assocType->getCode() . self::PRODUCT_MODEL_ASSOCIATION_SUFFIX;
                    $fieldNames[] = $assocType->getCode() . self::PRODUCT_MODEL_ASSOCIATION_SUFFIX . self::QUANTITY_SUFFIX;;
                }
            }
            $this->assocFieldsCache = $fieldNames;
        }

        return $this->assocFieldsCache;
    }
}
