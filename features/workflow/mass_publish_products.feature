@javascript
Feature: Publish many products at once
  In order to froze the product data I would use to export
  As a product manager
  I need to be able to publish a product

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku       | family  | name-en_US | categories |
      | unionjack | jackets | UnionJack  | jackets    |
      | jackadi   | jackets | Jackadi    | jackets    |
      | teafortwo | tees    | My tee     | tees       |

  Scenario: Successfully publish all products
    And I am logged in as "Julia"
    And I am on the products page
    And I mass-edit products unionjack and jackadi
    When I choose the "Publish products" operation
    Then I should see "The 2 selected products will be published"
    And I should see "Confirm"

  Scenario: Successfully publish few products of selected
    And I am logged in as "Julia"
    And I am on the products page
    And I mass-edit products unionjack, jackadi and teafortwo
    When I choose the "Publish products" operation
    Then I should see "You're not the owner of all the selected products, you can't publish the following teafortwo"
    And I should see "Confirm"
    
  Scenario: Forbid to publish if user is not the owner of at least one product
    And I am logged in as "Mary"
    And I am on the products page
    And I mass-edit products unionjack, jackadi and teafortwo
    When I choose the "Publish products" operation
    Then I should see "You're not the owner of the selected products, you can't publish them"
