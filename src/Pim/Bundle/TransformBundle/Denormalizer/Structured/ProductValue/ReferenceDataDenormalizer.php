<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataDenormalizer extends AbstractValueDenormalizer
{
    /** @var ConfigurationRegistryInterface */
    protected $registry;

    /** @var RegistryInterface */
    protected $doctrine;

    /**
     * @param array                          $supportedTypes
     * @param ConfigurationRegistryInterface $registry
     * @param RegistryInterface              $doctrine
     */
    public function __construct(
        array $supportedTypes,
        ConfigurationRegistryInterface $registry,
        RegistryInterface $doctrine
    ) {
        parent::__construct($supportedTypes);

        $this->registry = $registry;
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $referenceDataClass, $format = null, array $context = array())
    {
        if (empty($data)) {
            return null;
        }

        if (false === isset($context['attribute'])) {
            throw new InvalidParameterException(
                sprintf('Denormalizer\'s context expected to have an attribute, none found.')
            );
        }

        $attribute = $context['attribute'];

        if (!$attribute instanceof AttributeInterface) {
            throw new InvalidParameterException(
                sprintf('Attribute is not an instance of Pim\Bundle\CatalogBundle\Model\AttributeInterface.')
            );
        }

        $referenceDataConf = $this->registry->get($attribute->getReferenceDataName());
        $referenceDataClass = $referenceDataConf->getClass();

        $repository = $this->doctrine->getRepository($referenceDataClass);
        $referenceData = $repository->findOneBy(['code' => $data['code']]);

        return $referenceData;
    }
}
