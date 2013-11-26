Feature: Expose product data via a REST API
  In order to provide access to product data to an external application
  As a developer
  I need to expose product data via a REST API

  Background:
      Given the "default" catalog configuration

  Scenario: Fail to authenticate an anonymous user
    Given I send a GET request to "api/rest/ecommerce/products.json"
    Then the response code should be 401

  Scenario: Successfully authenticate a user
    Given I am authenticating as "admin" with "admin_api_key" api key
    And I send a GET request to "api/rest/ecommerce/products.json"
    Then the response code should be 200

  Scenario: Successfully retrieve a product
    Given a "Car" product
    And the following attributes:
      | type   | label             | scopable | translatable |
      | prices | Price             | no       | no           |
      | text   | Color             | no       | yes          |
      | text   | Short description | yes      | yes          |
    And the following product values:
      | product | attribute         | locale | scope     | value                  |
      | Car     | Price             |        |           | 10000 EUR, 15000 USD   |
      | Car     | Color             | en_US  |           | red                    |
      | Car     | Color             | fr_FR  |           | rouge                  |
      | Car     | Short description | en_US  | mobile    | A nice car             |
      | Car     | Short description | fr_FR  | mobile    | Une belle voiture      |
      | Car     | Short description | en_US  | ecommerce | A very nice car        |
      | Car     | Short description | fr_FR  | ecommerce | Une très belle voiture |
    And I am authenticating as "admin" with "admin_api_key" api key
    And I request information for product "Car"
    Then the response code should be 200
    And the response should be valid json
    And the response should contain json:
    """
    {
      "Car":{
        "sku":{
          "en_US":"Car",
          "fr_FR":"Car"
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
          "en_US":"A very nice car",
          "fr_FR":"Une très belle voiture"
        },
        "resource":"{baseUrl}/api/rest/ecommerce/products/Car"
      }
    }
    """
