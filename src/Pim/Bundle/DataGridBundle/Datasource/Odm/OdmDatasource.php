<?php

namespace Pim\Bundle\DataGridBundle\Datasource\Odm;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;

/**
 * Basic PIM data source, allow to prepare query builder from repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OdmDatasource implements DatasourceInterface
{
    /**
     * @var string
     */
    const TYPE = 'pim_odm';

    /** @var QueryBuilder */
    protected $qb;

    /** @var ObjectManager */
    protected $om;

    /** @var array grid configuration */
    protected $configuration;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
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

        $this->isFlexible = isset($config['is_flexible']) ? (bool) $config['is_flexible'] : false;
        $localeKey = ContextConfigurator::DISPLAYED_LOCALE_KEY;
        $this->localeCode = isset($config[$localeKey]) ? $config[$localeKey] : null;

        $grid->setDatasource(clone $this);
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $query = $this->qb
            ->hydrate(false)
            ->getQuery();
        $results = $query->execute();

        $rows       = [];
        $config     = $this->configuration['attributes_configuration'];
        $attributes = [];
        foreach ($config as $attributeConf) {
            $attributes[$attributeConf['id']]= $attributeConf;
        }

        foreach ($results as $result) {
            $result['id']= $result['_id']->__toString();
            unset($result['_id']);
            $result['dataLocale']= $this->localeCode;
            foreach ($result['values'] as $value) {
                $attribute = $attributes[$value['attributeId']];
                $value['attribute']= $attribute;
                $result[$attribute['code']]= $value;
            }
            unset($result['values']);
            $rows[] = new ResultRecord($result);
        }

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
     * Set QueryBuilder
     *
     * @param QueryBuilder $qb
     *
     * @return $this
     */
    public function setQueryBuilder(QueryBuilder $qb)
    {
        $this->qb = $qb;

        return $this;
    }
}
