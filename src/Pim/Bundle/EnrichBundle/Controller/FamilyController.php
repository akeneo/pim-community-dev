<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Factory\FamilyFactory;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Pim\Component\Enrich\Model\AvailableAttributes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Family controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyController
{
    /** @var Request */
    protected $request;

    /** @var RouterInterface */
    protected $router;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var EngineInterface */

    /** @var TranslatorInterface */
    protected $translator;

    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var FamilyFactory */
    protected $familyFactory;

    /** @var HandlerInterface */
    protected $familyHandler;

    /** @var Form */
    protected $familyForm;

    /** @var string */
    protected $attributeClass;

    /** @var SaverInterface */
    protected $familySaver;

    /** @var RemoverInterface */
    protected $familyRemover;

    /** @var string */
    protected $familyClass;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepo;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param Request                      $request

     * @param RouterInterface              $router
     * @param FormFactoryInterface         $formFactory
     * @param TranslatorInterface          $translator
     * @param ManagerRegistry              $doctrine
     * @param ChannelRepositoryInterface   $channelRepository
     * @param FamilyFactory                $familyFactory
     * @param HandlerInterface             $familyHandler
     * @param Form                         $familyForm
     * @param SaverInterface               $familySaver
     * @param RemoverInterface             $familyRemover
     * @param AttributeRepositoryInterface $attributeRepo
     * @param FamilyRepositoryInterface    $familyRepository
     * @param ValidatorInterface           $validator
     * @param string                       $attributeClass
     * @param string                       $familyClass
     */
    public function __construct(
        Request $request,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator,
        ManagerRegistry $doctrine,
        ChannelRepositoryInterface $channelRepository,
        FamilyFactory $familyFactory,
        HandlerInterface $familyHandler,
        Form $familyForm,
        SaverInterface $familySaver,
        RemoverInterface $familyRemover,
        AttributeRepositoryInterface $attributeRepo,
        FamilyRepositoryInterface $familyRepository,
        ValidatorInterface $validator,
        $attributeClass,
        $familyClass
    ) {
        $this->request = $request;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->doctrine = $doctrine;
        $this->channelRepository = $channelRepository;
        $this->familyFactory = $familyFactory;
        $this->familyHandler = $familyHandler;
        $this->familyForm = $familyForm;
        $this->attributeClass = $attributeClass;
        $this->familySaver = $familySaver;
        $this->familyRemover = $familyRemover;
        $this->familyClass = $familyClass;
        $this->attributeRepo = $attributeRepo;
        $this->familyRepository = $familyRepository;
        $this->validator = $validator;
    }

    /**
     * List families
     *
     * @Template
     * @AclAncestor("pim_enrich_family_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * Create a family
     *
     * @Template
     * @AclAncestor("pim_enrich_family_create")
     *
     * @return array
     */
    public function createAction()
    {
        if (!$this->request->isXmlHttpRequest()) {
            return new RedirectResponse($this->router->generate('pim_enrich_family_index'));
        }

        $family = $this->familyFactory->create();

        if ($this->familyHandler->process($family)) {
            $this->request->getSession()->getFlashBag()->add('success', new Message('flash.family.created'));

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

    /**
     * Edit a family
     *
     * @param int $code
     *
     * @Template
     * @AclAncestor("pim_enrich_family_index")
     *
     * @return array|Response
     */
    public function editAction($code)
    {
        return [
            'code' => $code,
        ];
    }
}
