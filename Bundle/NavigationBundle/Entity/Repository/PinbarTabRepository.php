<?php

namespace Oro\Bundle\NavigationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

/**
 * PinbarTab Repository
 */
class PinbarTabRepository extends EntityRepository implements NavigationRepositoryInterface
{
    /**
     * Find all Pinbar tabs for specified user
     *
     * @param \Pim\Bundle\UserBundle\Entity\UserInterface $user
     * @param string                                      $type
     * @param array                                       $options
     *
     * @return array
     */
    public function getNavigationItems($user, $type, $options = array())
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->add(
            'select',
            new Expr\Select(
                array(
                    'pt.id',
                    'ni.url',
                    'ni.title',
                    'ni.type',
                    'ni.id AS parent_id',
                    'pt.maximized'
                )
            )
        )
        ->add('from', new Expr\From('Oro\Bundle\NavigationBundle\Entity\PinbarTab', 'pt'))
        ->innerJoin('pt.item', 'ni', Expr\Join::WITH)
        ->add(
            'where',
            $qb->expr()->andx(
                $qb->expr()->eq('ni.user', ':user'),
                $qb->expr()->eq('ni.type', ':type')
            )
        )
        ->add('orderBy', new Expr\OrderBy('ni.position', 'ASC'))
        ->setParameters(array('user' => $user, 'type' => $type));

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Increment positions of Pinbar tabs for specified user
     *
     * @param  \Pim\Bundle\UserBundle\Entity\UserInterface $user
     * @param  int                                         $navigationItemId
     * @return mixed
     */
    public function incrementTabsPositions($user, $navigationItemId)
    {
        $updateQuery = $this->_em->createQuery(
            'UPDATE Oro\Bundle\NavigationBundle\Entity\NavigationItem p '
            . 'set p.position = p.position + 1 '
            . 'WHERE p.id != ' . (int) $navigationItemId
            . " AND p.type = 'pinbar'"
            . " AND p.user = :user"
        );
        $updateQuery->setParameter('user', $user);

        return $updateQuery->execute();
    }
}
