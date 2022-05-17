Feature:
  In order to retrieve relevant products
  As an external application
  I want to manage catalogs using an API

  @database
  Scenario: Create a catalog
    When the external application creates a catalog using the API
    Then the response should contain the catalog id
    And the catalog should exist in the PIM
