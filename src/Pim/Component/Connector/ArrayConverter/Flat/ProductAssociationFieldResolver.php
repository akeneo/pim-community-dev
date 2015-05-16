<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;

/**
 * Resolves association field name
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationFieldResolver
{
    /** @var string */
    const GROUP_ASSOCIATION_SUFFIX   = '-groups';

    /** @var string */
    const PRODUCT_ASSOCIATION_SUFFIX = '-products';

    /** @var AssociationTypeRepositoryInterface */
    protected $associationTypeRepo;

    /**
     * @param AssociationTypeRepositoryInterface $repository
     */
    public function __construct(AssociationTypeRepositoryInterface $repository)
    {
        $this->associationTypeRepo = $repository;
    }

    /**
     * Get the association field names
     *
     * @return array
     */
    public function resolveAssociationFields()
    {
        $fieldNames = [];
        $assocTypes = $this->associationTypeRepo->findAll();
        foreach ($assocTypes as $assocType) {
            $fieldNames[] = $assocType->getCode() . self::GROUP_ASSOCIATION_SUFFIX;
            $fieldNames[] = $assocType->getCode() . self::PRODUCT_ASSOCIATION_SUFFIX;
        }

        return $fieldNames;
    }
}
