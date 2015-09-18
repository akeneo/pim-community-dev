<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Collector;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Version;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * This class adds an entry in the Symfony2 debug toolbar to display:
 * - whether mongoDB is installed or not
 * - if not, if it should or not
 *
 * @author    Rémy Bétus <remy.betus@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DatabaseCollector extends DataCollector
{
    /** @var int the max number of product value allowed before having to switch to MongoBD */
    const MYSQL_PRODUCT_VALUE_LIMIT = 5000000;

    /** @var EntityRepository */
    protected $entityManager;

    /** @var string */
    protected $storageDriver;

    /**
     * @param EntityManager $entityManager
     * @param string        $storageDriver
     */
    public function __construct(EntityManager $entityManager, $storageDriver)
    {
        $this->entityManager = $entityManager;
        $this->storageDriver = $storageDriver;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'mongodb_enabled' => $this->isMongoDbEnabled(),
            'version' => Version::VERSION,
            'require_mongodb' => (!$this->isMongoDbEnabled()) ? $this->isMongoDbRequired() : false,
        ];
    }

    /**
     * @return bool
     */
    public function hasMongoDb()
    {
        return $this->data['mongodb_enabled'];
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->data['version'];
    }

    /**
     * Tells whether the PIM is in warning state or not (i.e: needs operations to be performed)
     *
     * @return bool
     */
    public function isWarning()
    {
        return (
            !$this->data['mongodb_enabled'] && $this->data['require_mongodb']
        );
    }

    /**
     * @return bool
     */
    public function requireMongoDb()
    {
        return $this->data['require_mongodb'];
    }

    /**
     * @return bool
     */
    public function isMongoDbEnabled()
    {
        return 'doctrine/mongodb-odm' === $this->storageDriver;
    }

    /**
     * Checks if MongoDB is required or not.
     *
     * @return bool
     */
    public function isMongoDbRequired()
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('count(product_value.id)');
        $qb->from('PimCatalogBundle:Category', 'product_value');
        $productValueCount = $qb->getQuery()->getSingleScalarResult();

        return ($productValueCount > self::MYSQL_PRODUCT_VALUE_LIMIT);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'database';
    }
}
