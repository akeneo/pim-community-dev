<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CommentBundle\Builder\CommentBuilder;
use Pim\Bundle\CommentBundle\Manager\CommentManager;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manage comments on a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCommentController
{
    /** @var EngineInterface */
    protected $templating;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var ProductManager */
    protected $productManager;

    /** @var CommentManager */
    protected $commentManager;

    /** @var CommentBuilder */
    protected $commentBuilder;

    /**
     * Constructor
     *
     * @param EngineInterface          $templating
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ProductManager           $productManager
     * @param CommentManager           $commentManager
     * @param CommentBuilder           $commentBuilder
     */
    public function __construct(
        EngineInterface $templating,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ProductManager $productManager,
        CommentManager $commentManager,
        CommentBuilder $commentBuilder
    ) {
        $this->templating      = $templating;
        $this->securityContext = $securityContext;
        $this->formFactory     = $formFactory;
        $this->productManager  = $productManager;
        $this->commentManager  = $commentManager;
        $this->commentBuilder  = $commentBuilder;
    }

    /**
     * List comments made on a product
     *
     * @param Request        $request
     * @param integer|string $id
     *
     * @AclAncestor("pim_enrich_product_comment")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listCommentsAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);
        $comment = $this->commentBuilder->buildComment($product, $this->getUser());
        $createForm = $this->formFactory->create('pim_comment_comment', $comment);

        $comments = $this->commentManager->getComments($product);
        $replyForms = [];

        foreach ($comments as $comment) {
            $reply = $this->commentBuilder->buildReply($comment, $this->getUser());
            $replyForm = $this->formFactory->create('pim_comment_comment', $reply, ['is_reply' => true]);
            $replyForms[$comment->getId()] = $replyForm->createView();
        }

        return $this->templating->renderResponse(
            'PimCommentBundle:Comment:_commentList.html.twig',
            [
                'createForm' => $createForm->createView(),
                'replyForms' => $replyForms,
                'comments'   => $comments,
            ]
        );
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param integer $id the product id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface
     */
    protected function findProductOr404($id)
    {
        $product = $this->productManager->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                sprintf('Product with id %s could not be found.', (string) $id)
            );
        }

        return $product;
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
}
