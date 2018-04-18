<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\Association;

use InvalidArgumentException;
use Pim\Component\Catalog\Model\AssociationAwareInterface;

/**
 * For a given association aware entity,
 * this resolver will give you the corresponding association class.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com> (et JM un peu)
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationClassResolver
{
    /** @var string[] */
    private $associationClassMap;

    /**
     * @param string[] $associationClassMap
     */
    public function __construct(array $associationClassMap)
    {
        $this->associationClassMap = $associationClassMap;
    }

    /**
     * @param AssociationAwareInterface $entity
     *
     * @return string
     */
    public function resolveAssociationClass(AssociationAwareInterface $entity): string
    {
        $entityClass = get_class($entity);

        if (!isset($this->associationClassMap[$entityClass])) {
            throw new InvalidArgumentException(sprintf(
                'Cannot find any association class for entity of type "%s"', $entityClass
            ));
        }

        return $this->associationClassMap[$entityClass];
    }
}
