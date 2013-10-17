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
      | mobile    | Mobile    |              | default   |
      | FOO       | foo       | fr_FR, en_US | master    |
      | BAR       | bar       | de_DE        | ecommerce |
      | BAZ       | baz       | fr_FR        | mobile    |
      | QUX       | qux       | en_US        | ipad      |
    And I am logged in as "admin"

  Scenario: Successfully display the sortable columns
    Given I am on the channels page
    Then the rows should be sortable by code, label and category tree
    And the rows should be sorted ascending by code
    And I should see sorted channels BAR, BAZ, ecommerce, FOO, mobile and QUX

  Scenario: Successfully sort channels by code
    Given I am on the channels page
    When I sort by "code" value ascending
    Then I should see sorted channels BAR, BAZ, ecommerce, FOO, mobile and QUX
    When I sort by "code" value descending
    Then I should see sorted channels QUX, mobile, FOO, ecommerce, BAZ and BAR

  Scenario: Successfully sort channels by label
    Given I am on the channels page
    When I sort by "label" value ascending
    Then I should see sorted channels BAR, BAZ, ecommerce, FOO, mobile and QUX
    When I sort by "label" value descending
    Then I should see sorted channels QUX, mobile, FOO, ecommerce, BAZ and BAR

  Scenario: Successfully sort channels by tree
    Given I am on the channels page
    When I sort by "category tree" value ascending
    Then I should see sorted channels ecommerce, mobile, BAR, QUX, FOO and BAZ
    When I sort by "category tree" value descending
    Then I should see sorted channels BAZ, FOO, QUX, BAR, ecommerce and mobile
