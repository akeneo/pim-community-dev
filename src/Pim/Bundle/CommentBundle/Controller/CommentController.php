<?php

namespace Pim\Bundle\CommentBundle\Controller;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\Common\Persistence\ManagerRegistry;

use Pim\Bundle\CommentBundle\Entity\Comment;
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

    /** @var string */
    protected $commentClassName;

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
        CommentBuilder $commentBuilder,
        $commentClassName
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
        $this->commentClassName = $commentClassName;
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \LogicException
     */
    public function createAction(Request $request)
    {
        if (true !== $request->isXmlHttpRequest()) {
            throw new \LogicException('The request should be an Xml Http request.');
        }

        $comment = $this->commentBuilder->buildCommentWithoutSubject($this->getUser());
        $createForm = $this->createForm('pim_comment_comment', $comment);
        $createForm->submit($this->request);

        if (true !== $createForm->isValid()) {
            return new JsonResponse('The form is not valid.', 400);
        }

        $manager = $this->getManagerForClass(ClassUtils::getClass($comment));
        $manager->persist($comment);
        $manager->flush();

        $reply = $this->commentBuilder->buildReply($comment, $this->getUser());
        $replyForm = $this->createForm('pim_comment_comment', $reply, ['is_reply' => true]);

        return $this->render(
            'PimCommentBundle:Comment:_thread.html.twig',
            [
                'replyForms' => [$comment->getId() => $replyForm->createView()],
                'comment' => $comment,
            ]
        );
    }

    /**
     * Reply to a comment
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \LogicException
     */
    public function replyAction(Request $request)
    {
        if (true !== $request->isXmlHttpRequest()) {
            throw new \LogicException('The request should be an Xml Http request.');
        }

        $reply = new Comment();
        $replyForm = $this->createForm('pim_comment_comment', $reply, ['is_reply' => true]);
        $replyForm->submit($this->request);

        if (true !== $replyForm->isValid()) {
            return new JsonResponse('The form is not valid.', 400);
        }

        $now = new \DateTime();
        $reply->setCreatedAt($now);
        $reply->setRepliedAt($now);
        $reply->setAuthor($this->getUser());
        $comment = $reply->getParent();
        $comment->setRepliedAt($now);

        $manager = $this->getManagerForClass($this->commentClassName);
        $manager->persist($reply);
        $manager->flush();

        return $this->render(
            'PimCommentBundle:Comment:_thread.html.twig',
            [
                'replyForms' => [$comment->getId() => $replyForm->createView()],
                'comment' => $comment,
            ]
        );
    }

    /**
     * Delete a comment with its children
     *
     * @param Request $request
     * @param $id
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAction(Request $request, $id)
    {
        $manager = $this->getManagerForClass($this->commentClassName);
        $comment = $manager->find($this->commentClassName, $id);

        if (null === $comment) {
            throw new NotFoundHttpException(sprintf('Comment with id %s not found.', $id));
        }

        if ($comment->getAuthor() !== $this->getUser()) {
            throw new AccessDeniedException('You are not allowed to delete this comment.');
        }

        $manager->remove($comment);
        $manager->flush();

        return new JsonResponse();
    }
}
