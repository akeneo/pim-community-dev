<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilder;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\Documentation;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\DocumentationCollection;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\HrefMessageParameter;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\Documentation\RouteMessageParameter;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\DocumentationBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\AttributeOptionsExist;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class AttributeOptionDoesNotExist implements DocumentationBuilderInterface
{
    public function support($object): bool
    {
        if ($object instanceof ConstraintViolationInterface) {
            switch ($object->getCode()) {
                case AttributeOptionsExist::ATTRIBUTE_OPTION_DOES_NOT_EXIST:
                case AttributeOptionsExist::ATTRIBUTE_OPTIONS_DO_NOT_EXIST:
                    return true;
            }
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

        if (!isset($parameters['%attribute_code%'])) {
            throw new \InvalidArgumentException(sprintf(
                'Parameter "%s" is missing from "%s" class.',
                '%attribute_code%',
                get_class($constraintViolation)
            ));
        }

        return new DocumentationCollection([
            new Documentation(
                'More information about select attributes: {manage_attributes_options}.',
                [
                    'manage_attributes_options' => new HrefMessageParameter(
                        'Manage select attributes options',
                        'https://help.akeneo.com/pim/serenity/articles/manage-your-attributes.html#manage-simple-and-multi-selects-attribute-options'
                    )
                ],
                Documentation::TYPE_INFORMATION
            ),
            new Documentation(
                'Please check the {attribute_options_settings}.',
                [
                    'attribute_options_settings' => new RouteMessageParameter(
                        sprintf('Options settings of the %s attribute', $parameters['%attribute_code%']),
                        'pim_enrich_attribute_edit',
                        ['code' => $parameters['%attribute_code%']]
                    )
                ],
                Documentation::TYPE_TEXT
            )
        ]);
    }
}
