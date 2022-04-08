@javascript @proposal-feature-enabled
Feature: Review a product draft with reference data
  In order to control which data should be applied to a product
  As a product manager
  I need to be able to review a product draft

  Background:
    Given a "footwear" catalog configuration
    And the product:
      | sku         | my-vans          |
      | categories  | winter_boots     |
      | family      | heels            |
      | sole_color  | cyan             |
      | sole_fabric | kevlar, neoprene |

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully accept from a product draft with simple and multi select reference data
    Given Mary proposed the following change to "my-vans":
      | tab   | field       | value                 |
      | Other | Sole color  | UA Red                |
      | Other | Sole fabric | Tweed, Haircloth      |
    And I am logged in as "Julia"
    When I am on the proposals page
    Then I should see the following proposals:
      | product  | author | attribute   | original        | new              |
      | my-vans  | Mary   | sole_color  | cyan            | UA Red           |
      | my-vans  | Mary   | sole_fabric | kevlar,neoprene | Tweed, Haircloth |
    And I edit the "my-vans" product
    When I visit the "Proposals" column tab
    And I click on the "Approve all" action of the row which contains "Sole color"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    When I visit the "Attributes" column tab
    And I visit the "Other" group
    Then the product Sole color should be "ua-red"
    And the product Sole fabric should be "tweed, haircloth"
