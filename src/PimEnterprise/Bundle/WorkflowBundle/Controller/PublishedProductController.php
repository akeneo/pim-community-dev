<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractController;
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProposalManager;
use PimEnterprise\Bundle\WorkflowBundle\Factory\PublishedProductFactory;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

/**
 * Published product controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishedProductController extends AbstractController
{
    /** @var PublishedProductFactory */
    protected $factory;

    /** @var ProductManager */
    protected $manager;

    /**
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param PublishedProductFactory  $factory
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        PublishedProductFactory $factory,
        ProductManager $manager
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator
        );
        $this->factory = $factory;
        $this->manager = $manager;
    }

    /**
     * @param integer|string $id
     *
     * @return RedirectResponse
     * @throws NotFoundHttpException
     */
    public function indexAction()
    {

        $product = $this->manager->find(1);

        $snapshot = $this->factory->publish($product);

        var_dump(get_class($snapshot));

        $this->manager->getObjectManager()->persist($snapshot);
        $this->manager->getObjectManager()->flush();

        die();
    }
}
