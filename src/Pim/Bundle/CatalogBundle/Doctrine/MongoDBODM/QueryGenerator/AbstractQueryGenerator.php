<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\NamingUtility;

/**
* Abstract query generator
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractQueryGenerator implements NormalizedDataQueryGeneratorInterface
{
    /** @var NamingUtility */
    protected $namingUtility;

    /** @var string */
    protected $entityClass;

    /** @var string */
    protected $field;

    /**
     * @param NamingUtility $namingUtility
     * @param string                 $entityClass
     * @param string                 $field
     */
    public function __construct(
        NamingUtility $namingUtility,
        $entityClass,
        $field = ''
    ) {
        $this->namingUtility = $namingUtility;
        $this->entityClass   = $entityClass;
        $this->field         = $field;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity, $field)
    {
        return $entity instanceof $this->entityClass && $field === $this->field;
    }
}
