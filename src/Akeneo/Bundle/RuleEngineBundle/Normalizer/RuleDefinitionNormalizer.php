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
use Pim\Component\Localization\Localizer\LocalizedAttributeConverterInterface;
use Pim\Component\Localization\Provider\Format\NumberFormatProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Rule definition normalizer for internal api
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class RuleDefinitionNormalizer implements NormalizerInterface
{
    /** @var LocalizedAttributeConverterInterface */
    protected $converter;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var NumberFormatProvider */
    protected $formatProvider;

    /**
     * @param LocalizedAttributeConverterInterface $converter
     * @param TokenStorageInterface                $tokenStorage
     * @param NumberFormatProvider                 $formatProvider
     */
    public function __construct(
        LocalizedAttributeConverterInterface $converter,
        TokenStorageInterface $tokenStorage,
        NumberFormatProvider $formatProvider
    ) {
        $this->converter      = $converter;
        $this->tokenStorage   = $tokenStorage;
        $this->formatProvider = $formatProvider;
    }

    /** @var string[] */
    protected $supportedFormats = ['array'];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'id'       => $object->getId(),
            'code'     => $object->getCode(),
            'type'     => $object->getType(),
            'priority' => $object->getPriority(),
            'content'  => $this->convertContent($object->getContent()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof RuleDefinitionInterface &&
            in_array($format, $this->supportedFormats);
    }

    /**
     * Convert a RuleDefinition content
     *
     * @param  mixed $content
     * @return mixed
     */
    protected function convertContent($content)
    {
        $locale = $this->getUiLocale();
        $options = $this->formatProvider->getFormat($locale);

        foreach ($content['actions'] as $i => $action) {
            $localizedAction = $this->converter->localizeValue($action['field'], $action['value'], $options);
            $content['actions'][$i]['value'] = $localizedAction;
        }

        foreach($content['conditions'] as $i => $condition) {
            $localizedAction = $this->converter->localizeValue($condition['field'], $condition['value'], $options);
            $content['conditions'][$i]['value'] = $localizedAction;
        }

        return $content;
    }

    protected function getUiLocale()
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }

        $user = $token->getUser();
        if (null === $user) {
            return null;
        }

        return $user->getUiLocale();
    }
}
