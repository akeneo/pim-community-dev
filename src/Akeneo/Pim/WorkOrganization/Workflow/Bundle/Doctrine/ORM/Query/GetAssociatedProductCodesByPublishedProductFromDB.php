<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\GetAssociatedProductCodesByPublishedProduct;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

class GetAssociatedProductCodesByPublishedProductFromDB implements GetAssociatedProductCodesByPublishedProduct
{
    public function __construct(private Connection $connection, private TokenStorageInterface $tokenStorage)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getCodes(int $publishedProductId, AssociationInterface $association): array
    {
        $user = $this->tokenStorage->getToken()->getUser();
        Assert::implementsInterface($user, UserInterface::class);
        $userGroupsIds = $user->getGroupsIds();

        $sql = <<<SQL
SELECT DISTINCT(p.identifier) as identifier
FROM pimee_workflow_published_product_association a
    INNER JOIN pimee_workflow_published_product_association_published_product ap ON a.id = ap.association_id
    INNER JOIN pimee_workflow_published_product p ON p.id = ap.product_id
    LEFT JOIN pimee_workflow_category_published_product cp on p.id = cp.product_id
    LEFT JOIN pimee_security_product_category_access pca ON pca.category_id = cp.category_id AND pca.user_group_id IN (:userGroupsIds)
WHERE a.owner_id = :ownerId AND a.association_type_id = :associationTypeId
    AND (cp.category_id IS NULL OR pca.view_items = 1)
ORDER BY identifier;
SQL;

        $stmt = $this->connection->executeQuery(
            $sql,
            [
                'userGroupsIds'     => $userGroupsIds,
                'ownerId'           => $publishedProductId,
                'associationTypeId' => $association->getAssociationType()->getId()
            ],
            [
                'userGroupsIds'     => Connection::PARAM_INT_ARRAY,
                'ownerId'           => \PDO::PARAM_INT,
                'associationTypeId' => \PDO::PARAM_INT
            ]
        );

        return $stmt->fetchFirstColumn();
    }
}
