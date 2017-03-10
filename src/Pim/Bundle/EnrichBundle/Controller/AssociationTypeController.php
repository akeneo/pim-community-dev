<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Pim\Component\Catalog\Repository\AssociationRepositoryInterface;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Association type controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeController
{
    /** @var Request */
    protected $request;

    /** @var RouterInterface */
    protected $router;

    /** @var HandlerInterface */
    protected $assocTypeHandler;

    /** @var Form */
    protected $assocTypeForm;

    /** @var AssociationRepositoryInterface */
    protected $assocRepository;

    /** @var RemoverInterface */
    protected $assocTypeRemover;

    /** @var AssociationTypeRepositoryInterface */
    protected $assocTypeRepo;

    /**
     * @param Request                            $request
     * @param RouterInterface                    $router
     * @param TranslatorInterface                $translator
     * @param AssociationRepositoryInterface     $assocRepository
     * @param HandlerInterface                   $assocTypeHandler
     * @param Form                               $assocTypeForm
     * @param RemoverInterface                   $assocTypeRemover
     * @param AssociationTypeRepositoryInterface $assocTypeRepo
     */
    public function __construct(
        Request $request,
        RouterInterface $router,
        TranslatorInterface $translator,
        AssociationRepositoryInterface $assocRepository,
        HandlerInterface $assocTypeHandler,
        Form $assocTypeForm,
        RemoverInterface $assocTypeRemover,
        AssociationTypeRepositoryInterface $assocTypeRepo
    ) {
        $this->request = $request;
        $this->router = $router;
        $this->translator = $translator;
        $this->assocRepository = $assocRepository;
        $this->assocTypeHandler = $assocTypeHandler;
        $this->assocTypeForm = $assocTypeForm;
        $this->assocTypeRemover = $assocTypeRemover;
        $this->assocTypeRepo = $assocTypeRepo;
    }

    /**
     * List association types
     *
     * @Template
     * @AclAncestor("pim_enrich_associationtype_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * Create an association type
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_associationtype_create")
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $associationType = new AssociationType();

        if ($this->assocTypeHandler->process($associationType)) {
            $this->request->getSession()->getFlashBag()->add('success', new Message('flash.association type.created'));

            $response = [
                'status' => 1,
                'url'    => $this->router->generate(
                    'pim_enrich_associationtype_edit',
                    ['code' => $associationType->getCode()]
                )
            ];

            return new Response(json_encode($response));
        }

        return [
            'form' => $this->assocTypeForm->createView(),
        ];
    }

    /**
     * Edit an association type
     *
     * @param string $code
     *
     * @Template
     * @AclAncestor("pim_enrich_associationtype_edit")
     *
     * @return array
     */
    public function editAction($code)
    {
        return [
            'code'       => $code
        ];
    }
}
