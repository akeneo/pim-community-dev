<?php

namespace Akeneo\UserManagement\Bundle\Controller\Rest;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleRepository;
use Akeneo\UserManagement\Component\Model\Role;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Role controller
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRoleController
{
    protected RoleRepository $roleRepository;

    protected UserContext $userContext;

    private BulkSaverInterface $saver;

    private SimpleFactoryInterface $factory;

    private ObjectUpdaterInterface $updater;

    private ValidatorInterface $validator;

    private NormalizerInterface $constraintViolationNormalizer;

    private NormalizerInterface $normalizer;

    private ArrayConverterInterface $flatToStandardArrayConverter;

    private SerializerInterface $serializer;

    public function __construct(
        RoleRepository $roleRepository,
        ArrayConverterInterface $flatToStandardArrayConverter,
        NormalizerInterface $normalizer,
        SerializerInterface $serializer,
        UserContext $userContext,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        BulkSaverInterface $saver,
        ValidatorInterface $validator,
        NormalizerInterface $constraintViolationNormalizer
    ) {
        $this->roleRepository = $roleRepository;
        $this->flatToStandardArrayConverter = $flatToStandardArrayConverter;
        $this->normalizer = $normalizer;
        $this->serializer = $serializer;
        $this->userContext = $userContext;
        $this->factory = $factory;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
    }

    /**
     * @AclAncestor("pim_user_role_create")
     */
    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $userRole = $this->factory->create();
        $content = json_decode($request->getContent(), true);

        if(!$content) {
            return new JsonResponse(['message' => 'Invalid json message received'], Response::HTTP_BAD_REQUEST);
        }

        $datas = $this->flatToStandardArrayConverter->convert($content);

        try {
            $this->updater->update($userRole, $datas);
        } catch (UnknownPropertyException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $violations = $this->validator->validate($userRole);

        if ($violations->count() > 0) {
            $normalizedViolations = [];
            foreach ($violations as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api',
                    ['userRole' => $userRole]
                );
            }

            return new JsonResponse(['values' => $normalizedViolations], Response::HTTP_BAD_REQUEST);
        }

        $this->saver->saveAll([$userRole]);

        $userRole = $this->roleRepository->findOneByIdentifier($userRole->role()->getRole());

        return new JsonResponse($this->normalizer->normalize($userRole, 'flat'), Response::HTTP_CREATED);
    }

    /**
     * @AclAncestor("pim_user_role_edit")
     */
    public function getAction(int $identifier): JsonResponse
    {
        $userRole = $this->getUserRoleOr404($identifier);

        return new JsonResponse($this->normalizer->normalize($userRole, 'flat'));
    }

    public function indexAction()
    {
        $queryBuildder = $this->roleRepository->getAllButAnonymousQB();
        $roles = $queryBuildder->getQuery()->execute();

        return new JsonResponse($this->serializer->normalize(
            $roles,
            'internal_api',
            $this->userContext->toArray()
        ));
    }

    private function getUserRoleOr404($identifier): Role
    {
        $userRole = $this->roleRepository->findOneBy(['id' => $identifier]);

        if (null === $userRole) {
            throw new NotFoundHttpException(
                sprintf('User Role with id "%s" not found', $identifier)
            );
        }

        return $userRole;
    }

}
