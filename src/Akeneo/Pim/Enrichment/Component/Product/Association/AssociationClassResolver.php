<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Association;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;

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
     * @param EntityWithAssociationsInterface $entity
     *
     * @return string
     */
    public function resolveAssociationClass(EntityWithAssociationsInterface $entity): string
    {
        foreach ($this->associationClassMap as $className => $associationClassName) {
            if ($entity instanceof $className) {
                return $associationClassName;
            }
        }

        $entityClass = get_class($entity);

        throw new \InvalidArgumentException(sprintf(
            'Cannot find any association class for entity of type "%s"', $entityClass
        ));
    }
}
