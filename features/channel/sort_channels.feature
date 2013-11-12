@javascript
Feature: Sort channels
  In order to sort channels in the catalog
  As a user
  I need to be able to sort channels by several columns in the catalog

  Background:
    Given the following categories:
      | code      | label      |
      | master    | Master     |
      | mobile    | Mobile     |
      | ipad      | IPad       |
      | ecommerce | E-Commerce |
    And the following channels:
      | code      | label     | locales      | category  |
      | ecommerce | Ecommerce |              | default   |
      | mobile    | Mobile    |              | default   |
      | FOO       | foo       | fr_FR, en_US | master    |
      | BAR       | bar       | de_DE        | ecommerce |
      | BAZ       | baz       | fr_FR        | mobile    |
      | QUX       | qux       | en_US        | ipad      |
    And I am logged in as "admin"

  Scenario: Successfully sort channels
    Given I am on the channels page
    Then the rows should be sorted ascending by code
    And I should be able to sort the rows by code, label and category tree
