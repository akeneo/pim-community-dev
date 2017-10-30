<?php

namespace Context;

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Buzz\Browser;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

require_once 'vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';

/**
 * Provides custom web API methods
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebApiContext extends BehatContext
{
    /** @var array */
    private $placeHolders = [];

    /** @var Browser */
    private $browser;

    /** @var string */
    private $baseUrl;

    /** @var array */
    private $headers = [];

    /** @var string */
    protected $url;

    /**
     * @param string  $baseUrl
     * @param Browser $browser
     */
    public function __construct($baseUrl, Browser $browser = null)
    {
        $this->url = rtrim($baseUrl, '/');

        $this->baseUrl = rtrim($baseUrl, '/');

        if (null === $browser) {
            $this->browser = new Browser();
        } else {
            $this->browser = $browser;
        }
    }

    /**
     * Provides WSSE authentication for next request.
     *
     * @param string $username
     * @param string $apiKey
     *
     * @Given /^I am authenticating as "([^"]*)" with "([^"]*)" api key$/
     */
    public function iAmAuthenticatingWithApiKey($username, $apiKey)
    {
        $user = $this->getFixturesContext()->getUser($username);
        $salt = $user->getSalt();
        $this->generateWsseHeader($username, $apiKey, $salt);
    }

    /**
     * @param string $sku
     *
     * @return array:Step
     * @Given /^I request information for product "([^"]*)"$/
     */
    public function iRequestInformationForProduct($sku)
    {
        $product = $this->getFixturesContext()->getProduct($sku);
        $this->setPlaceHolder('{identifier}', $product->getIdentifier());
        $this->setPlaceHolder('{baseUrl}', $this->url);

        return array(new Step\Given("I send a GET request to \"api/rest/products/{identifier}.json\""));
    }

    /**
     * @Given /^(?:the )?response should be valid json$/
     */
    public function theResponseShouldBeValidJson()
    {
        json_decode($this->getBrowser()->getLastResponse()->getContent());
        assertEquals(json_last_error(), JSON_ERROR_NONE);
    }

    /**
     * @param TableNode $table
     *
     * @Given /^(?:the )?response should contain the following data:$/
     */
    public function theResponseShouldContainTheFollowingData(TableNode $table)
    {
        $response = json_decode($this->getBrowser()->getLastResponse()->getContent(), true);

        foreach ($table->getHash() as $data) {
            assertArrayHasKey($data['key'], $response);
            assertEquals($response[$data['key']], $data['value']);
        }
    }

    /**
     * Checks that response has specific status code.
     *
     * @param string $code status code
     *
     * @Then /^(?:the )?response code should be (\d+)$/
     */
    public function theResponseCodeShouldBe($code)
    {
        \PHPUnit_Framework_Assert::assertSame(intval($code), $this->browser->getLastResponse()->getStatusCode());
    }

    /**
     * Sends HTTP request to specific relative URL.
     *
     * @param string $method request method
     * @param string $url    relative url
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)"$/
     */
    public function iSendARequest($method, $url)
    {
        $url = $this->baseUrl.'/'.ltrim($this->replacePlaceHolder($url), '/');

        $this->browser->call($url, $method, $this->getHeaders());
    }

    /**
     * Checks that response body contains JSON from PyString.
     *
     * @param PyStringNode $jsonString
     *
     * @Then /^(?:the )?response should contain json:$/
     */
    public function theResponseShouldContainJson(PyStringNode $jsonString)
    {
        $etalon = json_decode($this->replacePlaceHolder($jsonString->getRaw()), true);
        $actual = json_decode($this->browser->getLastResponse()->getContent(), true);

        if (null === $etalon) {
            throw new \RuntimeException(
                "Can not convert etalon to json:\n".$this->replacePlaceHolder($jsonString->getRaw())
            );
        }

        assertCount(count($etalon), $actual);
        foreach ($actual as $key => $needle) {
            assertArrayHasKey($key, $etalon);
            assertEquals($etalon[$key], $actual[$key]);
        }
    }

    /**
     * Sets place holder for replacement.
     *
     * you can specify placeholders, which will
     * be replaced in URL, request or response body.
     *
     * @param string $key   token name
     * @param string $value replace value
     */
    public function setPlaceHolder($key, $value)
    {
        $this->placeHolders[$key] = $value;
    }

    /**
     * Replaces placeholders in provided text.
     *
     * @param string $string
     *
     * @return string
     */
    public function replacePlaceHolder($string)
    {
        foreach ($this->placeHolders as $key => $val) {
            $string = str_replace($key, $val, $string);
        }
        return $string;
    }

    /**
     * Returns browser instance.
     *
     * @return Browser
     */
    public function getBrowser()
    {
        return $this->browser;
    }

    /**
     * Adds WSSE authentication header to next request.
     *
     * @param string $username
     * @param string $apiKey
     * @param string $salt
     */
    protected function generateWsseHeader($username, $apiKey, $salt)
    {
        $nonce   = uniqid();
        $created = date('c');
        $raw     = sprintf('%s%s%s', base64_decode($nonce), $created, $apiKey);
        $encoder = new MessageDigestPasswordEncoder('sha1', true, 1);
        $digest  = $encoder->encodePassword($raw, $salt);
        $this->addHeader('CONTENT_TYPE: application/json');
        $this->addHeader('Authorization: WSSE profile="UsernameToken"');
        $this->addHeader(
            sprintf(
                'X-WSSE: UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
                $username,
                $digest,
                $nonce,
                $created
            )
        );
    }

    /**
     * Return fixtures context
     *
     * @return FixturesContext
     */
    protected function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }

    /**
     * Returns headers, that will be used to send requests.
     *
     * @return array
     */
    protected function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Adds header
     *
     * @param string $header
     */
    protected function addHeader($header)
    {
        $this->headers[] = $header;
    }
}
