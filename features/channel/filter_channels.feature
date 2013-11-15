@javascript
Feature: Filter channels
  In order to filter channels in the catalog
  As a user
  I need to be able to filter channels in the catalog

  Background:
    Given a "footwear" catalog configuration
    And the following category:
      | code            | label           |
      | 2015_collection | 2015 collection |
    And the following channels:
      | code | label | locales      | category        |
      | FOO  | foo   | fr_FR, en_US | 2015_collection |
      | BAR  | bar   | de_DE        | 2015_collection |
      | BAZ  | baz   | fr_FR        | 2014_collection |
      | QUX  | qux   | en_US        | 2014_collection |
    And I am logged in as "admin"
    And I am on the channels page

  Scenario: Successfully display filters
    Then I should see the filters Code, Label and Category tree
    And the grid should contain 6 elements
    And I should see channels tablet, mobile, FOO, BAZ, QUX and BAR

  Scenario: Successfully filter by code
    Given I filter by "Code" with value "BA"
    Then the grid should contain 2 elements
    And I should see channels BAR and BAZ
    And I should not see channels tablet, mobile, FOO and QUX

  Scenario: Successfully filter by label
    Given I filter by "Label" with value "Ba"
    Then the grid should contain 2 elements
    And I should see channels BAR and BAZ
    And I should not see channels tablet, mobile, FOO and QUX

  Scenario: Successfully filter by label and code
    Given I filter by "Label" with value "Ba"
    And I filter by "Code" with value "R"
    Then the grid should contain 1 element
    And I should see channel BAR
    And I should not see channels tablet, mobile, BAZ, FOO and QUX

  Scenario: Successfully filter by category
    Given I filter by "Category tree" with value "2015 collection"
    Then the grid should contain 2 elements
    And I should see channels FOO and BAR
    And I should not see channels tablet, mobile, BAZ and QUX
