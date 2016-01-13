@javascript
Feature: Review a product draft with reference data
  In order to control which data should be applied to a product
  As a product manager
  I need to be able to review a product draft

  Background:
    Given a "footwear" catalog configuration
    And the following reference data:
      | type   | code     |
      | fabric | pvc      |
      | fabric | nylon    |
      | fabric | neoprene |
      | fabric | spandex  |
      | fabric | wool     |
      | fabric | kevlar   |
      | fabric | jute     |
      | color  | cyan     |
      | color  | black    |
    And the product:
      | sku         | my-vans          |
      | categories  | winter_boots     |
      | sole_color  | cyan             |
      | sole_fabric | kevlar, neoprene |

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept from a product draft with a simple select reference data
    Given Mary proposed the following change to "my-vans":
      | tab   | field      | value |
      | Other | Sole color | black |
    And I am logged in as "Julia"
    And I edit the "my-vans" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Sole color"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Other" group
    Then the product Sole color should be "black"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully reject a waiting for approval product draft with a simple select reference data
    Given Mary proposed the following change to "my-vans":
      | tab   | field      | value |
      | Other | Sole color | black |
    And I am logged in as "Julia"
    And I edit the "my-vans" product
    When I visit the "Proposals" tab
    And I click on the "reject" action of the row which contains "Sole color"
    And I press the "Send" button in the popin
    Then the grid should contain 1 element
    And the row "Mary" should contain:
      | column | value       |
      | Status | In progress |
    When I visit the "Attributes" tab
    And I visit the "Other" group
    Then the product Sole color should be "cyan"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully remove an in progress product draft with a simple select reference data
    Given Mary started to propose the following change to "my-vans":
      | tab   | field      | value |
      | Other | Sole color | black |
    And I am logged in as "Julia"
    And I edit the "my-vans" product
    When I visit the "Proposals" tab
    And I click on the "remove" action of the row which contains "Sole color"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Other" group
    Then the product Sole color should be "cyan"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept from a product draft with a multi select reference data
    Given Mary proposed the following change to "my-vans":
      | tab   | field       | value      |
      | Other | Sole fabric | wool, jute |
    And I am logged in as "Julia"
    And I edit the "my-vans" product
    When I visit the "Proposals" tab
    And I click on the "approve" action of the row which contains "Sole fabric"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Other" group
    Then the product Sole fabric should be "wool, jute"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully reject a waiting for approval product draft with a multi select reference data
    Given Mary proposed the following change to "my-vans":
      | tab   | field       | value      |
      | Other | Sole fabric | wool, jute |
    And I am logged in as "Julia"
    And I edit the "my-vans" product
    When I visit the "Proposals" tab
    And I click on the "reject" action of the row which contains "Sole fabric"
    And I press the "Send" button in the popin
    Then the grid should contain 1 element
    And the row "Mary" should contain:
      | column | value       |
      | Status | In progress |
    When I visit the "Attributes" tab
    And I visit the "Other" group
    Then the product Sole fabric should be "kevlar, neoprene"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully remove an in progress product draft with a multi select reference data
    Given Mary started to propose the following change to "my-vans":
      | tab   | field       | value      |
      | Other | Sole fabric | wool, jute |
    And I am logged in as "Julia"
    And I edit the "my-vans" product
    When I visit the "Proposals" tab
    And I click on the "remove" action of the row which contains "Sole fabric"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" tab
    And I visit the "Other" group
    Then the product Sole fabric should be "kevlar, neoprene"
