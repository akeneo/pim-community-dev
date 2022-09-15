Feature:
  In order to retrieve relevant products
  As an external application
  I want to manage catalogs using an API

  @database
  Scenario: Create a catalog
    When the external application creates a catalog using the API
    Then the response should contain the catalog id
    And the catalog should exist in the PIM

  @database
  Scenario: Get a catalog
    Given an existing catalog
    When the external application retrieves the catalog using the API
    Then the response should contain the catalog details

  @database
  Scenario: Delete a catalog
    Given an existing catalog
    When the external application deletes a catalog using the API
    Then the response should be empty
    And the catalog should be removed from the PIM

  @database
  Scenario: Update a catalog
    Given an existing catalog
    When the external application updates a catalog using the API
    Then the response should contain the catalog details
    And the catalog should be updated in the PIM

  @database
  Scenario: Get catalogs
    Given several existing catalogs
    When the external application retrieves the catalogs using the API
    Then the response should contain catalogs details

  @database
  Scenario: Get products identifiers
    Given a catalog sets up with a product selection
    When the external application retrieves the product identifiers using the API
    Then the response should contain only the product identifiers from the selection

  @database
  Scenario: Get products uuids
    Given a catalog sets up with a product selection
    When the external application retrieves the product uuids using the API
    Then the response should contain only the product uuids from the selection

  @database
  Scenario: Get products
    Given a catalog sets up with a product selection
    When the external application retrieves the products using the API
    Then the response should contain only the products from the selection
