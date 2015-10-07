<?php

namespace Oro\Bundle\NavigationBundle\Entity\Repository;

use Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

/**
 * NavigationItem Repository
 */
class HistoryItemRepository extends EntityRepository implements NavigationRepositoryInterface
{
    const DEFAULT_SORT_ORDER = 'DESC';

    /**
     * Find all history items for specified user
     * $options['orderBy'], if passed, must be an array with following structure:
     * array(
     *  array(
     *      'field'   => $field_name,
     *      'dir'  => 'ASC'|'DESC'
     *  )
     * )
     * @param \Pim\Bundle\UserBundle\Entity\UserInterface $user
     * @param string                                      $type
     * @param array                                       $options
     *
     * @return array
     */
    public function getNavigationItems($user, $type = null, $options = array())
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->add(
            'select',
            new Expr\Select(
                array(
                    'ni.id',
                    'ni.url',
                    'ni.title',
                )
            )
        )
            ->add('from', new Expr\From('Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem', 'ni'))
            ->add(
                'where',
                $qb->expr()->eq('ni.user', ':user')
            )
            ->setParameters(array('user' => $user));

        $orderBy = array(array('field' => NavigationHistoryItem::NAVIGATION_HISTORY_COLUMN_VISITED_AT));
        if (isset($options['orderBy'])) {
            $orderBy = (array) $options['orderBy'];
        }
        $fields = $this->_em->getClassMetadata('OroNavigationBundle:NavigationHistoryItem')->getFieldNames();
        foreach ($orderBy as $order) {
            if (isset($order['field']) && in_array($order['field'], $fields)) {
                $qb->addOrderBy(
                    'ni.' . $order['field'],
                    isset($order['dir']) ? $order['dir'] : self::DEFAULT_SORT_ORDER
                );
            }
        }
        if (isset($options['maxItems'])) {
            $qb->setMaxResults((int) $options['maxItems']);
        }

        return $qb->getQuery()->getArrayResult();
    }
}
