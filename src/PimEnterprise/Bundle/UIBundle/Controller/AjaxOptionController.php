<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\UIBundle\Controller;

use Pim\Bundle\UIBundle\Controller\AjaxOptionController as BaseAjaxOptionController;
use PimEnterprise\Bundle\UserBundle\Context\UserContext;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for ajax choices
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class AjaxOptionController extends BaseAjaxOptionController
{
    /** @var UserContext */
    protected $userContext;

    /**
     * @param RegistryInterface $doctrine
     * @param UserContext       $userContext
     */
    public function __construct(RegistryInterface $doctrine, UserContext $userContext)
    {
        parent::__construct($doctrine);

        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function listAction(Request $request)
    {
        $query      = $request->query;
        $search     = $query->get('search');
        $repository = $this->doctrine->getRepository($query->get('class'));

        if ($repository instanceof AssetRepositoryInterface) {
            $grantedCategories = $this->userContext->getGrantedCategories();

            $options = $query->get('options', []);
            $options = array_merge($options, ['categories' => $grantedCategories]);

            $choices['results'] = $repository->findBySearch($search, $options);

            return new JsonResponse($choices);
        }

        return parent::listAction($request);
    }
}
