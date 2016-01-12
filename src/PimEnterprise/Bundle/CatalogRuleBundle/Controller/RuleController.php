<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Controller;

use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Rule controller
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleController
{
    /** @var RuleDefinitionRepositoryInterface */
    protected $repository;

    /** @var RemoverInterface */
    protected $remover;

    /**
     * Constructor
     *
     * @param RuleDefinitionRepositoryInterface $repository
     * @param RemoverInterface                  $remover
     */
    public function __construct(RuleDefinitionRepositoryInterface $repository, RemoverInterface $remover)
    {
        $this->repository = $repository;
        $this->remover    = $remover;
    }

    /**
     * List all rules
     *
     * @Template
     *
     * @return JsonResponse
     *
     * @AclAncestor("pimee_catalog_rule_rule_view_permissions")
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * Delete a rule
     *
     * @param int $id
     *
     * @AclAncestor("pimee_catalog_rule_rule_delete_permissions")
     *
     * @throws NotFoundHttpException
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function deleteAction($id)
    {
        if (null === $rule = $this->repository->find($id)) {
            throw new NotFoundHttpException(
                sprintf('Rule definition with id "%s" can not be found.', (string) $id)
            );
        }

        $this->remover->remove($rule);

        return new JsonResponse();
    }
}
