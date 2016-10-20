<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Context for technical tests
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TechnicalContext extends RawMinkContext
{
    /**
     * @param string $identifiers
     *
     * @Then /^I should be able to normalize and denormalize (?:the )?products? (.*)$/
     */
    public function iShouldBeAbleToNormalizeAndDenormalizeProducts($identifiers)
    {
        $identifiers       = $this->getMainContext()->listToArray($identifiers);
        $serializer        = $this->getContainer()->get('pim_serializer');
        $productValueClass = $this->getContainer()->getParameter('pim_catalog.entity.product_value.class');

        foreach ($identifiers as $identifier) {
            $product = $this->getFixturesContext()->getProduct($identifier);
            $data    = $serializer->normalize($product, 'standard');
            $values  = $data['values'];

            foreach ($values as $attributeCode => $valuesData) {
                $attribute = $this->getFixturesContext()->getAttribute($attributeCode);

                foreach ($valuesData as $valueData) {
                    $createdValue = $serializer->denormalize(
                        $valueData,
                        $productValueClass,
                        'json',
                        ['attribute' => $attribute]
                    );
                    $newData = $serializer->normalize($createdValue, 'standard', ['entity' => 'product']);
                    assertSame(
                        $valueData,
                        $newData,
                        sprintf(
                            "Product's \"%s\" value for attribute %s in locale %s and scope %s is not correct",
                            $identifier,
                            $attributeCode,
                            $valueData['locale'] ?: 'null',
                            $valueData['scope'] ?: 'null'
                        )
                    );
                }
            }
        }
    }

    /**
     * @return FixturesContext
     */
    protected function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getMainContext()->getContainer();
    }
}
