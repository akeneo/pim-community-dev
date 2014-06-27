@javascript @skip
Feature: Approve a product changes proposition
  In order to contribute to a product edition
  As a contributor
  I need to be able to submit a product changes proposition

  Scenario: Successfully propose changes to a product
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | name-en_US |
      | my-sandals | sandals | Sandals    |
    And I am logged in as "Julia"
    When I edit the "my-sandals" product
    And I change the Name to "Basket"
    And I save the product
    Then the product Name should be "Basket"
    But I should see that Name is a modified value
    When I visit the "Propositions" tab
    Then the grid should contain 1 element

  Scenario: Fail to propose an empty change set to a product
    Given a "footwear" catalog configuration
    And the following product:
      | sku        | family  | name-en_US |
      | my-sandals | sandals | Sandals    |
    And I am logged in as "Julia"
    When I edit the "my-sandals" product
    And I save the product
    When I visit the "Propositions" tab
    Then the grid should contain 0 element

  # TODO : don't see the "in progress" and "propositions" tab with the same ownership status
  Scenario: Succesfully mark my proposition as ready to review
    Given the "footwear" catalog configuration
    And the following product:
      | sku        | family  |
      | my-sandals | sandals |
    And I am logged in as "Julia"
    When I edit the "my-sandals" product
    And I change the Name to "Basket"
    And I press the "In progress" button
    And I save the product
    When I visit the "Propositions" tab
    Then the row "Julia" should contain:
      | column | value |
      | Status | Ready |
