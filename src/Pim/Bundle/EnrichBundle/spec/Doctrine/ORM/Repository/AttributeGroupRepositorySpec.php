<?php

namespace spec\Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Context\UserContext;

class AttributeGroupRepositorySpec extends ObjectBehavior
{
    function let(UserContext $userContext, EntityManager $em, ClassMetadata $classMetadata)
    {
        $classMetadata->name = 'attribute_group';

        $userContext->getCurrentLocaleCode()->willReturn('en_US');
        $em->getClassMetadata('groupTypeClass')->willReturn($classMetadata);

        $this->beConstructedWith($userContext, $em, 'groupTypeClass');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\AttributeGroupRepository');
    }

    function it_provides_translated_data()
    {
        $this->shouldImplement('Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface');
    }

    function it_is_a_doctrine_repository()
    {
        $this->shouldHaveType('Doctrine\ORM\EntityRepository');
    }

    function it_finds_attribute_groups_to_build_select($em, QueryBuilder $queryBuilder, AbstractQuery $query)
    {
        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('g')->willReturn($queryBuilder);
        $queryBuilder->select('g.code')->willReturn($queryBuilder);
        $queryBuilder->addSelect('COALESCE(NULLIF(t.label, \'\'), CONCAT(\'[\', g.code, \']\')) as label')->willReturn($queryBuilder);
        $queryBuilder->from('attribute_group', 'g', null)->willReturn($queryBuilder);
        $queryBuilder->leftJoin('g.translations', 't', 'WITH', 't.locale = :locale')->willReturn($queryBuilder);
        $queryBuilder->setParameter('locale', 'en_US')->willReturn($queryBuilder);
        $queryBuilder->orderBy('t.label')->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);
        $query->getArrayResult()->willReturn([
            ['code' => 10, 'label' => 'group en'],
            ['code' => 11, 'label' => '[group_other_code]'],
        ]);

        $this->findTranslatedLabels()->shouldReturn([
            10 => 'group en',
            11 => '[group_other_code]',
        ]);
    }
}
