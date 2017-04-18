<?php

namespace Pim\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Context for the attribute validation
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeValidationContext extends PimContext
{
    /**
     * @param TableNode $table
     *
     * @throws \Exception
     *
     * @Then /^there should be the following attributes:$/
     */
    public function thereShouldBeTheFollowingAttributes(TableNode $table)
    {
        $this->getService('doctrine.orm.entity_manager')->clear();
        foreach ($table->getHash() as $data) {
            $attribute = $this->getFixturesContext()->getAttribute($data['code']);
            $this->getFixturesContext()->refresh($attribute);

            foreach ($data as $method => $value) {
                $matches = null;
                switch ($method) {
                    case 'code':
                        // Untestable method
                        break;
                    case (preg_match('/^label-(?<locale>.*)$/', $method, $matches) ? true : false):
                        assertEquals($value, $attribute->getTranslation($matches['locale'])->getLabel());
                        break;
                    case 'type':
                        assertEquals($value, $attribute->getType());
                        break;
                    case 'localizable':
                        assertEquals('1' === $value, $attribute->isLocalizable());
                        break;
                    case 'scopable':
                        assertEquals('1' === $value, $attribute->isScopable());
                        break;
                    case 'wysiwyg_enabled':
                        assertEquals('1' === $value, $attribute->isWysiwygEnabled());
                        break;
                    case 'decimals_allowed':
                        assertEquals('1' === $value, $attribute->isDecimalsAllowed());
                        break;
                    case 'negative_allowed':
                        assertEquals('1' === $value, $attribute->isNegativeAllowed());
                        break;
                    case 'useable_as_grid_filter':
                        assertEquals('1' === $value, $attribute->isUseableAsGridFilter());
                        break;
                    case 'unique':
                        assertEquals('1' === $value, $attribute->isUnique());
                        break;
                    case 'group':
                        assertEquals($value, $attribute->getGroup()->getCode());
                        break;
                    case 'allowed_extensions':
                        if ('' === $value) {
                            assertEmpty($attribute->getAllowedExtensions());
                        } else {
                            assertEquals(explode(',', $value), $attribute->getAllowedExtensions());
                        }
                        break;
                    case 'available_locales':
                        assertEquals(explode(',', $value), $attribute->getAvailableLocales()->toArray());
                        break;
                    case 'reference_data_name':
                        if ('' === $value) {
                            assertNull($attribute->getReferenceDataName());
                        } else {
                            assertEquals($value, $attribute->getReferenceDataName());
                        }
                        break;
                    case 'number_min':
                        if ('' === $value) {
                            assertNull($attribute->getNumberMin());
                        } else {
                            assertEquals($value, $attribute->getNumberMin());
                        }
                        break;
                    case 'number_max':
                        if ('' === $value) {
                            assertNull($attribute->getNumberMax());
                        } else {
                            assertEquals($value, $attribute->getNumberMax());
                        }
                        break;
                    case 'metric_family':
                        assertEquals($value, $attribute->getMetricFamily());
                        break;
                    case 'default_metric_unit':
                        assertEquals($value, $attribute->getDefaultMetricUnit());
                        break;
                    case 'sort_order':
                        assertEquals($value, $attribute->getSortOrder());
                        break;
                    case 'max_characters':
                        assertEquals($value, $attribute->getMaxCharacters());
                        break;
                    case 'validation_rule':
                        assertEquals($value, $attribute->getValidationRule());
                        break;
                    case 'validation_regexp':
                        assertEquals($value, $attribute->getValidationRegexp());
                        break;
                    case 'max_file_size':
                        assertEquals($value, $attribute->getMaxFileSize());
                        break;
                    case 'date_min':
                        $date = $attribute->getDateMin();
                        if (null !== $date) {
                            assertEquals($value, $date->format('Y-m-d'));
                        }
                        break;
                    case 'date_max':
                        $date = $attribute->getDateMax();
                        if (null !== $date) {
                            assertEquals($value, $date->format('Y-m-d'));
                        }
                        break;
                    case 'is_read_only':
                        assertEquals(($data['is_read_only'] == 1), $attribute->getProperty('is_read_only'));
                        break;
                    default:
                        throw new \Exception(sprintf(
                            "The attribute method '%s' is not testable, please add it in %s",
                            $method,
                            get_class($this)
                        ));
                }
            }
        }
    }
}
