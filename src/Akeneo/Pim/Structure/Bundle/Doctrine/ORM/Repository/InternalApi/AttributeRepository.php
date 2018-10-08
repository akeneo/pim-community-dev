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
class AttributeRepository extends EntityRepository implements
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
        $queryBuilder = $this->createQueryBuilder('a')
            ->select('a.code')
            ->addSelect('COALESCE(NULLIF(at.label, \'\'), CONCAT(\'[\', a.code, \']\')) as attribute_label')
            ->addSelect('COALESCE(NULLIF(gt.label, \'\'), CONCAT(\'[\', g.code, \']\')) as group_label')
            ->leftJoin('a.translations', 'at', 'WITH', 'at.locale = :locale_code')
            ->leftJoin('a.group', 'g')
            ->leftJoin('g.translations', 'gt', 'WITH', 'gt.locale = :locale_code')
            ->orderBy('g.sortOrder, a.sortOrder')
            ->setParameter('locale_code', $this->userContext->getCurrentLocaleCode());

        if (isset($options['excluded_attribute_ids']) && !empty($options['excluded_attribute_ids'])) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->notIn('a.id', $options['excluded_attribute_ids'])
            );
        }

        if (isset($options['useable_as_grid_filter'])) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('a.useableAsGridFilter', $options['useable_as_grid_filter'])
            );
        }

        $choices = [];
        foreach ($queryBuilder->getQuery()->getArrayResult() as $attribute) {
            $choices[$attribute['group_label']][$attribute['attribute_label']] = $attribute['code'];
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->createQueryBuilder('a');
        $rootAlias = $qb->getRootAlias();

        $labelExpr = sprintf(
            '(CASE WHEN translation.label IS NULL THEN %s.code ELSE translation.label END)',
            $rootAlias
        );
        $groupExpr = '(CASE WHEN gt.label IS NULL THEN attributeGroup.code ELSE gt.label END)';

        $qb
            ->select($rootAlias)
            ->addSelect(sprintf("%s AS label", $labelExpr))
            ->addSelect(sprintf("%s AS groupLabel", $groupExpr))
            ->addSelect('translation.label')
            ->addSelect('attributeGroup.code');

        $qb
            ->leftJoin($rootAlias .'.translations', 'translation', 'WITH', 'translation.locale = :localeCode')
            ->leftJoin($rootAlias .'.group', 'attributeGroup')
            ->leftJoin('attributeGroup.translations', 'gt', 'WITH', 'gt.locale = :localeCode');

        return $qb;
    }
}
