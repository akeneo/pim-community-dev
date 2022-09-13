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
    Given an existing catalog
    And the catalog is enabled
    And the catalog has product selection criteria
    And the following products exist:
      | uuid                                 | identifier    | enabled |
      | 21a28f70-9cc8-4470-904f-aeda52764f73 | t-shirt blue  | 0       |
      | 62071b85-67af-44dd-8db1-9bc1dab393e7 | t-shirt green | 1       |
    When the external application retrieves the product identifiers using the API
    Then the response should contain the following product identifiers:
      | identifier    |
      | t-shirt green |

  @database
  Scenario: Get products uuids
    Given an existing catalog
    And the catalog is enabled
    And the catalog has product selection criteria
    And the following products exist:
      | uuid                                 | identifier    | enabled |
      | 21a28f70-9cc8-4470-904f-aeda52764f73 | t-shirt blue  | 1       |
      | 62071b85-67af-44dd-8db1-9bc1dab393e7 | t-shirt green | 0       |
      | a43209b0-cd39-4faf-ad1b-988859906030 | t-shirt red   | 1       |
    When the external application retrieves the product uuids using the API
    Then the response should contain the following product uuids:
      | uuid                                 |
      | 21a28f70-9cc8-4470-904f-aeda52764f73 |
      | a43209b0-cd39-4faf-ad1b-988859906030 |

  @database
  Scenario: Get products
    Given an existing catalog
    And the catalog is enabled
    And the catalog has product selection criteria
    And the following products exist:
      | uuid                                 | identifier    | enabled |
      | 21a28f70-9cc8-4470-904f-aeda52764f73 | t-shirt blue  | 1       |
      | 62071b85-67af-44dd-8db1-9bc1dab393e7 | t-shirt green | 0       |
    When the external application retrieves the products using the API
    Then the response should contain the following products:
      | uuid                                 | identifier   |
      | 21a28f70-9cc8-4470-904f-aeda52764f73 | t-shirt blue |
