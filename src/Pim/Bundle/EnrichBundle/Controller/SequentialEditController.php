<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;

use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Manager\SequentialEditManager;
use Pim\Bundle\UserBundle\Context\UserContext;

/**
 * Sequential edit action controller for products
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SequentialEditController extends AbstractDoctrineController
{
    /** @var MassActionParametersParser */
    protected $parametersParser;

    /** @var MassActionDispatcher */
    protected $massActionDispatcher;

    /** @var integer */
    protected $massEditLimit;

    /** @var array */
    protected $objects;

    /** @var SequentialEditManager */
    protected $sequentialEditManager;

    /** @var UserContext */
    protected $userContext;

    /**
     * Constructor
     *
     * @param Request                    $request
     * @param EngineInterface            $templating
     * @param RouterInterface            $router
     * @param SecurityContextInterface   $securityContext
     * @param FormFactoryInterface       $formFactory
     * @param ValidatorInterface         $validator
     * @param TranslatorInterface        $translator
     * @param EventDispatcherInterface   $eventDispatcher
     * @param ManagerRegistry            $doctrine
     * @param MassActionParametersParser $parametersParser
     * @param MassActionDispatcher       $massActionDispatcher
     * @param integer                    $massEditLimit
     * @param SequentialEditManager      $sequentialEditManager
     * @param UserContext                $userContext
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        MassActionParametersParser $parametersParser,
        MassActionDispatcher $massActionDispatcher,
        $massEditLimit,
        SequentialEditManager $sequentialEditManager,
        UserContext $userContext
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher,
            $doctrine
        );

        $this->parametersParser = $parametersParser;
        $this->massActionDispatcher = $massActionDispatcher;
        $this->massEditLimit = $massEditLimit;
        $this->sequentialEditManager = $sequentialEditManager;
        $this->userContext = $userContext;
    }

    /**
     * Action for product sequential edition
     *
     * @AclAncestor("pim_enrich_product_edit_attributes")
     *
     * @return RedirectResponse
     */
    public function sequentialEditAction()
    {
        $sequentialEdit = $this->sequentialEditManager->createEntity(
            $this->getObjects(),
            $this->userContext->getUser()
        );

        $this->sequentialEditManager->save($sequentialEdit);

        // TODO: Redirect on edit view
    }

    /**
     * Check if the mass action is executable
     *
     * @return boolean
     */
    protected function isExecutable()
    {
        return $this->exceedsMassEditLimit() === false;
    }

    /**
     * Temporary method to avoid editing too many objects
     *
     * @return boolean
     */
    protected function exceedsMassEditLimit()
    {
        if ($this->getObjectCount() > $this->massEditLimit) {
            $this->addFlash('error', 'pim_enrich.mass_edit_action.limit_exceeded', ['%limit%' => $this->massEditLimit]);

            return true;
        }

        return false;
    }

    /**
     * Dispatch mass action
     */
    protected function dispatchMassAction()
    {
        $this->objects = $this->massActionDispatcher->dispatch($this->request);
    }

    /**
     * Get products to mass edit
     *
     * @return array
     */
    protected function getObjects()
    {
        if ($this->objects === null) {
            $this->dispatchMassAction();
        }

        return $this->objects;
    }

    /**
     * Get the count of objects to perform the mass action on
     *
     * @return integer
     */
    protected function getObjectCount()
    {
        return count($this->getObjects());
    }
}
