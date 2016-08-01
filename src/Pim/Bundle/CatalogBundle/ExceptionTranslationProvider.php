<?php

namespace Pim\Bundle\CatalogBundle;

use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ExceptionTranslationProvider
{
    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Get the string translated message for the given $exception.
     *
     * @param InvalidArgumentException $exception
     *
     * @return string
     */
    public function getTranslation(InvalidArgumentException $exception)
    {
        $translationKey = $this->getTranslationKeyFromException($exception);

        return $this->translator->trans($translationKey);
    }

    /**
     * Return the correct translation key from the given $exception
     * by looking to its code.
     *
     * @param InvalidArgumentException $exception
     *
     * @return string
     */
    protected function getTranslationKeyFromException(InvalidArgumentException $exception)
    {
        $translationKeys = [
            InvalidArgumentException::EXPECTED_CODE                    => 'expected',
            InvalidArgumentException::BOOLEAN_EXPECTED_CODE            => 'boolean_expected',
            InvalidArgumentException::FLOAT_EXPECTED_CODE              => 'float_expected',
            InvalidArgumentException::INTEGER_EXPECTED_CODE            => 'integer_expected',
            InvalidArgumentException::NUMERIC_EXPECTED_CODE            => 'numeric_expected',
            InvalidArgumentException::STRING_EXPECTED_CODE             => 'string_expected',
            InvalidArgumentException::ARRAY_EXPECTED_CODE              => 'array_expected',
            InvalidArgumentException::ARRAY_OF_ARRAYS_EXPECTED_CODE    => 'array_of_arrays_expected',
            InvalidArgumentException::ARRAY_KEY_EXPECTED_CODE          => 'array_key_expected',
            InvalidArgumentException::ARRAY_INVALID_KEY_CODE           => 'array_invalid_key',
            InvalidArgumentException::ARRAY_NUMERIC_KEY_EXPECTED_CODE  => 'array_numeric_key_expected',
            InvalidArgumentException::ARRAY_STRING_KEY_EXPECTED_CODE   => 'array_string_key_expected',
            InvalidArgumentException::ARRAY_STRING_VALUE_EXPECTED_CODE => 'array_string_value_expected',
            InvalidArgumentException::EMPTY_ARRAY_CODE                 => 'empty_array',
            InvalidArgumentException::VALID_ENTITY_CODE_EXPECTED_CODE  => 'valid_entity_code_expected',
            InvalidArgumentException::LOCALE_AND_SCOPE_EXPECTED_CODE   => 'locale_and_scope_expected',
            InvalidArgumentException::SCOPE_EXPECTED_CODE              => 'scope_expected',
            InvalidArgumentException::ASSOCIATION_FORMAT_EXPECTED_CODE => 'association_format_expected',
        ];

        if (isset($translationKeys[$exception->getCode()])) {
            return sprintf('pim_catalog.constraint.%s', $translationKeys[$exception->getCode()]);
        }

        return '';
    }
}
