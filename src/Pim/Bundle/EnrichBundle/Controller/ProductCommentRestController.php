<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CommentBundle\Builder\CommentBuilder;
use Pim\Bundle\CommentBundle\Manager\CommentManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Controller for product comments
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCommentRestController
{
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

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * Constructor
     *
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ProductManager           $productManager
     * @param CommentManager           $commentManager
     * @param CommentBuilder           $commentBuilder
     * @param NormalizerInterface      $normalizer
     * @param ValidatorInterface       $validator
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ProductManager $productManager,
        CommentManager $commentManager,
        CommentBuilder $commentBuilder,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator
    ) {
        $this->securityContext = $securityContext;
        $this->formFactory     = $formFactory;
        $this->productManager  = $productManager;
        $this->commentManager  = $commentManager;
        $this->commentBuilder  = $commentBuilder;
        $this->normalizer      = $normalizer;
        $this->validator       = $validator;
    }

    /**
     * List comments made on a product
     *
     * @param Request    $request
     * @param int|string $id
     *
     * @AclAncestor("pim_enrich_product_comment")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);
        $comments = $this->commentManager->getComments($product);
        $class = $this->productManager->getProductName();

        return new JsonResponse($this->normalizer->normalize($comments, 'json'));
    }

    /**
     * Create a comment on a product
     *
     * @param Request $request
     * @param string  $id
     *
     * @return JsonResponse
     */
    public function postAction(Request $request, $id)
    {
        $product = $this->findProductOr404($id);

        $data = json_decode($request->getContent(), true);

        $comment = $this->commentBuilder->buildComment($product, $this->getUser());

        $form = $this->formFactory->create('pim_comment_comment', $comment, ['csrf_protection' => false]);

        $form->submit($data, false);

        if ($form->isValid()) {
            $this->commentManager->save($comment);

            return new JsonResponse($this->normalizer->normalize($comment, 'json'));
        }

        $violations = $this->validator->validate($comment);

        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = [
                'message'       => $violation->getMessage(),
                'invalid_value' => $violation->getInvalidValue()
            ];
        }

        return new JsonResponse($errors, 400);
    }

    /**
     * Reply to a product comment
     *
     * @param Request $request
     * @param string  $id
     * @param string  $commentId
     *
     * @return JsonResponse
     */
    public function postReplyAction(Request $request, $id, $commentId)
    {
        $product = $this->findProductOr404($id);

        $data = json_decode($request->getContent(), true);
        $data['parent'] = $commentId;

        $reply = $this->commentBuilder->buildComment($product, $this->getUser());
        $form = $this->formFactory->create(
            'pim_comment_comment',
            $reply,
            ['is_reply' => true, 'csrf_protection' => false]
        );
        $form->submit($data, false);

        if ($form->isValid()) {
            $now = new \DateTime();
            $reply->setCreatedAt($now);
            $reply->setRepliedAt($now);
            $comment = $reply->getParent();
            $comment->setRepliedAt($now);

            $this->commentManager->save($reply);

            return new JsonResponse($this->normalizer->normalize($reply, 'json'));
        }

        $violations = $this->validator->validate($reply);

        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = [
                'message'       => $violation->getMessage(),
                'invalid_value' => $violation->getInvalidValue()
            ];
        }

        return new JsonResponse($errors, 400);
    }

    /**
     * Find a product by its id or return a 404 response
     *
     * @param int $id the product id
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
     * Get the user from the Security Context
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface|null
     */
    protected function getUser()
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
