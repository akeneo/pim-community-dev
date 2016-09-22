<?php

namespace Context;

use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

/**
 * Context to be able to test the standard format.
 *
 * The standard format is rendered via the Symfony VarDumper component to be able to
 * have a simple and nice Gherkin input.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StandardFormatContext extends BehatContext
{
    const DATE_FIELD_COMPARISON = 'this is a date formatted to ISO-8601';
    const MEDIA_ATTRIBUTE_DATA_COMPARISON = 'this is a media identifier';

    const DATE_FIELD_PATTERN = '#[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}\+[0-9]{2}:[0-9]{2}#';
    const MEDIA_ATTRIBUTE_DATA_PATTERN = '#[0-9a-z]/[0-9a-z]/[0-9a-z]/[0-9a-z]/[0-9a-z]{40}_\w+\.[a-zA-Z]+#';

    /** @var array */
    private $results;

    /**
     * @Given /^I normalize the product "([^"]*)" with the standard format$/
     */
    public function iNormalizeTheProductWithTheStandardFormat($identifier)
    {
        $repository = $this->getContainer()->get('pim_catalog.repository.product');
        $product = $repository->findOneByIdentifier($identifier);

        $serializer = $this->getContainer()->get('pim_serializer');
        $this->results[$identifier] = $serializer->normalize($product, 'standard');
    }

    /**
     * Please note that date fields (created/updated) and medias attributes values are replaced respectively by
     * self::DATE_FIELD_COMPARISON and self::MEDIA_ATTRIBUTE_DATA_COMPARISON.
     *
     * The pattern of those properties is verified before being replaced.
     *
     * This is because those properties are generated on the fly during the product creation, and thus
     * can not be the same that what is written in the Gherkin.
     *
     * @Then /^the standard format result of the product "([^"]*)" should be:$/
     */
    public function theStandardFormatResultOfTheProductShouldBe($identifier, PyStringNode $expected)
    {
        if (!isset($this->results[$identifier])) {
            throw new \OutOfBoundsException(
                sprintf('"%s" has not been normalized to the standard format yet', $identifier)
            );
        }

        $resultSanitized = $this->results[$identifier];
        $expectedSanitized = $expected;

        $this->sanitizeDateFields($expectedSanitized, $resultSanitized);
        $this->sanitizeMediaAttributeData($expectedSanitized, $resultSanitized);

        $this->assertDumpEquals(
            $expectedSanitized->getRaw(),
            $resultSanitized,
            sprintf('The standard format results of the product "%s" is not valid.', $identifier)
        );
    }

    /**
     * Replaces dates fields (created/updated) in the $expected string by self::DATE_FIELD_COMPARISON.
     *
     * Replaces also date fields (created/updated) in the the result of the standard normalization
     * only if those properties do respect the standard pattern self::MEDIA_ATTRIBUTE_DATA_PATTERN.
     *
     * @param PyStringNode $expected
     * @param array        $result
     */
    private function sanitizeDateFields(PyStringNode $expected, array &$result)
    {
        if ($this->assertDateFieldPattern($result['created']) &&
            $this->assertDateFieldPattern($result['updated'])
        ) {
            $result['created'] = self::DATE_FIELD_COMPARISON;
            $result['updated'] = self::DATE_FIELD_COMPARISON;
        }

        $sanitizedLines = $expected->getLines();
        foreach ($sanitizedLines as $index => $line) {
            if (1 === preg_match('/.*created|updated.*/', $line)) {
                $sanitizedLines[$index] = preg_replace(self::DATE_FIELD_PATTERN, self::DATE_FIELD_COMPARISON, $line);
            }
        }

        $expected->setLines($sanitizedLines);
    }

    /**
     * Replaces media attributes data in the $expected string by self::MEDIA_ATTRIBUTE_DATA_COMPARISON.
     *
     * Replaces also media attributes data in the the result of the standard normalization
     * only if those properties do respect the standard pattern self::DATE_FIELD_PATTERN.
     *
     * @param PyStringNode $expected
     * @param array        $result
     */
    private function sanitizeMediaAttributeData(PyStringNode $expected, array &$result)
    {
        foreach ($result['values'] as $attributeCode => $values) {
            if (1 === preg_match('/.*(file|image).*/', $attributeCode)) {
                foreach ($values as $index => $value) {
                    if ($this->assertMediaAttributeDataPattern($value['data'])) {
                        $result['values'][$attributeCode][$index]['data'] = self::MEDIA_ATTRIBUTE_DATA_COMPARISON;
                    }
                }
            }
        }

        $expectedSanitized = preg_replace(
            self::MEDIA_ATTRIBUTE_DATA_PATTERN,
            self::MEDIA_ATTRIBUTE_DATA_COMPARISON,
            $expected->getRaw()
        );
        $expected->setLines(explode("\n", $expectedSanitized));
    }

    /**
     * @param string $field
     *
     * @return bool
     */
    private function assertDateFieldPattern($field)
    {
        return 1 === preg_match(self::DATE_FIELD_PATTERN, $field);
    }

    /**
     * @param string $data
     *
     * @return bool
     */
    private function assertMediaAttributeDataPattern($data)
    {
        return 1 === preg_match(self::MEDIA_ATTRIBUTE_DATA_PATTERN, $data);
    }

    /**
     * @return ContainerInterface
     */
    private function getContainer()
    {
        return $this->getMainContext()->getContainer();
    }

    /**
     * Copy/paste from Symfony
     * See {@link Symfony\Component\VarDumper\Test\VarDumperTestTrait}
     *
     * We can't use this trait directly here as it is provided for classes that extend \PHPUnit_Framework_TestCase
     *
     * @param string $dump
     * @param string $data
     * @param string $message
     */
    private function assertDumpEquals($dump, $data, $message)
    {
        \PHPUnit_Framework_TestCase::assertSame($dump, $this->getVarDumperDump($data), $message);
    }

    /**
     * Copy/paste from Symfony
     * See {@link Symfony\Component\VarDumper\Test\VarDumperTestTrait}
     *
     * We can't use this trait directly here as it is provided for classes that extend \PHPUnit_Framework_TestCase
     *
     * @param string $data
     *
     * @return string
     */
    private function getVarDumperDump($data)
    {
        $h = fopen('php://memory', 'r+b');
        $cloner = new VarCloner();
        $dumper = new CliDumper($h);
        $dumper->setColors(false);
        $dumper->dump($cloner->cloneVar($data)->withRefHandles(false));
        $data = stream_get_contents($h, -1, 0);
        fclose($h);

        return rtrim($data);
    }
}
