<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * Product datasource, allows to prepare query builder from repository
 *
 * The query builder is built from the object repository (entity or document)
 * The extensions are common or orm/odm specific
 *
 * TODO :
 * - The storage can be configured (orm or mongodb-odm)
 * - Delegate the hydration as grid results
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductDatasource implements DatasourceInterface
{
    /**
     * @var string
     */
    const TYPE = 'pim_product';

    /**
     * @var string
     */
    const ENTITY_PATH = '[source][entity]';

    /**
     * @var string
     */
    const DISPLAYED_ATTRIBUTES_PATH = '[source][displayed_attribute_ids]';

    /**
     * @var string
     */
    const USEABLE_ATTRIBUTES_PATH = '[source][attributes_configuration]';

    /**
     * @var mixed can be Doctrine\ORM\QueryBuilder or Doctrine\ODM\MongoDB\Query\Builder
     * */
    protected $qb;

    /** @var ObjectManager */
    protected $om;

    /** @var HydratorInterface */
    protected $hydrator;

    /** @var array grid configuration */
    protected $configuration;

    /** @var string */
    protected $localeCode = null;

    /**
     * @param ObjectManager     $om
     * @param HydratorInterface $hydrator
     */
    public function __construct(ObjectManager $om, HydratorInterface $hydrator)
    {
        $this->om       = $om;
        $this->hydrator = $hydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function process(DatagridInterface $grid, array $config)
    {
        $this->configuration = $config;

        if (!isset($config['entity'])) {
            throw new \Exception(get_class($this).' expects to be configured with entity');
        }

        $entity = $config['entity'];
        $repository = $this->om->getRepository($entity);

        if (isset($config['repository_method']) && $method = $config['repository_method']) {
            $this->qb = $repository->$method();
        } else {
            $this->qb = $repository->createQueryBuilder('o');
        }

        $localeKey = ContextConfigurator::DISPLAYED_LOCALE_KEY;
        $this->localeCode = isset($config[$localeKey]) ? $config[$localeKey] : null;

        $grid->setDatasource(clone $this);
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $options = [
            'locale_code'              => $this->localeCode,
            'attributes_configuration' => $this->configuration['attributes_configuration']
        ];

        $rows = $this->hydrator->hydrate($this->qb, $options);

        return $rows;
    }

    /**
     * Returns query builder
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }
}
