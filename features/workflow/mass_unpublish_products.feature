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

  Scenario: Successfully unpublish all products
    And I am logged in as "Julia"
    And I am on the published page
    And I mass-edit products unionjack and jackadi
    When I choose the "Unpublish products" operation
    Then I should see "The 2 selected products will be unpublished"
    And I should see "Confirm"
    When I apply the following mass-edit operation with the given configuration:
      | operation | filters                                                               | actions |
      | unpublish | [{"field":"sku", "operator":"IN", "value": ["unionjack", "jackadi"]}] | []      |
    And I am on the published page
    Then I should not see products unionjack and jackadi

  # TODO: Re enable this scenario when security context check will be done in backend and
  # results available on reporting screen.
  @skip
  Scenario: Successfully unpublish few products of selected
    And I am logged in as "Julia"
    And I am on the published page
    And I mass-edit products unionjack, jackadi and teafortwo
    When I choose the "Unpublish products" operation
    Then I should see "You're not the owner of all the selected products. You can't unpublish the products \"teafortwo\""
    And I should see "Confirm"

  # TODO: Re enable this scenario when security context check will be done in backend and
  # results available on reporting screen.
  @skip
  Scenario: Forbid to unpublish if user is not the owner of at least one product
    And I am logged in as "Mary"
    And I am on the published page
    And I mass-edit products unionjack, jackadi and teafortwo
    When I choose the "Unpublish products" operation
    Then I should see "You're not the owner of the selected products, you can't unpublish them"
