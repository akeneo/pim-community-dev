<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Persistence\ManagerRegistry;
use PimEnterprise\Component\CatalogRule\Model\RuleRelationInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Rule relation normalizer
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class RuleRelationNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['internal_api'];

    /** @var ManagerRegistry */
    protected $doctrine;

    /**
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($ruleRelation, $format = null, array $context = [])
    {
        return [
            'rule'      => $ruleRelation->getRuleDefinition()->getCode(),
            'attribute' => $this->doctrine->getManagerForClass($ruleRelation->getResourceName())
                ->find($ruleRelation->getResourceName(), $ruleRelation->getResourceId())->getCode()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof RuleRelationInterface && in_array($format, $this->supportedFormats);
    }
}
