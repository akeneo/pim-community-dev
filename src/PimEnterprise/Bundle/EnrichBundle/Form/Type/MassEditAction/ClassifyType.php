<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Pim\Bundle\EnrichBundle\Form\Type\MassEditAction\ClassifyType as BaseClassifyType;
use PimEnterprise\Bundle\CatalogBundle\Manager\CategoryManager;
use Symfony\Component\Security\Core\SecurityContextInterface;

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
    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * @param CategoryManager          $categoryManager
     * @param SecurityContextInterface $securityContext
     * @param string                   $categoryClass
     * @param string                   $dataClass
     */
    public function __construct(
        CategoryManager $categoryManager,
        SecurityContextInterface $securityContext,
        $categoryClass,
        $dataClass
    ) {
        parent::__construct($categoryManager, $categoryClass, $dataClass);

        $this->securityContext = $securityContext;
        $this->trees = $categoryManager->getAccessibleTrees($this->securityContext->getToken()->getUser());
    }
}
