Feature: Expose product data via a REST API
  In order to provide access to product data to an external application
  As a developer
  I need to expose product data via a REST API

  Background:
    Given an "apparel" catalog configuration
    And the following product:
      | sku   | family  |
      | shirt | tshirts |

  Scenario: Fail to authenticate an anonymous user
    Given I send a GET request to "api/rest/products/shirt.json"
    Then the response code should be 401

  Scenario: Successfully authenticate a user
    Given I am authenticating as "admin" with "admin_api_key" api key
    And I send a GET request to "api/rest/products/shirt.json"
    Then the response code should be 200

  @skip
  Scenario: Successfully retrieve a product
    Given I am authenticating as "admin" with "admin_api_key" api key
    And I send a GET request to "api/rest/products/shirt.json"
    Then the response code should be 200
    And the response should be valid json
    And the response should contain json:
    """
    {
      "shirt":{
        "sku":{
          "en_US":"shirt",
          "fr_FR":"shirt"
        },
        "price":{
          "en_US":"10000.00 EUR, 15000.00 USD",
          "fr_FR":"10000.00 EUR, 15000.00 USD"
        },
        "color":{
          "en_US":"red",
          "fr_FR":"rouge"
        },
        "shortDescription":{
          "en_US":"A very nice shirt",
          "fr_FR":"Une très belle voiture"
        },
        "resource":"{baseUrl}/api/rest/ecommerce/products/shirt"
      }
    }
    """
