<?php

namespace Context;

use Behat\CommonContexts\WebApiContext as BehatWebApiContext;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;

/**
 * Provides custom web API methods
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebApiContext extends BehatWebApiContext
{
    /** Default Nonce */
    const NONCE = 'd36e316282959a9ed4c89851497a717f';

    protected $url;

    /**
     * {@inheritdoc}
     */
    public function __construct($baseUrl, Browser $browser = null)
    {
        parent::__construct($baseUrl, $browser);
        $this->url = rtrim($baseUrl, '/');
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
        $this->generateWsseHeader($username, $apiKey);
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

        return array(new Step\Given("I send a GET request to \"api/rest/ecommerce/products/{identifier}.json\""));
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
     * Adds WSSE authentication header to next request.
     *
     * @param string $username
     * @param string $apiKey
     * @param string $nonce
     */
    private function generateWsseHeader($username, $apiKey, $nonce = self::NONCE)
    {
        $created = date('c');
        $digest  = base64_encode(sha1(base64_decode($nonce) . $created . $apiKey, true));
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
    private function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }
}
