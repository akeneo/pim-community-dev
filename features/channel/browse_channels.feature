@javascript
Feature: Browse channels
  In order to list the existing channels for the catalog
  As a user
  I need to be able to see channels

  Background:
    Given a "footwear" catalog configuration
    And the following channels:
      | code | label | locales      | category          |
      | FOO  | foo   | fr_FR, en_US | summer_collection |
      | BAR  | bar   | de_DE        | winter_collection |
      | BAZ  | baz   | fr_FR        | winter_boots      |
      | QUX  | qux   | en_US        | sandals           |
    And I am logged in as "admin"

  Scenario: Successfully display channels
    Given I am on the channels page
    Then the grid should contain 6 elements
    And I should see the columns Code, Label and Category tree
    And I should see channels tablet, mobile, FOO, BAR, BAZ and QUX
