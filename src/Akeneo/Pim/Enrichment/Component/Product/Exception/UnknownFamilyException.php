<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\Documented\Documentation;
use Akeneo\Pim\Enrichment\Component\Error\Documented\DocumentationCollection;
use Akeneo\Pim\Enrichment\Component\Error\Documented\DocumentedErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\Documented\HrefMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\Documented\RouteMessageParameter;
use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UnknownFamilyException extends InvalidPropertyException implements
    DomainErrorInterface,
    TemplatedErrorMessageInterface,
    DocumentedErrorInterface
{
    /** @var TemplatedErrorMessage */
    private $templatedErrorMessage;

    public function __construct(string $propertyName, string $propertyValue, string $className)
    {
        $this->templatedErrorMessage = new TemplatedErrorMessage(
            'The {family_code} family does not exist in your PIM.',
            ['family_code' => $propertyValue]
        );

        parent::__construct(
            $propertyName,
            $propertyValue,
            $className,
            (string) $this->templatedErrorMessage,
            self::VALID_ENTITY_CODE_EXPECTED_CODE
        );
    }

    public function getTemplatedErrorMessage(): TemplatedErrorMessage
    {
        return $this->templatedErrorMessage;
    }

    public function getDocumentation(): DocumentationCollection
    {
        return new DocumentationCollection([
            new Documentation(
                'Please check your {family_settings}.',
                [
                    'family_settings' => new RouteMessageParameter(
                        'Family settings',
                        'pim_enrich_family_index'
                    )
                ]
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
                ]
            )
        ]);
    }
}
