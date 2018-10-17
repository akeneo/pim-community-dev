<?php

namespace Specification\Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\AttributeRepository;
use Akeneo\Platform\Bundle\UIBundle\Provider\TranslatedLabelsProviderInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;

class AttributeRepositorySpec extends ObjectBehavior
{
    function let(UserContext $userContext, EntityManager $em, ClassMetadata $classMetadata)
    {
        $classMetadata->name = 'attribute';

        $userContext->getCurrentLocaleCode()->willReturn('en_US');
        $em->getClassMetadata('groupClass')->willReturn($classMetadata);

        $this->beConstructedWith($userContext, $em, 'groupClass');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeRepository::class);
    }

    function it_provides_translated_data()
    {
        $this->shouldImplement(TranslatedLabelsProviderInterface::class);
    }

    function it_is_a_doctrine_repository()
    {
        $this->shouldHaveType('Doctrine\ORM\EntityRepository');
    }

    function it_finds_attributes_to_build_select($em, QueryBuilder $queryBuilder, AbstractQuery $query, Expr $expr)
    {
        $queryBuilder->expr()->willReturn($expr);
        $expr->notIn('a.id', [10])->willReturn($expr);

        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('a')->willReturn($queryBuilder);
        $queryBuilder->select('a.code')->willReturn($queryBuilder);
        $queryBuilder->addSelect('COALESCE(NULLIF(at.label, \'\'), CONCAT(\'[\', a.code, \']\')) as attribute_label')->willReturn($queryBuilder);
        $queryBuilder->addSelect('COALESCE(NULLIF(gt.label, \'\'), CONCAT(\'[\', g.code, \']\')) as group_label')->willReturn($queryBuilder);
        $queryBuilder->from('attribute', 'a', null)->willReturn($queryBuilder);
        $queryBuilder->leftJoin('a.translations', 'at', 'WITH', 'at.locale = :locale_code')->willReturn($queryBuilder);
        $queryBuilder->leftJoin('a.group', 'g')->willReturn($queryBuilder);
        $queryBuilder->leftJoin('g.translations', 'gt', 'WITH', 'gt.locale = :locale_code')->willReturn($queryBuilder);
        $queryBuilder->andWhere($expr)->willReturn($queryBuilder);
        $queryBuilder->orderBy('g.sortOrder, a.sortOrder')->willReturn($queryBuilder);
        $queryBuilder->setParameter('locale_code', 'en_US')->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);
        $query->getArrayResult()->willReturn([
            ['code' => 10, 'group_label' => 'group fr', 'attribute_label' => 'attribute fr'],
            ['code' => 11, 'group_label' => '[group_other_code]', 'attribute_label' => '[group_attribute_code]'],
        ]);

        $this->findTranslatedLabels([
            'locale_code' => 'en_US',
            'excluded_attribute_ids' => [10],
        ])->shouldReturn([
            'group fr' => ['attribute fr' => 10],
            '[group_other_code]' => ['[group_attribute_code]' => 11],
        ]);
    }
}
