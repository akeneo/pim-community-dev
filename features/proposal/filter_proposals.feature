@javascript
Feature: Filter proposals
  In order to easily find proposals for the product
  As an admin
  I need to be able to filter them

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family |
      | black-boots | boots  |
      | white-boots | boots  |
    And the following proposals:
      | product     | status   | author | locale |
      | black-boots | approved | admin  | en_US  |
      | black-boots | waiting  | peter  | fr_FR  |
      | grey-boots  | canceled | julia  | en_US  |
    And I am logged in as "admin"

  Scenario: Successfully filter proposals
    Given I edit the "black-boots" product
    When I visit the "Proposals" tab
    Then the grid should contain 2 elements
    And I should see proposals approved and waiting
    And I should be able to use the following filters:
      | filter         | value           | result |
      | Status         | approved        | admin  |
      | Locale context | French (France) | peter  |
