<?php

namespace Akeneo\UserManagement\Bundle\Controller\Rest;

use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * User group rest controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserGroupController
{
    protected GroupRepository $groupRepository;

    protected NormalizerInterface $normalizer;

    protected UserContext $userContext;

    private SaverInterface $saver;

    private SimpleFactoryInterface $factory;

    private ObjectUpdaterInterface $updater;

    private ValidatorInterface $validator;

    private NormalizerInterface $constraintViolationNormalizer;

    public function __construct(
        GroupRepository $groupRepository,
        NormalizerInterface $normalizer,
        UserContext $userContext,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        ValidatorInterface $validator,
        NormalizerInterface $constraintViolationNormalizer
    )
    {
        $this->groupRepository = $groupRepository;
        $this->normalizer = $normalizer;
        $this->userContext = $userContext;
        $this->factory = $factory;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
    }

    /**
     * @AclAncestor("pim_user_group_create")
     */
    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $userGroup = $this->factory->create();
        $content = json_decode($request->getContent(), true);

        $this->updater->update($userGroup, $content);
        $violations = $this->validator->validate($userGroup);

        if ($violations->count() > 0) {
            $normalizedViolations = [];
            foreach ($violations as $violation) {
                $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                    $violation,
                    'internal_api',
                    ['userGroup' => $userGroup]
                );
            }

            return new JsonResponse(['values' => $normalizedViolations], Response::HTTP_BAD_REQUEST);
        }

        $this->saver->save($userGroup);

        return new JsonResponse($this->normalizer->normalize($userGroup, 'internal_api'));
    }

    /**
     * @AclAncestor("pim_user_group_edit")
     */
    public function getAction(int $identifier): JsonResponse
    {
        $userGroup = $this->getUserGroupOr404($identifier);

        return new JsonResponse($this->normalizer->normalize($userGroup, 'internal_api'));
    }


    /**
     * Get the list of all user groups
     *
     * @return JsonResponse all user groups
     */
    public function indexAction()
    {
        $userGroups = array_map(function (GroupInterface $group) {
            return [
                'name' => $group->getName(),
                'meta' => [
                    'id'      => $group->getId(),
                    'default' => 'All' === $group->getName()
                ]
            ];
        }, $this->groupRepository->findAll());

        return new JsonResponse($userGroups);
    }

    /**
     * @param $identifier
     * @return Group
     */
    private function getUserGroupOr404($identifier): Group
    {
        $userGroup = $this->groupRepository->findOneBy(['id' => $identifier]);

        if (null === $userGroup) {
            throw new NotFoundHttpException(
                sprintf('The "%s" user group is not found', $identifier)
            );
        }

        return $userGroup;
    }
}
