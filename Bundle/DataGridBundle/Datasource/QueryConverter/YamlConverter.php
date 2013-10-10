<?php

namespace Oro\Bundle\DataGridBundle\Datasource\QueryConverter;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Processor;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;

use Oro\Bundle\DataGridBundle\DependencyInjection\Configuration\QueryConfiguration;

class YamlConverter implements QueryConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($value, EntityManager $em)
    {
        if (!is_array($value)) {
            $value = Yaml::parse($value);
        }

        $processor = new Processor();

        $value = $processor->processConfiguration(new QueryConfiguration(), $value);
        $qb    = $em->createQueryBuilder();

        if (!isset($value['from'])) {
            throw new \RuntimeException('Missing mandatory "from" section');
        }

        foreach ((array) $value['from'] as $from) {
            $qb->from($from['table'], $from['alias']);
        }

        if (isset($value['select'])) {
            foreach ($value['select'] as $select) {
                $qb->add('select', new Expr\Select($select), true);
            }
        }

        if (isset($value['distinct'])) {
            $qb->distinct((bool) $value['distinct']);
        }

        if (isset($value['groupBy'])) {
            $qb->groupBy($value['groupBy']);
        }

        if (isset($value['having'])) {
            $qb->having($value['having']);
        }

        $this->addJoin($qb, $value);
        $this->addWhere($qb, $value);
        $this->addOrder($qb, $value);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(QueryBuilder $input)
    {
        return '';
    }

    /**
     * @param QueryBuilder $qb
     * @param array        $value
     */
    protected function addJoin(QueryBuilder $qb, $value)
    {
        if (isset($value['join'])) {
            if (isset($value['join']['inner'])) {
                foreach ((array) $value['join']['inner'] as $join) {
                    $qb->innerJoin($join['join'], $join['alias']);
                }
            }

            if (isset($value['join']['left'])) {
                foreach ((array) $value['join']['left'] as $join) {
                    $qb->leftJoin($join['join'], $join['alias']);
                }
            }
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param array        $value
     */
    protected function addWhere(QueryBuilder $qb, $value)
    {
        if (isset($value['where'])) {
            if (isset($value['where']['and'])) {
                foreach ((array) $value['where']['and'] as $where) {
                    $qb->andWhere($where);
                }
            }

            if (isset($value['where']['or'])) {
                foreach ((array) $value['where']['or'] as $where) {
                    $qb->orWhere($where);
                }
            }
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param array        $value
     */
    protected function addOrder(QueryBuilder $qb, $value)
    {
        if (isset($value['orderBy'])) {
            $qb->resetDQLPart('orderBy');

            foreach ((array) $value['orderBy'] as $order) {
                $qb->addOrderBy($order['column'], $order['dir']);
            }
        }
    }
}
