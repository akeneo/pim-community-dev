<?php

namespace Oro\Bundle\AddressBundle\Controller\Api\Soap;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\Common\Persistence\ObjectManager;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\AddressBundle\Entity\Address;

class RegionController extends ContainerAware
{
    /**
     * @Soap\Method("getRegions")
     * @Soap\Result(phpType = "Oro\Bundle\AddressBundle\Entity\Region[]")
     */
    public function cgetAction()
    {
        return $this->getManager()->getRepository('OroAddressBundle:Region')->findAll();
    }

    /**
     * @Soap\Method("getRegion")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\AddressBundle\Entity\Region")
     */
    public function getAction($id)
    {
        return $this->getEntity('OroAddressBundle:Region', (int)$id);
    }

    /**
     * @Soap\Method("getRegionByCountry")
     * @Soap\Param("country_id", phpType = "string")
     * @Soap\Param("code", phpType = "string")
     * @Soap\Result(phpType = "Oro\Bundle\AddressBundle\Entity\Region")
     */
    public function getByCountryAction($country_id, $code)
    {
        return $this->getManager()->getRepository('OroAddressBundle:Region')->findOneBy(
            array(
                'country' => $country_id,
                'code'    => $code,
            )
        );
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
