<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Pim\Component\Catalog\Factory\FamilyFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Family controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyController
{
    /** @var RequestStack */
    protected $requestStack;

    /** @var RouterInterface */
    protected $router;

    /** @var FamilyFactory */
    protected $familyFactory;

    /** @var HandlerInterface */
    protected $familyHandler;

    /** @var Form */
    protected $familyForm;

    /**
     * @param RequestStack     $requestStack
     * @param RouterInterface  $router
     * @param FamilyFactory    $familyFactory
     * @param HandlerInterface $familyHandler
     */
    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router,
        FamilyFactory $familyFactory,
        HandlerInterface $familyHandler,
        Form $familyForm
    ) {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->familyFactory = $familyFactory;
        $this->familyHandler = $familyHandler;
        $this->familyForm = $familyForm;
    }

    /**
     * Create a family
     *
     * @Template
     * @AclAncestor("pim_enrich_family_create")
     *
     * @return Response|array
     */
    public function createAction()
    {
        $family = $this->familyFactory->create();

        if ($this->familyHandler->process($family)) {
            $this
                ->requestStack
                ->getCurrentRequest()
                ->getSession()
                ->getFlashBag()
                ->add('success', new Message('flash.family.created'));

            $response = [
                'status' => 1,
                'url'    => $this->router->generate(
                    'pim_enrich_family_edit',
                    ['code' => $family->getCode()]
                )
            ];

            return new Response(json_encode($response));
        }

        return [
            'form' => $this->familyForm->createView()
        ];
    }
}
