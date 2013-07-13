Feature: Expose product data via a REST API
  In order to provide access to product data to an external application
  As a developer
  I need to expose product data via a REST API

  Scenario: Fail to authenticate an anonymous user
    Given I request the products API page
    Then the response code should be 401

  Scenario: Successfully authenticate a user
    Given I request the products API page with a valid authentication token
    Then the response code should be 200
