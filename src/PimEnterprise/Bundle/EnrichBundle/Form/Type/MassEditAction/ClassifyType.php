<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\ClassifyType as BaseClassifyType;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
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

    /**
     * @param CategoryManager       $categoryManager
     * @param TokenStorageInterface $tokenStorage
     * @param string                $categoryClass
     * @param string                $dataClass
     */
    public function __construct(
        CategoryManager $categoryManager,
        TokenStorageInterface $tokenStorage,
        $categoryClass,
        $dataClass
    ) {
        parent::__construct($categoryManager, $categoryClass, $dataClass);

        $this->tokenStorage = $tokenStorage;
        $this->trees        = $categoryManager->getAccessibleTrees($this->tokenStorage->getToken()->getUser());
    }
}
