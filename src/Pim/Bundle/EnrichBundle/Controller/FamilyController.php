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

    /** @var FamilyFactory */
    protected $familyFactory;

    /** @var HandlerInterface */
    protected $familyHandler;

    /** @var Form */
    protected $familyForm;

    /**
     * @param Request                      $request
     * @param RouterInterface              $router
     * @param FamilyFactory                $familyFactory
     * @param HandlerInterface             $familyHandler
     */
    public function __construct(
        Request $request,
        RouterInterface $router,
        FamilyFactory $familyFactory,
        HandlerInterface $familyHandler,
        Form $familyForm
    ) {
        $this->request = $request;
        $this->router = $router;
        $this->familyFactory = $familyFactory;
        $this->familyHandler = $familyHandler;
        $this->familyForm = $familyForm;
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
     * @return Response|array
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
