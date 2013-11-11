@javascript
Feature: Browse channels
  In order to list the existing channels for the catalog
  As a user
  I need to be able to see channels

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

  Scenario: Successfully display channels
    Given I am on the channels page
    Then the grid should contain 6 elements
    And I should see the columns Code, Label and Category tree
    And I should see channels ecommerce, mobile, FOO, BAR, BAZ and QUX
