<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use PimEnterprise\Component\Workflow\Query\DraftAuthors as DraftAuthorsInterface;

/**
 * Find all authors for all drafts (product & product model)
 */
class DraftAuthors implements DraftAuthorsInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAuthors(?string $search, int $page = 1, int $limit = 20, array $identifiers = []): array
    {
        $sqlPmd = <<<SQL
SELECT u.username, u.username AS label
FROM oro_user u
INNER JOIN pimee_workflow_product_model_draft pmd ON u.username = pmd.author
WHERE 1=1
SQL;
        $sqlPd = <<<SQL
SELECT u.username, u.username AS label
FROM oro_user u
INNER JOIN pimee_workflow_product_draft pd ON u.username = pd.author
WHERE 1=1
SQL;

        if (null !== $search && '' !== $search) {
            $sqlPmd .= ' AND pmd.author LIKE :search';
            $sqlPd .= ' AND pd.author LIKE :search';
        }

        if (!empty($identifiers)) {
            $sqlPmd .= ' AND pmd.author in (:identifiers)';
            $sqlPd .= ' AND pd.author in (:identifiers)';
        }

        $sqlPmd .= ' LIMIT :start,:limit';
        $sqlPd .= ' LIMIT :start,:limit';
        $start = $limit * ($page - 1);

        $sql = '('.$sqlPmd.') UNION ('.$sqlPd.')';

        $stmt = $this->entityManager->getConnection()->prepare($sql);

        if (null !== $search && '' !== $search) {
            $stmt->bindValue('search', "%" . $search . "%");
        }

        if (!empty($identifiers)) {
            $stmt->bindValue('identifiers', $identifiers);
        }

        $stmt->bindValue('start', $start, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll();
    }
}
