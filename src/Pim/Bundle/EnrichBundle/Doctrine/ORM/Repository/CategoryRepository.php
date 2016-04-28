<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Enrich\Repository\ChoicesProviderInterface;

class CategoryRepository extends NestedTreeRepository implements ChoicesProviderInterface
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
        $query = $this->childrenQueryBuilder(null, true, 'created', 'DESC')
            ->select('node.id')
            ->addSelect('COALESCE(t.label, CONCAT(\'[\', node.code, \']\')) as label')
            ->leftJoin('node.translations', 't')
            ->where('t.locale = :locale')
            ->setParameter('locale', $this->userContext->getCurrentLocaleCode())
            ->orderBy('t.label')
            ->getQuery();


        $choices = [];
        foreach ($query->getArrayResult() as $code) {
            $choices[$code['id']] = $code['label'];
        }

        return $choices;
    }
}
