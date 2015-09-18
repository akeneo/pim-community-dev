<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Validator\Constraints;

use Akeneo\Component\FileTransformer\Exception\InvalidOptionsTransformationException;
use Akeneo\Component\FileTransformer\Exception\NonRegisteredTransformationException;
use Akeneo\Component\FileTransformer\Transformation\TransformationRegistry;
use PimEnterprise\Component\ProductAsset\Model\ChannelVariationsConfigurationInterface;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for channel configuration
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ChannelConfigurationValidator extends ConstraintValidator
{
    /** @var TransformationRegistry */
    protected $registry;

    /** @var string */
    protected $mimeType;

    /**
     * @param TransformationRegistry $registry
     */
    public function __construct(TransformationRegistry $registry)
    {
        $this->registry = $registry;
        $this->mimeType = 'image/jpeg';
    }

    /**
     * @param object     $channelConfiguration
     * @param Constraint $constraint
     */
    public function validate($channelConfiguration, Constraint $constraint)
    {
        if ($channelConfiguration instanceof ChannelVariationsConfigurationInterface) {
            $configuration = $channelConfiguration->getConfiguration();
            foreach ($configuration as $transformationCode => $options) {
                $transformation = null;
                try {
                    $transformation = $this->registry->get($transformationCode, $this->mimeType);
                } catch (NonRegisteredTransformationException $e) {
                    $this->addUnknownTransformationViolation($constraint, $transformationCode);
                }
                if (null !== $transformation) {
                    $resolver = $transformation->getOptionsResolver();
                    try {
                        $resolver->resolve($options);
                    } catch (InvalidOptionsTransformationException $exception) {
                        $errorMessage = $exception->getMessage();
                        $this->addInvalidConfigurationViolation($constraint, $transformationCode, $errorMessage);
                    }
                }
            }
        }
    }

    /**
     * @param ChannelConfiguration $constraint
     * @param string               $transformationCode
     */
    protected function addUnknownTransformationViolation(
        ChannelConfiguration $constraint,
        $transformationCode
    ) {
        $this->context->buildViolation(
            $constraint->unknownTransformation,
            [
                '%transformation%' => $transformationCode
            ]
        )->addViolation();
    }

    /**
     * @param ChannelConfiguration $constraint
     * @param string               $transformationCode
     * @param string               $errorMessage
     */
    protected function addInvalidConfigurationViolation(
        ChannelConfiguration $constraint,
        $transformationCode,
        $errorMessage
    ) {
        $this->context->buildViolation(
            $constraint->invalidConfiguration,
            [
                '%transformation%' => $transformationCode,
                '%error%'          => $errorMessage
            ]
        )->addViolation();
    }
}
