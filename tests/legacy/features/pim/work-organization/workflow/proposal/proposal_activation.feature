@javascript
Feature: Proposal and drafts are only available if the feature is enabled

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku       | family  | name-en_US | categories |
      | unionjack | jackets | UnionJack  | jackets    |

  Scenario: Proposal feature is not available when deactivated
    And I am logged in as "Julia"
    Given I am on the dashboard page
    Then I should see the text "Activity"
    And I should not see the text "Proposals"
    When I am on the products grid
    Then I should not see the text "DRAFT STATUS"
    When I edit the "unionjack" product
    Then I should not see the text "Proposals"

  @proposal-feature-enabled
  Scenario: Proposal feature is not available when deactivated
    And I am logged in as "Julia"
    Given I am on the dashboard page
    Then I should see the text "Activity"
    And I should see the text "Proposals"
    When I am on the products grid
    Then I should see the text "DRAFT STATUS"
    When I edit the "unionjack" product
    Then I should see the text "Proposals"
