<?php

namespace Oro\Bundle\AddressBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\AddressBundle\Entity\Country;

/**
 * @RouteResource("country/regions")
 * @NamePrefix("oro_api_country_")
 */
class CountryRegionsController extends FOSRestController
{
    /**
     * REST GET regions by country
     *
     * @param Country $country
     *
     * @ApiDoc(
     *  description="Get regions by country id",
     *  resource=true
     * )
     * @return Response
     */
    public function getAction(Country $country = null)
    {
        if (!$country) {
            return $this->handleView(
                $this->view(null, Codes::HTTP_NOT_FOUND)
            );
        }

        /** @var $countryRepository EntityRepository */
        $countryRepository = $this->getDoctrine()->getRepository('OroAddressBundle:Region');
        $query = $countryRepository->createQueryBuilder('r')
            ->where('r.country = :country')
            ->orderBy('r.name', 'ASC')
            ->setParameter('country', $country)
            ->getQuery();

        $query->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );

        return $this->handleView(
            $this->view($query->execute(), Codes::HTTP_OK)
        );
    }
}
