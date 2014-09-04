<?php

namespace Pim\Bundle\CommentBundle\Controller;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\Common\Persistence\ManagerRegistry;

use Pim\Bundle\CommentBundle\Builder\CommentBuilder;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;

/**
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommentController extends AbstractDoctrineController
{
    /** @var CommentBuilder */
    protected $commentBuilder;

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
        CommentBuilder $commentBuilder
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

        $this->commentBuilder = $commentBuilder;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createAction(Request $request)
    {
        $comment = $this->commentBuilder->buildCommentWithoutSubject($this->getUser());
        $form = $this->createForm(
            'pim_comment_comment',
            $comment
        );

        $form->submit($this->request);
        if ($form->isValid()) {
            $manager = $this->getManagerForClass(ClassUtils::getClass($comment));
            $manager->persist($comment);
            $manager->flush();
        } else {
            $this->addFlash('error', 'flash.comment.invalid');
        }

        return new JsonResponse('OK');
    }

    public function replyAction($name)
    {
    }
    public function deleteAction($name)
    {
    }
}
