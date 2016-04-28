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
class FamilyRepository extends EntityRepository implements ChoicesProviderInterface
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
        $query = $this->createQueryBuilder('f')
            ->select('f.id')
            ->addSelect('COALESCE(ft.label, CONCAT(\'[\', f.code, \']\')) as label')
            ->leftJoin('f.translations', 'ft', 'WITH', 'ft.locale = :locale_code')
            ->orderBy('label')
            ->setParameter('locale_code', $this->userContext->getCurrentLocaleCode())
            ->getQuery();

        $choices = [];
        foreach ($query->getArrayResult() as $family) {
            $choices[$family['id']] = $family['label'];
        }

        return $choices;
    }
}
