<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Enrich\Repository\ChoicesProviderInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupRepository extends EntityRepository implements ChoicesProviderInterface
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
    public function findChoices(array $options = [])
    {
        $queryBuilder = $this->createQueryBuilder('g')
            ->select('g.id')
            ->addSelect('COALESCE(t.label, CONCAT(\'[\', g.code, \']\')) as label')
            ->leftJoin('g.translations', 't')
            ->andWhere('g.locale = :locale')
            ->setParameter('locale', $this->userContext->getCurrentLocaleCode())
            ->orderBy('g.label')
            ->getQuery();


        $choices = [];
        foreach ($queryBuilder->getArrayResult() as $code) {
            $choices[$code['id']] = $code['label'];
        }

        return $choices;
    }
}
