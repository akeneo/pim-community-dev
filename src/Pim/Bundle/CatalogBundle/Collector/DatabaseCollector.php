<?php

namespace Pim\Bundle\CatalogBundle\Collector;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Pim\Bundle\CatalogBundle\VersionProviderInterface;
use Pim\Component\Catalog\Repository\ProductValueCounterRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * This class adds an entry in the Symfony debug toolbar to display:
 * - whether mongoDB is installed or not
 * - if not, if it should or not
 *
 * @author    Remy Betus <remy.betus@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DatabaseCollector extends DataCollector
{
    /** @var int the max number of product value allowed before having to switch to MongoBD */
    const MYSQL_PRODUCT_VALUE_LIMIT = 5000000;

    /** @var ProductValueCounterRepositoryInterface */
    protected $productValueRepository;

    /** @var string */
    protected $storageDriver;

    /** @var VersionProviderInterface */
    protected $versionProvider;

    /** @var  array */
    protected $data;

    /**
     * @param VersionProviderInterface               $versionProvider
     * @param ProductValueCounterRepositoryInterface $productValueRepository
     * @param string                                 $storageDriver
     */
    public function __construct(
        VersionProviderInterface $versionProvider,
        ProductValueCounterRepositoryInterface $productValueRepository,
        $storageDriver
    ) {
        $this->productValueRepository = $productValueRepository;
        $this->versionProvider = $versionProvider;
        $this->storageDriver = $storageDriver;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'mongodb_enabled'     => $this->isMongoDbEnabled(),
            'require_mongodb'     => !($this->isMongoDbEnabled() || !$this->isMongoDbRequired()),
            'product_value_count' => $this->isMongoDbEnabled() ? null : $this->getProductValueCount(),
            'version'             => [
                'patch' => $this->versionProvider->getPatch(),
                'minor' => $this->versionProvider->getMinor()
            ]
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
        return AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM === $this->storageDriver;
    }

    /**
     * Checks if MongoDB is required or not.
     *
     * @return bool
     */
    public function isMongoDbRequired()
    {
        return ($this->getProductValueCount() > static::MYSQL_PRODUCT_VALUE_LIMIT);
    }

    /**
     * @return int
     */
    public function getProductValueCount()
    {
        return $this->productValueRepository->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'database';
    }
}
