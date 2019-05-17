<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var ObjectManager */
    protected $doctrine;

    /** @var RemoverInterface */
    protected $commentRemover;

    /** @var string */
    protected $commentClassName;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param ObjectManager         $doctrine
     * @param RemoverInterface      $commentRemover
     * @param string                $commentClassName
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        ObjectManager $doctrine,
        RemoverInterface $commentRemover,
        $commentClassName
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->doctrine = $doctrine;
        $this->commentRemover = $commentRemover;
        $this->commentClassName = $commentClassName;
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
     * @return Response
     */
    public function deleteAction(Request $request, $id)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $comment = $this->doctrine->find($this->commentClassName, $id);

        if (null === $comment) {
            throw new NotFoundHttpException(sprintf('Comment with id %s not found.', $id));
        }

        if ($comment->getAuthor() !== $this->getUser()) {
            throw new AccessDeniedException('You are not allowed to delete this comment.');
        }

        $this->commentRemover->remove($comment);

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
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }
}
