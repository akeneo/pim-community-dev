<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\ClassifyType as BaseClassifyType;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * We override the ClassifyType because we want to show only the category tree
 * the user has access to.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClassifyType extends BaseClassifyType
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var CategoryManager */
    protected $categoryManager;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryManager             $categoryManager
     * @param TokenStorageInterface       $tokenStorage
     * @param string                      $dataClass
     * @param string                      $formType
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        CategoryManager $categoryManager,
        TokenStorageInterface $tokenStorage,
        $dataClass,
        $formType
    ) {
        parent::__construct($categoryRepository, $dataClass, $formType);

        $this->tokenStorage = $tokenStorage;
        $this->categoryManager = $categoryManager;
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['trees'] = $this->categoryManager->getAccessibleTrees($this->tokenStorage->getToken()->getUser());
    }
}
