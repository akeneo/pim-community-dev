<?php

namespace Akeneo\Platform\Bundle\UIBundle\Controller;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ReferenceDataRepositoryInterface;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
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
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /**
     * @param RegistryInterface              $doctrine
     * @param ConfigurationRegistryInterface $registry
     */
    public function __construct(RegistryInterface $doctrine, ConfigurationRegistryInterface $registry)
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
        $query = $request->query;
        $search = $query->get('search');
        $referenceDataName = $query->get('referenceDataName');
        $class = $query->get('class');

        if (null !== $referenceDataName) {
            $class = $this->registry->get($referenceDataName)->getClass();
        }

        $repository = $this->doctrine->getRepository($class);

        if ($repository instanceof ReferenceDataRepositoryInterface) {
            $choices['results'] = $repository->findBySearch(
                $search,
                $query->get('options', [])
            );
        } elseif ($repository instanceof SearchableRepositoryInterface) {
            $choices['results'] = $repository->findBySearch(
                $search,
                $query->get('options', [])
            );
        } elseif (method_exists($repository, 'getOptions')) {
            $choices = $repository->getOptions(
                $query->get('dataLocale'),
                $query->get('collectionId'),
                $search,
                $query->get('options', [])
            );
        } else {
            throw new \LogicException(
                sprintf(
                    'The repository of the class "%s" can not retrieve options via Ajax.',
                    $query->get('class')
                )
            );
        }

        if (
            $query->get('isCreatable') &&
            !empty($search) &&
            !in_array(['id' => $search, 'text' => $search], $choices['results'])
        ) {
            $choices['results'][] = ['id' => $search, 'text' => $search];
        }

        return new JsonResponse($choices);
    }
}
