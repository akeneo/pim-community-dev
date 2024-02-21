<?php

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi;

use Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeRepository extends EntityRepository implements
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
            ->orderBy('t.label');

        if (isset($options['type'])) {
            $queryBuilder
                ->andWhere('g.type = :type')
                ->setParameter('type', $options['type'])
            ;
        }

        $choices = [];
        foreach ($queryBuilder->getQuery()->getArrayResult() as $code) {
            $choices[$code['label']] = $code['id'];
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder()
    {
        $rootAlias = 'g';
        $qb = $this->createQueryBuilder($rootAlias);

        $labelExpr = sprintf(
            "(CASE WHEN translation.label IS NULL THEN %s.code ELSE translation.label END)",
            $rootAlias
        );

        $qb
            ->select($rootAlias)
            ->addSelect(sprintf("%s AS label", $labelExpr));

        $qb
            ->leftJoin($rootAlias .'.translations', 'translation', 'WITH', 'translation.locale = :localeCode');

        return $qb;
    }
}
