@javascript
Feature: Browse product assets
  In order to list the existing product assets
  As an asset manager
  I need to be able to see product assets

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Pamela"
    And I am on the assets page

  Scenario: Successfully display product assets
    Then the grid should contain 16 elements
    And I should see the columns Thumbnail, Code, Description, End of use, Created at and Last updated at
