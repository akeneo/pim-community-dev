<?php

namespace Oro\Bundle\GridBundle\Datagrid\QueryConverter;

use Symfony\Component\Yaml\Yaml;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\GridBundle\Datagrid\QueryConverterInterface;

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

        $qb = $em->createQueryBuilder();

        if (!isset($value['from'])) {
            throw new \RuntimeException('Missing mandatory "from" section');
        }

        foreach ((array) $value['from'] as $from) {
            $qb->from($from['table'], $from['alias']);
        }

        if (isset($value['select'])) {
            $qb->select($value['select']);
        }

        if (isset($value['distinct'])) {
            $qb->distinct((bool) $value['distinct']);
        }

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

        if (isset($value['groupBy'])) {
            $qb->groupBy($value['groupBy']);
        }

        if (isset($value['having'])) {
            $qb->having($value['having']);
        }

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

        if (isset($value['orderBy'])) {
            $qb->resetDQLPart('orderBy');

            foreach ((array) $value['orderBy'] as $order) {
                $qb->addOrderBy($order['column'], $order['dir']);
            }
        }

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(QueryBuilder $input)
    {
        ;
    }
}