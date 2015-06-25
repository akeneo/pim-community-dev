@javascript
Feature: Browse product assets
  In order to list the existing product assets
  As a product manager
  I need to be able to see product assets

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And I am on the assets page

  Scenario: Successfully display product assets
    Then the grid should contain 11 elements
    And I should see the columns Thumbnail, Code, Description, Status, End of use, Created at and Last updated at
