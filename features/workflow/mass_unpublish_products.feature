@javascript
Feature: Unpublish many products at once
  In order to unfroze the product data
  As a product manager
  I need to be able to unpublish several products at the same time

  Background:
    Given a "clothing" catalog configuration
    And the following published product:
      | sku       | family  | name-en_US | categories |
      | unionjack | jackets | UnionJack  | jackets    |
      | jackadi   | jackets | Jackadi    | jackets    |
      | teafortwo | tees    | My tee     | tees       |

  @skip @jira https://akeneo.atlassian.net/browse/PIM-4600
  Scenario: Successfully unpublish all products
    Given I am logged in as "Julia"
    And I am on the published page
    And I mass-edit products unionjack and jackadi
    When I choose the "Unpublish products" operation
    Then I should see "The 2 selected products will be unpublished"
    And I move on to the next step
    And I wait for the "unpublish" mass-edit job to finish
    And I am on the published page
    Then I should not see products unionjack and jackadi

  @skip @jira https://akeneo.atlassian.net/browse/PIM-4600
  Scenario: Only unpublish products on which user is the owner
    Given I am logged in as "Julia"
    And I am on the published page
    And I mass-edit products unionjack, jackadi and teafortwo
    When I choose the "Unpublish products" operation
    And I move on to the next step
    And I wait for the "unpublish" mass-edit job to finish
    Then I should see "You're not the owner of the product, you can't unpublish it"
    And I should see "skipped products 1"
    When I am on the published index page
    Then the grid should contain 1 elements

  @skip @jira https://akeneo.atlassian.net/browse/PIM-4600
  Scenario: Unpublish nothing if the user is the owner of no product
    Given I am logged in as "Mary"
    And I am on the published page
    And I mass-edit products unionjack, jackadi and teafortwo
    When I choose the "Unpublish products" operation
    And I move on to the next step
    And I wait for the "unpublish" mass-edit job to finish
    Then I should see "You're not the owner of the product, you can't unpublish it"
    And I should see "skipped products 3"
    When I am on the published index page
    Then the grid should contain 3 elements
