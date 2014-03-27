<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\QueryBuilderUtility;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * Product datasource, allows to prepare query builder from repository
 *
 * The query builder is built from the object repository (entity or document)
 * The extensions are common or orm/odm specific
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductDatasource implements DatasourceInterface, ParameterizableInterface
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

    /** @var string */
    protected $scopeCode = null;

    /** @var integer */
    protected $currentGroupId = null;

    /** @var array */
    protected $parameters = array();

    /** @var ProductRepositoryInterface $repository */
    protected $repository;

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
        if (isset($config['repository_method']) && $method = $config['repository_method']) {
            if (isset($config['repository_parameters'])) {
                $this->qb = $this->getRepository()->$method($config['repository_parameters']);
            } else {
                $this->qb = $this->getRepository()->$method();
            }
        } else {
            $this->qb = $this->getRepository()->createQueryBuilder('o');
        }

        $localeKey = ContextConfigurator::DISPLAYED_LOCALE_KEY;
        $this->localeCode = isset($config[$localeKey]) ? $config[$localeKey] : null;

        $scopeKey = ContextConfigurator::DISPLAYED_SCOPE_KEY;
        $this->scopeCode = isset($config[$scopeKey]) ? $config[$scopeKey] : null;

        $groupKey = ContextConfigurator::CURRENT_GROUP_ID_KEY;
        $this->currentGroupId = isset($config[$groupKey]) ? $config[$groupKey] : null;

        $grid->setDatasource(clone $this);
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $options = [
            'locale_code'              => $this->localeCode,
            'scope_code'               => $this->scopeCode,
            'current_group_id'         => $this->currentGroupId,
            'attributes_configuration' => $this->configuration['attributes_configuration']
        ];

        if (method_exists($this->qb, 'setParameters')) {
            QueryBuilderUtility::removeExtraParameters($this->qb);
        }

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

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
        if (method_exists($this->qb, 'setParameters')) {
            $this->qb->setParameters($parameters);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository()
    {
        if (!$this->repository) {
            $this->repository = $this->om->getRepository($this->getConfiguration('entity'));
        }

        return $this->repository;
    }

    /**
     * Get configuration
     *
     * @param string $key
     *
     * @return mixed
     *
     * @throws \LogicException
     * @throws \Exception
     */
    protected function getConfiguration($key)
    {
        if (!$this->configuration) {
            throw new \LogicException('Datasource is not yet built. You need to call process method before');
        }

        if (!isset($this->configuration[$key])) {
            throw new \Exception(sprintf('"%s" expects to be configured with "%s"', get_class($this), $key));
        }

        return $this->configuration[$key];
    }
}
