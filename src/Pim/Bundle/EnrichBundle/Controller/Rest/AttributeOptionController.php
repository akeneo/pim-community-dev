<?php

namespace Pim\Bundle\EnrichBundle\Controller\Rest;

use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\AttributeOptionSearchableRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute Option controller
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionController
{
    /** @var AttributeOptionSearchableRepository */
    protected $attributeOptionSearchableRepo;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * @param AttributeOptionSearchableRepository $attributeOptionSearchableRepo
     * @param NormalizerInterface                 $normalizer
     */
    public function __construct(
        AttributeOptionSearchableRepository $attributeOptionSearchableRepo,
        NormalizerInterface $normalizer
    ) {
        $this->attributeOptionSearchableRepo = $attributeOptionSearchableRepo;
        $this->normalizer                    = $normalizer;
    }

    /**
     * Return the attribute option array
     *
     * TODO in 2.3 pull up: this action should only respond to POST method
     *
     * @param Request $request
     * @param int     $identifier
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, $identifier)
    {
        $query  = $request->query;
        $search = $query->get('search');

        $options = $query->get('options', []);
        $options['identifier'] = $identifier;

        $attributeOptions = $this->attributeOptionSearchableRepo->findBySearch(
            $search,
            $options
        );

        $normalizedAttributeOptions = [];
        foreach ($attributeOptions as $attributeOption) {
            $normalizedAttributeOptions[] = $this->normalizer->normalize(
                $attributeOption,
                'json',
                ['onlyActivatedLocales' => true]
            );
        }

        return new JsonResponse($normalizedAttributeOptions);
    }
}
