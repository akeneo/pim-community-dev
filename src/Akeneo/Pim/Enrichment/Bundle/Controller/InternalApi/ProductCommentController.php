<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Pim\Enrichment\Bundle\Form\Type\CommentType;
use Akeneo\Pim\Enrichment\Component\Comment\Builder\CommentBuilder;
use Akeneo\Pim\Enrichment\Component\Comment\Repository\CommentRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controller for product comments
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCommentController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly FormFactoryInterface $formFactory,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CommentRepositoryInterface $commentRepository,
        private readonly SaverInterface $commentSaver,
        private readonly CommentBuilder $commentBuilder,
        private readonly NormalizerInterface $normalizer,
        private readonly ValidatorInterface $validator,
        private readonly PresenterInterface $datetimePresenter,
        private readonly LocaleResolver $localeResolver,
        private readonly UserContext $userContext,
    ) {
    }

    /**
     * List comments made on a product
     *
     * @param string $uuid
     *
     * @AclAncestor("pim_enrich_product_comment")
     *
     * @return JsonResponse
     */
    public function getAction($uuid)
    {
        $product = $this->findProductOr404($uuid);
        $comments = $this->commentRepository->getCommentsByUuid(
            ClassUtils::getClass($product),
            $product->getUuid()
        );

        $comments = $this->normalizer->normalize($comments, 'standard');

        foreach ($comments as $commentKey => $comment) {
            $comments[$commentKey]['created'] = $this->presentDate($comment['created']);
            $comments[$commentKey]['replied'] = $this->presentDate($comment['replied']);

            foreach ($comment['replies'] as $replyKey => $reply) {
                $comments[$commentKey]['replies'][$replyKey]['created'] = $this->presentDate($reply['created']);
                $comments[$commentKey]['replies'][$replyKey]['replied'] = $this->presentDate($reply['created']);
            }
        }

        return new JsonResponse($comments);
    }

    /**
     * Create a comment on a product
     */
    public function postAction(Request $request, string $uuid): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductOr404($uuid);
        $data = json_decode($request->getContent(), true);
        $comment = $this->commentBuilder->buildComment($product, $this->getUser());
        $form = $this->formFactory->create(CommentType::class, $comment, ['csrf_protection' => false]);
        $form->submit($data, false);

        if ($form->isValid()) {
            $this->commentSaver->save($comment);

            return new JsonResponse($this->normalizer->normalize($comment, 'standard'));
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
     * @param string $commentId
     */
    public function postReplyAction(Request $request, string $uuid, $commentId): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $product = $this->findProductOr404($uuid);

        $data = json_decode($request->getContent(), true);
        $data['parent'] = $commentId;

        $reply = $this->commentBuilder->buildComment($product, $this->getUser());
        $form = $this->formFactory->create(
            CommentType::class,
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

            $this->commentSaver->save($reply);

            return new JsonResponse($this->normalizer->normalize($reply, 'standard'));
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
     * @throws NotFoundHttpException
     */
    protected function findProductOr404(string $uuid): ProductInterface
    {
        $product = $this->productRepository->find($uuid);

        if (!$product) {
            throw new NotFoundHttpException(
                sprintf('Product with uuid %s could not be found.', $uuid)
            );
        }

        return $product;
    }

    /**
     * Get the user from the Security Context
     *
     * @return UserInterface|null
     */
    protected function getUser()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    /**
     * @param string $date
     *
     * @return string
     */
    protected function presentDate($date)
    {
        $context = [
            'locale' => $this->localeResolver->getCurrentLocale(),
            'timezone' => $this->userContext->getUserTimezone(),
        ];
        $dateTime = new \DateTime($date);

        return $this->datetimePresenter->present($dateTime, $context);
    }
}
