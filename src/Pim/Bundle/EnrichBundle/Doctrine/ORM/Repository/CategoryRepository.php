<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface;

class CategoryRepository extends NestedTreeRepository implements TranslatedLabelsProviderInterface
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
        $query = $this->childrenQueryBuilder(null, true, 'created', 'DESC')
            ->select('node.code')
            ->addSelect('COALESCE(NULLIF(t.label, \'\'), CONCAT(\'[\', node.code, \']\')) as label')
            ->leftJoin('node.translations', 't', 'WITH', 't.locale = :locale')
            ->setParameter('locale', $this->userContext->getCurrentLocaleCode())
            ->orderBy('t.label')
            ->getQuery();


        $choices = [];
        foreach ($query->getArrayResult() as $code) {
            $choices[$code['label']] = $code['code'];
        }

        return $choices;
    }
}
