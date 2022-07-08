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
