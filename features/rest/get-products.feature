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
    And I am authenticating as "admin" with "admin_api_key" api key
    And I request information for product "Car"
    Then the response code should be 200
    And the response should be valid json
