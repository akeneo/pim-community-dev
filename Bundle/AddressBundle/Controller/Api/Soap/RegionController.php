<?php

namespace Oro\Bundle\AddressBundle\Controller\Api\Soap;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\Common\Persistence\ObjectManager;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\AddressBundle\Entity\Repository\RegionRepository;

class RegionController extends ContainerAware
{
    /**
     * @Soap\Method("getRegions")
     * @Soap\Result(phpType = "Oro\Bundle\AddressBundle\Entity\Region[]")
     * @AclAncestor("oro_address")
     */
    public function cgetAction()
    {
        return $this->getManager()->getRepository('OroAddressBundle:Region')->findAll();
    }

    /**
     * @Soap\Method("getRegion")
     * @Soap\Param("combinedCode", phpType = "string")
     * @Soap\Result(phpType = "Oro\Bundle\AddressBundle\Entity\Region")
     * @AclAncestor("oro_address")
     */
    public function getAction($combinedCode)
    {
        return $this->getEntity('OroAddressBundle:Region', $combinedCode);
    }

    /**
     * @Soap\Method("getRegionByCountry")
     * @Soap\Param("country", phpType = "string")
     * @Soap\Result(phpType = "Oro\Bundle\AddressBundle\Entity\Region[]")
     * @AclAncestor("oro_address")
     */
    public function getByCountryAction(Country $country = null)
    {
        /** @var  RegionRepository $regionRepository */
        $regionRepository = $this->getManager()->getRepository('OroAddressBundle:Region');
        $regions = $regionRepository->getCountryRegions($country);

        return $regions;
    }

    /**
     * @return ObjectManager
     */
    protected function getManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * Shortcut to get entity
     *
     * @param string $repo
     * @param int|string $id
     * @throws \SoapFault
     * @return Address
     */
    protected function getEntity($repo, $id)
    {
        $entity = $this->getManager()->find($repo, $id);

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('Record #%u can not be found', $id));
        }

        return $entity;
    }
}
