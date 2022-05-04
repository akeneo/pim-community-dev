Feature:
  In order to retrieve products
  As an external application
  I want to manage catalogs using an API

  Scenario: Create a catalog
    When the external application creates a catalog using the API
    Then the PIM should store the catalog
    And the response should contain the catalog id
