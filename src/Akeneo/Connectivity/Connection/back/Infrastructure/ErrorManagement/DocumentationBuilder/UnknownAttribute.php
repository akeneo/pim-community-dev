<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilder;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\Documentation;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\DocumentationCollection;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\HrefMessageParameter;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\RouteMessageParameter;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownAttributeException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class UnknownAttribute implements DocumentationBuilderInterface
{
    public function support($object): bool
    {
        if ($object instanceof UnknownAttributeException) {
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
                'Please check your {attribute_settings}.',
                [
                    'attribute_settings' => new RouteMessageParameter(
                        'Attributes settings',
                        'pim_enrich_attribute_index'
                    )
                ],
                Documentation::TYPE_TEXT
            ),
            new Documentation(
                'More information about attributes: {what_is_attribute} {manage_attribute}.',
                [
                    'what_is_attribute' => new HrefMessageParameter(
                        'What is an attribute?',
                        'https://help.akeneo.com/pim/serenity/articles/what-is-an-attribute.html'
                    ),
                    'manage_attribute' => new HrefMessageParameter(
                        'Manage your attributes',
                        'https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html'
                    )
                ],
                Documentation::TYPE_INFORMATION
            )
        ]);
    }
}
