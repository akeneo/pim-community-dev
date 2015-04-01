<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValue;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\ReferenceDataBundle\Doctrine\ReferenceDataRepositoryResolver;
use Symfony\Component\Routing\Exception\InvalidParameterException;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReferenceDataDenormalizer extends AbstractValueDenormalizer
{
    /** @var ReferenceDataRepositoryResolver */
    protected $repositoryResolver;

    /**
     * @param array                           $supportedTypes
     * @param ReferenceDataRepositoryResolver $repositoryResolver
     */
    public function __construct(
        array $supportedTypes,
        ReferenceDataRepositoryResolver $repositoryResolver
    ) {
        parent::__construct($supportedTypes);
        $this->repositoryResolver = $repositoryResolver;
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

        $repository = $this->repositoryResolver->resolve($attribute->getReferenceDataName());
        $referenceData = $repository->findOneBy(['code' => $data]);

        return $referenceData;
    }
}
