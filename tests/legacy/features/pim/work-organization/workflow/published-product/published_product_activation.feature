@javascript
Feature: Published products are only available if the feature is enabled

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku       | family  | name-en_US | categories |
      | unionjack | jackets | UnionJack  | jackets    |

  Scenario: Published product feature is not available when deactivated
    And I am logged in as "Julia"
    And I am on the products grid
    And I select rows unionjack
    And I press the "Bulk actions" button
    Then I should not see the text "Publish"
    When I edit the "unionjack" product
    Then I should not see the secondary action "Publish"


    # Test of the feature when it's activated is done thanks to all the other tests of the feature
