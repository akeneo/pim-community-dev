<?php

namespace Akeneo\Platform\Bundle\UIBundle\Controller;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for ajax choices
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AjaxOptionController
{
    protected ManagerRegistry $doctrine;
    protected ConfigurationRegistryInterface $registry;

    public function __construct(ManagerRegistry $doctrine, ConfigurationRegistryInterface $registry)
    {
        $this->doctrine = $doctrine;
        $this->registry = $registry;
    }

    /**
     * Returns a JSON response containing options
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $search = $request->get('search');
        $referenceDataName = $request->get('referenceDataName');
        $class = $request->get('class');

        if (null !== $referenceDataName) {
            $class = $this->registry->get($referenceDataName)->getClass();
        }

        $repository = $this->doctrine->getRepository($class);

        if ($repository instanceof ReferenceDataRepositoryInterface) {
            $choices['results'] = $repository->findBySearch(
                $search,
                $request->get('options', [])
            );
        } elseif ($repository instanceof SearchableRepositoryInterface) {
            $choices['results'] = $repository->findBySearch(
                $search,
                $request->get('options', [])
            );
        } elseif (method_exists($repository, 'getOptions')) {
            $choices = $repository->getOptions(
                $request->get('dataLocale'),
                $request->get('collectionId'),
                $search,
                $request->get('options', [])
            );
        } else {
            throw new \LogicException(
                sprintf(
                    'The repository of the class "%s" can not retrieve options via Ajax.',
                    $request->get('class')
                )
            );
        }

        if (
            $request->get('isCreatable') &&
            !empty($search) &&
            !in_array(['id' => $search, 'text' => $search], $choices['results'])
        ) {
            $choices['results'][] = ['id' => $search, 'text' => $search];
        }

        return new JsonResponse($choices);
    }
}
