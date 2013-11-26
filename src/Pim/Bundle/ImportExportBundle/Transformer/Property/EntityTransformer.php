<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Pim\Bundle\ImportExportBundle\Cache\EntityCache;

/**
 * Transform entity codes in entity arrays
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityTransformer extends AbstractAssociationTransformer
{
    /**
     * @var EntityCache
     */
    protected $entityCache;

    /**
     * Constructor
     *
     * @param EntityCache $entityCache
     */
    public function __construct(EntityCache $entityCache)
    {
        $this->entityCache = $entityCache;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity($class, $value)
    {
        return $this->entityCache->find($class, $value);
    }
}
