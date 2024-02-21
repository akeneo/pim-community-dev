<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\RouteMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownFamilyException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class UnknownFamily implements DocumentationBuilderInterface
{
    public function support($object): bool
    {
        if ($object instanceof UnknownFamilyException) {
            return true;
        }

        return false;
    }

    public function buildDocumentation($object): DocumentationCollection
    {
        if (false === $this->support($object)) {
            throw new \InvalidArgumentException('Parameter $object is not supported.');
        }

        return new DocumentationCollection([
            new Documentation(
                'Please check your {family_settings}.',
                [
                    'family_settings' => new RouteMessageParameter(
                        'Family settings',
                        'pim_enrich_family_index'
                    )
                ],
                Documentation::STYLE_TEXT
            ),
            new Documentation(
                'More information about families: {what_is_a_family} {manage_your_families}.',
                [
                    'what_is_a_family' => new HrefMessageParameter(
                        'What is a family?',
                        'https://help.akeneo.com/pim/serenity/articles/what-is-a-family.html'
                    ),
                    'manage_your_families' => new HrefMessageParameter(
                        'Manage your families',
                        'https://help.akeneo.com/pim/serenity/articles/manage-your-families.html'
                    )
                ],
                Documentation::STYLE_INFORMATION
            )
        ]);
    }
}
