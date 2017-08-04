<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\DataGridBundle\Doctrine\ORM\Repository\DatagridRepositoryInterface;
use Pim\Bundle\DataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyRepository extends EntityRepository implements
    TranslatedLabelsProviderInterface,
    DatagridRepositoryInterface,
    MassActionRepositoryInterface
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
        $query = $this->createQueryBuilder('f')
            ->select('f.id')
            ->addSelect('COALESCE(NULLIF(ft.label, \'\'), CONCAT(\'[\', f.code, \']\')) as label')
            ->leftJoin('f.translations', 'ft', 'WITH', 'ft.locale = :locale_code')
            ->orderBy('label')
            ->setParameter('locale_code', $this->userContext->getCurrentLocaleCode())
            ->getQuery();

        $choices = [];
        foreach ($query->getArrayResult() as $family) {
            $choices[$family['label']] = $family['id'];
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('f');
        $rootAlias = $qb->getRootAlias();

        $labelExpr = sprintf(
            '(CASE WHEN translation.label IS NULL THEN %s.code ELSE translation.label END)',
            $rootAlias
        );

        $qb
            ->select($rootAlias)
            ->addSelect(sprintf('%s AS familyLabel', $labelExpr))
            ->addSelect('translation.label');

        $qb
            ->leftJoin($rootAlias . '.translations', 'translation', 'WITH', 'translation.locale = :localeCode')
            ->leftJoin($rootAlias . '.attributeAsLabel', 'a');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function applyMassActionParameters($qb, $inset, array $values)
    {
        if ($values) {
            $rootAlias = $qb->getRootAlias();
            $valueWhereCondition =
                $inset
                    ? $qb->expr()->in($rootAlias, $values)
                    : $qb->expr()->notIn($rootAlias, $values);
            $qb->andWhere($valueWhereCondition);
        }

        if (null !== $qb->getDQLPart('where')) {
            $whereParts = $qb->getDQLPart('where')->getParts();
            $qb->resetDQLPart('where');

            foreach ($whereParts as $part) {
                if (!is_string($part) || !strpos($part, 'entityIds')) {
                    $qb->andWhere($part);
                }
            }
        }

        $qb->setParameters(
            $qb->getParameters()->filter(
                function ($parameter) {
                    return $parameter->getName() !== 'entityIds';
                }
            )
        );

        // remove limit of the query
        $qb->setMaxResults(null);
    }
}
