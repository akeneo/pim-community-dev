@javascript
Feature: Filter channels
  In order to filter channels in the catalog
  As a user
  I need to be able to filter channels in the catalog

  Background:
    Given the following categories:
      | code   | label  |
      | master | Master |
      | mobile | Mobile |
      | ipad   | IPad   |
    And the following channels:
      | code      | label     | locales      | category |
      | ecommerce | Ecommerce |              | default  |
      | mobile    | Mobile    |              | default  |
      | FOO       | foo       | fr_FR, en_US | master   |
      | BAR       | bar       | de_DE        | master   |
      | BAZ       | baz       | fr_FR        | mobile   |
      | QUX       | qux       | en_US        | ipad     |
    And I am logged in as "admin"

  Scenario: Successfully display filters
    Given I am on the channels page
    Then I should see the filters Code, Label and Category tree
    And the grid should contain 6 elements
    And I should see channels ecommerce, mobile, FOO, BAZ, QUX and BAR

  Scenario: Successfully filter by code
    Given I am on the channels page
    When I filter by "Code" with value "BA"
    Then the grid should contain 2 elements
    And I should see channels BAR and BAZ
    And I should not see channels ecommerce, mobile, FOO and QUX

  Scenario: Successfully filter by label
    Given I am on the channels page
    When I filter by "Label" with value "Ba"
    Then the grid should contain 2 elements
    And I should see channels BAR and BAZ
    And I should not see channels ecommerce, mobile, FOO and QUX

  Scenario: Successfully filter by label and code
    Given I am on the channels page
    When I filter by "Label" with value "Ba"
    And I filter by "Code" with value "R"
    Then the grid should contain 1 element
    And I should see channel BAR
    And I should not see channels ecommerce, mobile, BAZ, FOO and QUX

  Scenario: Successfully filter by category
    Given I am on the channels page
    When I filter by "Category tree" with value "Master"
    Then the grid should contain 2 elements
    And I should see channels FOO and BAR
    And I should not see channels ecommerce, mobile, BAZ and QUX
