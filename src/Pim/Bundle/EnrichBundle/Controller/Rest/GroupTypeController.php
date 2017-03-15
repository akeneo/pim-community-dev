<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Group type controller
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeController
{
    /** @var GroupTypeRepositoryInterface */
    protected $groupTypeRepo;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var RemoverInterface */
    protected $remover;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var SaverInterface */
    protected $saver;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var UserContext */
    protected $userContext;

    /**
     * @param GroupTypeRepositoryInterface $groupTypeRepo
     * @param NormalizerInterface                $normalizer
     */
    public function __construct(
        GroupTypeRepositoryInterface $groupTypeRepo,
        NormalizerInterface $normalizer,
        RemoverInterface $remover,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        ValidatorInterface $validator,
        UserContext $userContext
    ) {
        $this->groupTypeRepo = $groupTypeRepo;
        $this->normalizer = $normalizer;
        $this->remover = $remover;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
        $this->userContext = $userContext;
    }

    /**
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_grouptype_index")
     */
    public function indexAction()
    {
        $groupTypes = $this->groupTypeRepo->findAll();

        $data = $this->normalizer->normalize($groupTypes, 'internal_api');

        return new JsonResponse($data);
    }

    /**
     * @param string $identifier
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_grouptype_edit")
     */
    public function getAction($identifier)
    {
        $groupType = $this->getGroupTypeOr404($identifier);

        return new JsonResponse(
            $this->normalizer->normalize($groupType, 'standard')
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_grouptype_edit")
     */
    public function postAction(Request $request, $code)
    {
        $groupType = $this->getGroupTypeOr404($code);

        $data = json_decode($request->getContent(), true);

        $this->updater->update($groupType, $data);

        $violations = $this->validator->validate($groupType);

        if (0 < $violations->count()) {
            $errors = $this->normalizer->normalize(
                $violations,
                'standard'
            );

            return new JsonResponse($errors, 400);
        }

        $this->saver->save($groupType);

        return new JsonResponse(
            $this->normalizer->normalize(
                $groupType,
                'standard',
                $this->userContext->toArray()
            )
        );
    }

    /**
     * Remove action
     *
     * @param $code
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_grouptype_remove")
     */
    public function removeAction($code)
    {
        $groupType = $this->getGroupTypeOr404($code);

        $this->remover->remove($groupType);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Finds group type by code or throws not found exception
     *
     * @param $code
     *
     * @throws NotFoundHttpException
     *
     * @return GroupTypeInterface
     */
    private function getGroupTypeOr404($code)
    {
        $groupType = $this->groupTypeRepo->findOneBy(
            ['code' => $code]
        );
        if (null === $groupType) {
            throw new NotFoundHttpException(
                sprintf('Group type with code "%s" not found', $code)
            );
        }

        return $groupType;
    }
}
