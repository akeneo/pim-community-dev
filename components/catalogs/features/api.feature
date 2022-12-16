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
  Scenario: Get product's identifiers from an enabled catalog
    Given an enabled catalog with product selection criteria
    When the external application retrieves the product's identifiers using the API
    Then the response should contain only the product's identifiers from the selection

  @database
  Scenario: Get product's uuids from an enabled catalog
    Given an enabled catalog with product selection criteria
    When the external application retrieves the product's uuids using the API
    Then the response should contain only the product's uuids from the selection

  @database
  Scenario: Get products from an enabled catalog
    Given an enabled catalog with product selection criteria
    When the external application retrieves the products using the API
    Then the response should contain only the products from the selection

  @database
  Scenario: Get product's identifiers from a disabled catalog
    Given a disabled catalog
    When the external application retrieves the product's identifiers using the API
    Then the response should contain an error message

  @database
  Scenario: Get product's uuids from a disabled catalog
    Given a disabled catalog
    When the external application retrieves the product's uuids using the API
    Then the response should contain an error message

  @database
  Scenario: Get products from a disabled catalog
    Given a disabled catalog
    When the external application retrieves the products using the API
    Then the response should contain an error message

  @database
  Scenario: Get product mapping schema of a catalog
    Given an existing catalog with a product mapping schema
    When the external application retrieves the catalog product mapping schema using the API
    Then the response should contain the catalog product mapping schema

  @database
  Scenario: Update product mapping schema of a catalog
    Given an existing catalog
    When the external application updates a catalog product mapping schema using the API
    Then the catalog product mapping schema should be updated in the PIM

  @database
  Scenario: Delete product mapping schema of a catalog
    Given an existing catalog with a product mapping schema
    When the external application deletes a catalog product mapping schema using the API
    Then the catalog product mapping schema should be empty in the PIM

  @database
  Scenario: Get mapped products of a catalog
    Given an existing catalog with a product mapping
    When the external application gets mapped products using the API
    Then the response should contain the mapped products

  @database
  Scenario: Get mapped product of a catalog
    Given an existing catalog with a product mapping
    When the external application gets mapped product using the API
    Then the response should contain the mapped product
