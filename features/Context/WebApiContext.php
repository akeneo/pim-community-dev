<?php

namespace Context;

use Behat\CommonContexts\WebApiContext as BehatWebApiContext;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

/**
 * Provides custom web API methods
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebApiContext extends BehatWebApiContext
{
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
     * @param string $salt
     */
    private function generateWsseHeader($username, $apiKey, $salt)
    {
        $nonce   = uniqid();
        $created = date('c');
        $raw = sprintf('%s%s%s', base64_decode($nonce), $created, $apiKey);
        $encoder = new MessageDigestPasswordEncoder('sha1', true, 1);
        $digest = $encoder->encodePassword($raw, $salt);
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
