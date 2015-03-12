<?php

namespace Pim\Bundle\UIBundle\Controller;

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
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * Constructor
     *
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
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

        if (null === $query->get('collectionId') && $query->get('class') === 'PimCatalogBundle:AttributeOption') {
            $code = $query->get('collectionCode');

            $attribute = $this->doctrine->getRepository('PimCatalogBundle:Attribute')->findOneByIdentifier($code);

            $collectionId = $attribute->getId();
        } else {
            $collectionId = $query->get('collectionId');
        }

        $choices = $this->doctrine->getRepository($query->get('class'))
            ->getOptions(
                $query->get('dataLocale'),
                $collectionId,
                $query->get('search'),
                $query->get('options', array())
            );

        return new JsonResponse($choices);
    }
}
