<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Controller\Rest;

use Pim\Bundle\UIBundle\Controller\AjaxOptionController as BaseAjaxOptionController;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use PimEnterprise\Bundle\SecurityBundle\User\UserContext;
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
    /** @var \PimEnterprise\Bundle\SecurityBundle\User\UserContext */
    protected $userContext;

    /**
     * @param RegistryInterface                                     $doctrine
     * @param ConfigurationRegistryInterface                        $referenceDataRegistry
     * @param \PimEnterprise\Bundle\SecurityBundle\User\UserContext $userContext
     */
    public function __construct(
        RegistryInterface $doctrine,
        ConfigurationRegistryInterface $referenceDataRegistry,
        UserContext $userContext
    ) {
        parent::__construct($doctrine, $referenceDataRegistry);

        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function listAction(Request $request)
    {
        $query = $request->query;
        $search = $query->get('search');
        $referenceDataName = $query->get('referenceDataName');
        $class = $query->get('class');

        if (null !== $referenceDataName) {
            $class = $this->registry->get($referenceDataName)->getClass();
        }

        $repository = $this->doctrine->getRepository($class);

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
