<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\RouteMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\ScopableValues;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UnknownChannel implements DocumentationBuilderInterface
{
    public function support($object): bool
    {
        if (
            $object instanceof ConstraintViolationInterface
            && $object->getCode() === ScopableValues::SCOPABLE_VALUES
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param ConstraintViolationInterface $constraintViolation
     */
    public function buildDocumentation($constraintViolation): DocumentationCollection
    {
        if (false === $this->support($constraintViolation)) {
            throw new \InvalidArgumentException('Parameter $constraintViolation is not supported.');
        }

        return new DocumentationCollection([
            new Documentation(
                'Please check your {channels_settings}.',
                [
                    'channels_settings' => new RouteMessageParameter(
                        'Channel settings',
                        'pim_enrich_channel_index'
                    )
                ],
                Documentation::STYLE_TEXT
            ),
            new Documentation(
                'More information about channels: {manage_channel}',
                [
                    'manage_channel' => new HrefMessageParameter(
                        'Manage your channels',
                        'https://help.akeneo.com/pim/serenity/articles/manage-your-channels.html'
                    ),
                ],
                Documentation::STYLE_INFORMATION
            ),
        ]);
    }
}
