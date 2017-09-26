<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Pim\Bundle\DataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupRepository extends EntityRepository implements
    TranslatedLabelsProviderInterface,
    DatagridRepositoryInterface
{
    /** @var UserContext */
    protected $userContext;

    /**
     * @param UserContext   $userContext
     * @param EntityManager $em
     * @param string        $class
     */
    public function __construct(UserContext $userContext, EntityManager $em, $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));

        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function findTranslatedLabels(array $options = [])
    {
        $queryBuilder = $this->createQueryBuilder('g')
            ->select('g.id')
            ->addSelect('COALESCE(NULLIF(t.label, \'\'), CONCAT(\'[\', g.code, \']\')) as label')
            ->leftJoin('g.translations', 't', 'WITH', 't.locale = :locale')
            ->setParameter('locale', $this->userContext->getCurrentLocaleCode())
            ->orderBy('t.label')
            ->getQuery();

        $choices = [];
        foreach ($queryBuilder->getArrayResult() as $code) {
            $choices[$code['label']] = $code['id'];
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('g');

        $groupLabelExpr = 'COALESCE(translation.label, g.code)';
        $typeLabelExpr = 'COALESCE(typeTrans.label, type.code)';

        $qb
            ->addSelect(sprintf('%s AS groupLabel', $groupLabelExpr))
            ->addSelect(sprintf('%s AS typeLabel', $typeLabelExpr))
            ->addSelect('translation.label')
        ;

        $qb
            ->innerJoin('g.type', 'type', Expr\Join::WITH)
            ->leftJoin('g.translations', 'translation', Expr\Join::WITH, 'translation.locale = :localeCode')
            ->leftJoin('type.translations', 'typeTrans', Expr\Join::WITH, 'typeTrans.locale = :localeCode')
        ;

        $qb->distinct(true);

        return $qb;
    }
}
