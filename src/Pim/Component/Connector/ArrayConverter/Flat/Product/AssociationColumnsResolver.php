<?php

namespace Pim\Component\Connector\ArrayConverter\Flat\Product;

use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;

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
    const GROUP_ASSOCIATION_SUFFIX   = '-groups';

    /** @var string */
    const PRODUCT_ASSOCIATION_SUFFIX = '-products';

    /** @var AssociationTypeRepositoryInterface */
    protected $associationTypeRepo;

    /** @var array */
    protected $associationFieldsCache;

    /**
     * @param AssociationTypeRepositoryInterface $repository
     */
    public function __construct(AssociationTypeRepositoryInterface $repository)
    {
        $this->associationTypeRepo = $repository;
        $this->associationFieldsCache = [];
    }

    /**
     * Get the association field names
     *
     * @return array
     */
    public function resolveAssociationColumns()
    {
        if (empty($this->associationFieldsCache)) {
            $fieldNames = [];
            $assocTypes = $this->associationTypeRepo->findAll();
            foreach ($assocTypes as $assocType) {
                $fieldNames[] = $assocType->getCode() . self::GROUP_ASSOCIATION_SUFFIX;
                $fieldNames[] = $assocType->getCode() . self::PRODUCT_ASSOCIATION_SUFFIX;
            }
            $this->associationFieldsCache = $fieldNames;
        }

        return $this->associationFieldsCache;
    }
}
