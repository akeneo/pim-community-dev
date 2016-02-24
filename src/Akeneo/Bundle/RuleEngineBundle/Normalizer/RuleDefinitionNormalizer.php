<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Normalizer;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Component\Localization\Presenter\PresenterRegistryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Rule definition normalizer for internal api
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class RuleDefinitionNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormats = ['array'];

    /** @var PresenterRegistryInterface */
    protected $presenterRegistry;

    /**
     * @param PresenterRegistryInterface $presenterRegistry
     */
    public function __construct(PresenterRegistryInterface $presenterRegistry)
    {
        $this->presenterRegistry = $presenterRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($ruleDefinition, $format = null, array $context = [])
    {
        return [
            'id'       => $ruleDefinition->getId(),
            'code'     => $ruleDefinition->getCode(),
            'type'     => $ruleDefinition->getType(),
            'priority' => $ruleDefinition->getPriority(),
            'content'  => $this->localizeContent($ruleDefinition->getContent(), $context)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof RuleDefinitionInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Localize content of Rule Definition
     *
     * @param array $content
     * @param array $context
     *
     * @return array
     */
    protected function localizeContent(array $content, array $context)
    {
        foreach ($content as $key => $items) {
            foreach ($items as $index => $action) {
                if (isset($action['field']) && isset($action['value'])) {
                    $presenter = $this->presenterRegistry->getPresenterByAttributeCode($action['field']);

                    if (null !== $presenter) {
                        $content[$key][$index]['value'] = $presenter->present($action['value'], $context);
                    }
                }
            }
        }

        return $content;
    }
}
