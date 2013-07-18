Feature: Expose product data via a REST API
  In order to provide access to product data to an external application
  As a developer
  I need to expose product data via a REST API

  Scenario: Fail to authenticate an anonymous user
    Given I send a GET request to "api/rest/ecommerce/products.json"
    Then the response code should be 401

  Scenario: Successfully authenticate a user
    Given I am authenticating as "admin" with "admin_api_key" api key
    And I send a GET request to "api/rest/ecommerce/products.json"
    Then the response code should be 200

  Scenario: Successfully retrieve a product
    Given a "Car" product available in english
    And the following product attributes:
      | type   | label | scopable | translatable |
      | prices | Price | yes      | no           |
      | text   | Color | no       | yes          |
    And the following product values:
      | product | attribute | locale | scope     | value                |
      | Car     | Price     |        | web       | 10000 EUR, 15000 USD |
      | Car     | Price     |        | ecommerce | 10500 EUR, 16000 USD |
      | Car     | Color     | en_US  |           | red                  |
      | Car     | Color     | fr_FR  |           | rouge                |
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
          "en_US":"10500 EUR, 16000 USD",
          "fr_FR":"10500 EUR, 16000 USD"
        },
        "color":{
          "en_US":"red",
          "fr_FR":"rouge"
        },
        "resource":"http://akeneo-pim.local/app_behat.php/api/rest/ecommerce/products/Car"
      }
    }
    """
