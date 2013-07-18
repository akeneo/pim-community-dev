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
        $this->getFixturesContext()->getOrCreateUser($username, null, $apiKey);

        $this->generateWsseHeader($username, $apiKey);
    }

    /**
     * @Given /^I request information for product "([^"]*)"$/
     */
    public function iRequestInformationForProduct($sku)
    {
        $product = $this->getFixturesContext()->getProduct($sku);
        $this->setPlaceHolder('{identifier}', $product->getIdentifier());

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
    private function generateWsseHeader($userName, $apiKey, $nonce = self::NONCE)
    {
        $created = date('c');
        $digest  = base64_encode(sha1(base64_decode($nonce) . $created . $apiKey, true));
        $this->addHeader('CONTENT_TYPE: application/json');
        $this->addHeader('Authorization: WSSE profile="UsernameToken"');
        $this->addHeader(sprintf('X-WSSE: UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
            $userName,
            $digest,
            $nonce,
            $created
        ));
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
