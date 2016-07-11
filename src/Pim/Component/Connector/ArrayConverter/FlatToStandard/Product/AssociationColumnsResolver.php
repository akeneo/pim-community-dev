<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard\Product;

use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;

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
        $this->assocFieldsCache = [];
    }

    /**
     * Get the association field names
     *
     * @return array
     */
    public function resolveAssociationColumns()
    {
        if (empty($this->assocFieldsCache)) {
            $fieldNames = [];
            $assocTypes = $this->assocTypeRepository->findAll();
            foreach ($assocTypes as $assocType) {
                $fieldNames[] = $assocType->getCode() . self::GROUP_ASSOCIATION_SUFFIX;
                $fieldNames[] = $assocType->getCode() . self::PRODUCT_ASSOCIATION_SUFFIX;
            }
            $this->assocFieldsCache = $fieldNames;
        }

        return $this->assocFieldsCache;
    }
}
