<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilder;

use Akeneo\Pim\Enrichment\Component\Error\Documentation\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\Documentation\RouteMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidAssociationProductIdentifierException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class InvalidAssociationProductIdentifier implements DocumentationBuilderInterface
{
    public function support($object): bool
    {
        return $object instanceof InvalidAssociationProductIdentifierException;
    }

    public function buildDocumentation($object): DocumentationCollection
    {
        if (false === $this->support($object)) {
            throw new \InvalidArgumentException('Parameter $object is not supported.');
        }

        return new DocumentationCollection([
            new Documentation(
                'Please check if the product exists in your PIM or check {permissions_settings}.',
                [
                    'permissions_settings' => new RouteMessageParameter(
                        'your connection group permissions settings',
                        'pim_user_group_index'
                    )
                ],
                Documentation::STYLE_TEXT
            ),
            new Documentation(
                'More information about connection permissions: {manage_your_connections}.',
                [
                    'manage_your_connections' => new HrefMessageParameter(
                        'Manage your connections',
                        'https://help.akeneo.com/pim/serenity/articles/manage-your-connections.html#configure-the-connection-user-group'
                    )
                ],
                Documentation::STYLE_INFORMATION
            )
        ]);
    }
}
