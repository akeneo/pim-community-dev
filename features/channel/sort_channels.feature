@javascript
Feature: Sort channels
  In order to sort channels in the catalog
  As a user
  I need to be able to sort channels by several columns in the catalog

  Scenario: Successfully sort channels
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
    Then the rows should be sorted ascending by code
    And I should be able to sort the rows by code, label and category tree
