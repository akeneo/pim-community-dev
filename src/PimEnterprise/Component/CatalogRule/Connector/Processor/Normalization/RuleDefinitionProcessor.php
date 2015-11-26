<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Connector\Processor\Normalization;

use Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer\Processor;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Processes and transforms rules definition to array of rules
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleDefinitionProcessor extends Processor
{
    /** @var NormalizerInterface */
    protected $ruleNormalizer;

    /**
     * @param SerializerInterface       $serializer
     * @param LocaleRepositoryInterface $localeRepository
     * @param NormalizerInterface       $ruleNormalizer
     */
    public function __construct(
        SerializerInterface $serializer,
        LocaleRepositoryInterface $localeRepository,
        NormalizerInterface $ruleNormalizer
    ) {
        parent::__construct($serializer, $localeRepository);

        $this->ruleNormalizer = $ruleNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $normalizedRule = $this->ruleNormalizer->normalize($item);

        unset($normalizedRule['code']);
        unset($normalizedRule['type']);

        $rule[$item->getCode()] = $normalizedRule;

        return $rule;
    }
}
