@javascript
Feature: Display proposition widget
  In order to easily see which products have pending propositions
  As a product manager
  I need to be able to see a widget with pending propositions on the dashboard

  Scenario: Display proposition widget
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    When I am on the dashboard page
    Then I should see "Propositions to review"
    And I should see "No propositions to review"

  Scenario: Successfully display all opened propositions
    Given the "footwear" catalog configuration
    And the following product:
      | sku        | family  |
      | my-sandals | sandals |
    And the following propositions:
      | product    | author |
      | my-sandals | Mary   |
    And I am logged in as "Julia"
    When I am on the dashboard page
    Then I should see "Propositions to review"
    And I should the following proposition:
      | product    | author |
      | my-sandals | Mary   |
