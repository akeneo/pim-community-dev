<?php

namespace Akeneo\Tool\Bundle\ClassificationBundle\Doctrine\ORM\Repository;

use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\SearchableRepository;
use Akeneo\Tool\Component\Classification\Repository\TagRepositoryInterface;

/**
 * Tag repository
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TagRepository extends SearchableRepository implements TagRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties(): array
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier(string $identifier): ?object
    {
        return $this->findOneBy(['code' => $identifier]);
    }

    /**
     * Get all tags id and code
     *
     * @return string[]
     */
    public function findAllCodes(): array
    {
        $queryBuilder = $this->createQueryBuilder('t');
        $queryBuilder->select('t.id, t.code');
        $queryBuilder->orderBy('t.code');

        $codes = [];

        foreach ($queryBuilder->getQuery()->getArrayResult() as $result) {
            $codes[$result['code']] = $result['id'];
        }

        return $codes;
    }

    protected function getAlias(): string
    {
        return 'tag';
    }
}
