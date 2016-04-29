<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Enrich\Repository\TranslatedLabelsProviderInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeRepository extends EntityRepository implements TranslatedLabelsProviderInterface
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
            ->addSelect('COALESCE(t.label, CONCAT(\'[\', g.code, \']\')) as label')
            ->leftJoin('g.translations', 't')
            ->andWhere('t.locale = :locale')
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
            $choices[$code['id']] = $code['label'];
        }

        return $choices;
    }
}
