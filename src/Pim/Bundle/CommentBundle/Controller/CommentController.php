<?php

namespace Pim\Bundle\CommentBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CommentBundle\Manager\CommentManager;
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
    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var CommentManager */
    protected $commentManager;

    /** @var string */
    protected $commentClassName;

    /**
     * @param SecurityContextInterface $securityContext
     * @param ManagerRegistry          $doctrine
     * @param CommentManager           $commentManager
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        ManagerRegistry $doctrine,
        CommentManager $commentManager,
        $commentClassName
    ) {
        $this->securityContext  = $securityContext;
        $this->doctrine         = $doctrine;
        $this->commentManager   = $commentManager;
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
