<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilder;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\Documentation;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\DocumentationCollection;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\HrefMessageParameter;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\RouteMessageParameter;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\OnlyExpectedAttributes;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AttributeDoesNotBelongToFamily implements DocumentationBuilderInterface
{
    public function support($object): bool
    {
        if (
            $object instanceof ConstraintViolationInterface
            && $object->getCode() === OnlyExpectedAttributes::ATTRIBUTE_DOES_NOT_BELONG_TO_FAMILY
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

        $parameters = $constraintViolation->getParameters();

        if (!isset($parameters['%family%'])) {
            throw new \InvalidArgumentException(sprintf(
                'Parameter "%s" is missing from "%s" class.',
                '%family%',
                get_class($constraintViolation)
            ));
        }

        return new DocumentationCollection([
            new Documentation(
                'More information about family attributes settings: {manage_family_attributes}.',
                [
                    'manage_family_attributes' => new HrefMessageParameter(
                        'Manage attributes in a family',
                        'https://help.akeneo.com/pim/serenity/articles/manage-your-families.html#manage-attributes-in-a-family'
                    )
                ],
                Documentation::TYPE_INFORMATION
            ),
            new Documentation(
                sprintf('Please check the {family_settings} of the %s family.', $parameters['%family%']),
                [
                    'family_settings' => new RouteMessageParameter(
                        '"Attributes" settings',
                        'pim_enrich_family_edit',
                        ['code' => $parameters['%family%']]
                    )
                ],
                Documentation::TYPE_TEXT
            )
        ]);
    }
}
