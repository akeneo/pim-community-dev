<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Pim\Bundle\CatalogBundle\Entity\VariantGroup;

/**
 * Variant group controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupController extends AbstractDoctrineController
{
    /**
     * @var DatagridWorkerInterface
     */
    protected $datagridWorker;

    /**
     * Constructor
     *
     * @param Request $request
     * @param EngineInterface $templating
     * @param RouterInterface $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface $formFactory
     * @param ValidatorInterface $validator
     * @param TranslatorInterface $translator
     * @param RegistryInterface $doctrine
     * @param DatagridWorkerInterface $datagridWorker
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        RegistryInterface $doctrine,
        DatagridWorkerInterface $datagridWorker
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $doctrine
        );

        $this->datagridWorker = $datagridWorker;
    }

    /**
     * List variant groups
     *
     * @param Request $request
     *
     * @Template
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
    }

    /**
     * Create a variant group
     *
     * @return array
     */
    public function createAction()
    {
        $variant = new VariantGroup();

        return $this->editAction($variant);
    }

    /**
     * Edit a variant group
     *
     * @param VariantGroup $variant
     *
     * @return array
     */
    public function editAction(VariantGroup $variant)
    {
    }

    /**
     * Remove a variant group
     *
     * @param VariantGroup $variant
     *
     * @return Response
     */
    public function deleteAction(VariantGroup $variant)
    {
    }
}
