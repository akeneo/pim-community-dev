@javascript @proposal-feature-enabled
Feature: Partial review a proposal
  In order to easily review changes in a proposal
  As a product owner
  I need to be able to partially approve or refuse a proposal

  Background:
    Given an "clothing" catalog configuration
    And the following product:
      | sku    | categories |
      | jacket | jackets    |
    And the following product values:
      | product | attribute          | value | locale | scope |
      | jacket  | name               | Coat  | en_US  |       |
      | jacket  | weather_conditions | dry   |        |       |
    And Mary proposed the following change to "jacket":
      | field              | value  |
      | Name               | Jacket |
      | Weather conditions | Snowy  |
    And I am logged in as "Julia"

  Scenario: Successfully partial reject then a partial approve a proposal
    Given I am on the proposals page
    And the grid should contain 1 element
    And I should see the following proposals:
      | product | author | attribute          | original | new    |
      | jacket  | Mary   | name               | Coat     | Jacket |
      | jacket  | Mary   | weather_conditions | Dry      | Snowy  |
    When I partially approve:
      | product | author | attribute          | locale | scope |
      | jacket  | Mary   | weather_conditions |        |       |
    Then the grid should contain 1 element
    And I should see the following proposals:
      | product | author | attribute | original | new    |
      | jacket  | Mary   | name      | Coat     | Jacket |
    When I partially reject:
      | product | author | attribute | locale | scope |
      | jacket  | Mary   | name      | en_US  |       |
    Then the grid should contain 0 element
    And I should not get the following proposal:
      | jacket | Mary |

  Scenario: Don't apply proposal part which has been partially rejected
    Given I am on the proposals page
    And the grid should contain 1 element
    And I should see the following proposals:
      | product | author | attribute          | original | new    |
      | jacket  | Mary   | name               | Coat     | Jacket |
      | jacket  | Mary   | weather_conditions | Dry      | Snowy  |
    When I partially reject:
      | product | author | attribute          | locale | scope |
      | jacket  | Mary   | weather_conditions |        |       |
    Then the grid should contain 1 element
    And I should see the following proposals:
      | product | author | attribute | original | new    |
      | jacket  | Mary   | name      | Coat     | Jacket |
    When I click on the "Approve all" action of the row which contains "jacket"
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    And I should not get the following proposal:
      | jacket | Mary |
    When I edit the "jacket" product
    Then the field Name should contain "Jacket"
    And the field Weather conditions should contain "Dry"

  Scenario: Successfully update a proposal which has been partially rejected by updating and sending a new draft for approval
    Given I am on the proposals page
    And the grid should contain 1 element
    And I should see the following proposals:
      | product | author | attribute          | original | new    |
      | jacket  | Mary   | name               | Coat     | Jacket |
      | jacket  | Mary   | weather_conditions | Dry      | Snowy  |
    When I partially reject:
      | product | author | attribute          | locale | scope |
      | jacket  | Mary   | weather_conditions |        |       |
    Then the grid should contain 1 element
    And I should see the following proposals:
      | product | author | attribute | original | new    |
      | jacket  | Mary   | name      | Coat     | Jacket |
    And I logout
    When Mary proposed the following change to "jacket":
      | field              | value |
      | Weather conditions | Wet   |
    And I am logged in as "Julia"
    And I am on the proposals page
    Then I should see the following proposals:
      | product | author | attribute          | original | new    |
      | jacket  | Mary   | name               | Coat     | Jacket |
      | jacket  | Mary   | weather_conditions | Dry      | Wet    |
