<?php

namespace Pim\Bundle\EntityBundle\ORM;

use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use Oro\Bundle\EntityBundle\ORM\EntityClassResolver as BaseEntityClassResolver;

/**
 * This class allows to get the real class name of an entity by its name
 *
 * Override Oro EntityClassResolver to be able to retrieve entity managed in ODM.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityClassResolver extends BaseEntityClassResolver
{
    /**
     * Injection of the SmartManagerRegistry instead of the regular Doctrine ManagerRegistry
     *
     * @var SmartManagerRegistry
     */
    protected $doctrine;

    /**
     * Constructor
     *
     * @param SmartManagerRegistry $doctrine
     */
    public function __construct(SmartManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }
}
