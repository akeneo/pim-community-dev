<?php

namespace Pim\Bundle\CommentBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CommentBundle\Builder\CommentBuilder;
use Pim\Bundle\CommentBundle\Manager\CommentManager;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Comment controller
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommentController
{
    /** @var EngineInterface */
    protected $templating;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var CommentBuilder */
    protected $commentBuilder;

    /** @var string */
    protected $commentClassName;

    /** @var CommentManager */
    protected $commentManager;

    /**
     * @param EngineInterface          $templating
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ManagerRegistry          $doctrine
     * @param CommentManager           $commentManager
     * @param CommentBuilder           $commentBuilder
     * @param string                   $commentClassName
     */
    public function __construct(
        EngineInterface $templating,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ManagerRegistry $doctrine,
        CommentManager $commentManager,
        CommentBuilder $commentBuilder,
        $commentClassName
    ) {
        $this->templating       = $templating;
        $this->securityContext  = $securityContext;
        $this->formFactory      = $formFactory;
        $this->doctrine         = $doctrine;
        $this->commentBuilder   = $commentBuilder;
        $this->commentClassName = $commentClassName;
        $this->commentManager   = $commentManager;
    }

    /**
     * @param Request $request
     *
     * @throws \LogicException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        if (true !== $request->isXmlHttpRequest()) {
            throw new \LogicException('The request should be an Xml Http request.');
        }

        $comment = $this->commentBuilder->buildCommentWithoutSubject($this->getUser());
        $createForm = $this->formFactory->create('pim_comment_comment', $comment);
        $createForm->submit($request);

        if (true !== $createForm->isValid()) {
            return new JsonResponse('The form is not valid.', 400);
        }

        $this->commentManager->save($comment);

        $reply = $this->commentBuilder->buildReply($comment, $this->getUser());
        $replyForm = $this->formFactory->create('pim_comment_comment', $reply, ['is_reply' => true]);

        return $this->templating->renderResponse(
            'PimCommentBundle:Comment:_thread.html.twig',
            [
                'replyForms' => [$comment->getId() => $replyForm->createView()],
                'comment'    => $comment,
            ]
        );
    }

    /**
     * Reply to a comment
     *
     * @param Request $request
     *
     * @throws \LogicException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function replyAction(Request $request)
    {
        if (true !== $request->isXmlHttpRequest()) {
            throw new \LogicException('The request should be an Xml Http request.');
        }

        $reply = $this->commentBuilder->newInstance();
        $replyForm = $this->formFactory->create('pim_comment_comment', $reply, ['is_reply' => true]);
        $replyForm->submit($request);

        if (true !== $replyForm->isValid()) {
            return new JsonResponse('The form is not valid.', 400);
        }

        $now = new \DateTime();
        $reply->setCreatedAt($now);
        $reply->setRepliedAt($now);
        $reply->setAuthor($this->getUser());
        $comment = $reply->getParent();
        $comment->setRepliedAt($now);

        $this->commentManager->save($reply);

        return $this->templating->renderResponse(
            'PimCommentBundle:Comment:_thread.html.twig',
            [
                'replyForms' => [$comment->getId() => $replyForm->createView()],
                'comment'    => $comment,
            ]
        );
    }

    /**
     * Delete a comment with its children
     *
     * @param Request $request
     * @param string  $id
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
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

        $this->commentManager->remove($comment);

        return new JsonResponse();
    }

    /**
     * Get a user from the Security Context
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface|null
     *
     * @see Symfony\Component\Security\Core\Authentication\Token\TokenInterface::getUser()
     */
    public function getUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    /**
     * Returns the Doctrine manager for the given class
     *
     * @param string $class
     *
     * @return ObjectManager
     */
    protected function getManagerForClass($class)
    {
        return $this->doctrine->getManagerForClass($class);
    }
}
