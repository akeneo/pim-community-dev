@javascript
Feature: Browse proposals for a specific product
  In order to list the existing proposals for a specific product
  As a user
  I need to be able to see proposals

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

  Scenario: Successfully display proposals
    Given I edit the "black-boots" product
    When I visit the "Proposals" tab
    Then the grid should contain 2 elements
    And I should see the columns Author, Changes, Proposed at, Status and Locale context
    And I should see proposals admin and peter
