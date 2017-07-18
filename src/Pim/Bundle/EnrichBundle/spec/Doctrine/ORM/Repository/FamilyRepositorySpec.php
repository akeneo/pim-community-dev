<?php

namespace spec\Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Context\UserContext;

class FamilyRepositorySpec extends ObjectBehavior
{
    function let(UserContext $userContext, EntityManager $em, ClassMetadata $classMetadata)
    {
        $classMetadata->name = 'family';

        $userContext->getCurrentLocaleCode()->willReturn('en_US');
        $em->getClassMetadata('familyClass')->willReturn($classMetadata);

        $this->beConstructedWith($userContext, $em, 'familyClass');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\FamilyRepository');
    }

    function it_provides_translated_data()
    {
        $this->shouldImplement('Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface');
    }

    function it_is_a_doctrine_repository()
    {
        $this->shouldHaveType('Doctrine\ORM\EntityRepository');
    }

    function it_finds_families_to_build_select($em, QueryBuilder $queryBuilder, AbstractQuery $query)
    {
        $em->createQueryBuilder()->willReturn($queryBuilder);
        $queryBuilder->select('f')->willReturn($queryBuilder);
        $queryBuilder->select('f.id')->willReturn($queryBuilder);
        $queryBuilder->addSelect('COALESCE(NULLIF(ft.label, \'\'), CONCAT(\'[\', f.code, \']\')) as label')->willReturn($queryBuilder);
        $queryBuilder->from('family', 'f', null)->willReturn($queryBuilder);
        $queryBuilder->leftJoin('f.translations', 'ft', 'WITH', 'ft.locale = :locale_code')->willReturn($queryBuilder);
        $queryBuilder->orderBy('label')->willReturn($queryBuilder);
        $queryBuilder->setParameter('locale_code', 'en_US')->willReturn($queryBuilder);
        $queryBuilder->getQuery()->willReturn($query);
        $query->getArrayResult()->willReturn([
            ['id' => 10, 'label' => 'family en'],
            ['id' => 11, 'label' => '[family_other_code]'],
        ]);

        $this->findTranslatedLabels(['locale_code' => 'en_US'])->shouldReturn([
            'family en' => 10,
            '[family_other_code]' => 11,
        ]);
    }
}
