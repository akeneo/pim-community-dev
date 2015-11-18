<?php

namespace Oro\Bundle\DataGridBundle\Datasource\Orm\QueryConverter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class YamlConverter implements QueryConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($value, QueryBuilder $qb)
    {
        if (!is_array($value)) {
            $value = Yaml::parse(file_get_contents($value));
        }

        $processor = new Processor();

        $value = $processor->processConfiguration(new QueryConfiguration(), $value);

        if (!isset($value['from'])) {
            throw new \RuntimeException('Missing mandatory "from" section');
        }

        foreach ((array)$value['from'] as $from) {
            $qb->from($from['table'], $from['alias']);
        }

        if (isset($value['select'])) {
            foreach ($value['select'] as $select) {
                $qb->add('select', new Expr\Select($select), true);
            }
        }

        if (isset($value['distinct'])) {
            $qb->distinct((bool)$value['distinct']);
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
        $defaultValues = ['conditionType' => null, 'condition' => null];
        if (isset($value['join'])) {
            if (isset($value['join']['inner'])) {
                foreach ((array)$value['join']['inner'] as $join) {
                    $join = array_merge($defaultValues, $join);
                    $qb->innerJoin($join['join'], $join['alias'], $join['conditionType'], $join['condition']);
                }
            }

            if (isset($value['join']['left'])) {
                foreach ((array)$value['join']['left'] as $join) {
                    $join = array_merge($defaultValues, $join);
                    $qb->leftJoin($join['join'], $join['alias'], $join['conditionType'], $join['condition']);
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
                foreach ((array)$value['where']['and'] as $where) {
                    $qb->andWhere($where);
                }
            }

            if (isset($value['where']['or'])) {
                foreach ((array)$value['where']['or'] as $where) {
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

            foreach ((array)$value['orderBy'] as $order) {
                $qb->addOrderBy($order['column'], $order['dir']);
            }
        }
    }
}
