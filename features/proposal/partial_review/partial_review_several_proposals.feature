@javascript
Feature: Partial review several proposals
  In order to easily review changes in proposals
  As a product owner
  I need to be able to partially approve, refuse, and mass review proposals

  Background:
    Given an "clothing" catalog configuration
    And the following product:
      | sku    | categories |
      | jacket | jackets    |
      | coat   | jackets    |
    And the following product values:
      | product | attribute          | value  | locale | scope  |
      | jacket  | name               | Jacket | en_US  |        |
      | jacket  | weather_conditions | wet    |        |        |
      | coat    | name               | Caut   | en_US  |        |
      | coat    | weather_conditions | hot    |        |        |

  Scenario: Successfully partial reject then approve all proposals
    Given Mary proposed the following change to "jacket":
      | field              | value  |
      | Name               | Jaquet |
      | Weather conditions | Dry    |
    And Mary proposed the following change to "coat":
      | field              | value |
      | Name               | Coat  |
      | Weather conditions | Cold  |
    When I am logged in as "Julia"
    And I am on the proposals page
    And the grid should contain 2 elements
    And I should see the following proposals:
      | product | author | attribute          | original | new    |
      | coat    | Mary   | name               | Caut     | Coat   |
      | coat    | Mary   | weather_conditions | Hot      | Cold   |
      | jacket  | Mary   | name               | Jacket   | Jaquet |
      | jacket  | Mary   | weather_conditions | Wet      | Dry    |
    When I partially reject:
      | product | author | attribute | locale | scope |
      | jacket  | Mary   | name      | en_US  |       |
    Then the grid should contain 2 elements
    And I should see the following proposals:
      | product | author | attribute          | original | new    |
      | coat    | Mary   | name               | Caut     | Coat   |
      | coat    | Mary   | weather_conditions | Hot      | Cold   |
      | jacket  | Mary   | weather_conditions | Wet      | Dry    |
    When I press the "All" button
    And I press "Approve all selected" on the "Bulk Action" dropdown button
    And I press the "Send" button in the popin
    Then the grid should contain 0 element
    And I should not get the following proposals:
      | jacket | Mary |
      | coat   | Mary |
    When I edit the "jacket" product
    Then the field Name should contain "Jacket"
    And the field Weather conditions should contain "Dry"
    When I edit the "coat" product
    Then the field Name should contain "Coat"
    And the field Weather conditions should contain "Cold"
