<?php

namespace Pim\Bundle\CommentBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CommentBundle\Builder\CommentBuilder;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;

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
    /** @var Request */
    protected $request;

    /** @var EngineInterface */
    protected $templating;

    /** @var RouterInterface */
    protected $router;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var CommentBuilder */
    protected $commentBuilder;

    /** @var string */
    protected $commentClassName;

    /**
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry          $doctrine
     * @param CommentBuilder           $commentBuilder
     * @param string                   $commentClassName
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
        CommentBuilder $commentBuilder,
        $commentClassName
    ) {
        $this->request         = $request;
        $this->templating      = $templating;
        $this->router          = $router;
        $this->securityContext = $securityContext;
        $this->formFactory     = $formFactory;
        $this->validator       = $validator;
        $this->translator      = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->doctrine = $doctrine;
        $this->commentBuilder = $commentBuilder;
        $this->commentClassName = $commentClassName;
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

        $manager->remove($comment);
        $manager->flush();

        return new JsonResponse();
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param string|FormTypeInterface $type    The built type of the form
     * @param mixed                    $data    The initial data for the form
     * @param array                    $options Options for the form
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createForm($type, $data = null, array $options = array())
    {
        return $this->formFactory->create($type, $data, $options);
    }

    /**
     * Renders a view.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Response $response   A response instance
     *
     * @return Response A Response instance
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        return $this->templating->renderResponse($view, $parameters, $response);
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
