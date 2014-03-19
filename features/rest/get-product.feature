Feature: Expose product data via a REST API
  In order to provide access to product data to an external application
  As a developer
  I need to expose product data via a REST API

  Background:
    Given a "footwear" catalog configuration
    And the following product:
      | sku     | name-en_US | description-en_US-mobile | description-en_US-tablet | price-EUR | price-USD |
      | sandals | My sandals | My great sandals         | My great new sandals     | 20        | 30        |

  Scenario: Fail to authenticate an anonymous user
    Given I send a GET request to "api/rest/products/sandals.json"
    Then the response code should be 401

  Scenario: Successfully authenticate a user
    Given I am authenticating as "admin" with "admin_api_key" api key
    And I send a GET request to "api/rest/products/sandals.json"
    Then the response code should be 200

  Scenario: Successfully retrieve a product
    Given I am authenticating as "admin" with "admin_api_key" api key
    And I send a GET request to "api/rest/products/sandals.json"
    Then the response code should be 200
    And the response should be valid json
    And the response should contain json:
    """
    {
      "family":null,
      "groups":[],
      "categories":[],
      "enabled":true,
      "associations":[],
      "values": {
        "sku":[
          {"locale":null,"scope":null,"value":"sandals"}
        ],
        "name":[
          {"locale":"en_US","scope":null,"value":"My sandals"}
        ],
        "description":[
          {"locale":"en_US","scope":"mobile","value":"My great sandals"},
          {"locale":"en_US","scope":"tablet","value":"My great new sandals"}
        ],
        "price":[
          {"locale":null,"scope":null,"value":[
            {"data":"20.00","currency":"EUR"},
            {"data":"30.00","currency":"USD"}
          ]}
        ]
      },
      "resource":"http:\/\/akeneo-pim-behat.local\/api\/rest\/products\/sandals"
    }
    """
