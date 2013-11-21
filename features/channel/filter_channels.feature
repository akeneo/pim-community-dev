@javascript
Feature: Filter channels
  In order to filter channels in the catalog
  As a user
  I need to be able to filter channels in the catalog

  Scenario: Successfully filter channels
    Given a "footwear" catalog configuration
    And the following category:
      | code            | label           |
      | 2015_collection | 2015 collection |
    And the following channels:
      | code | label | locales      | category        |
      | FOO  | foo   | fr_FR, en_US | 2015_collection |
      | BAR  | bar   | de_DE        | 2015_collection |
      | BAZ  | baz   | fr_FR        | 2014_collection |
    And I am logged in as "admin"
    And I am on the channels page
    Then the grid should contain 5 elements
    And I should see channels tablet, mobile, FOO, BAZ and BAR
    And I should be able to use the following filters:
      | filter        | value           | result         |
      | Code          | BA              | BAR and BAZ    |
      | Label         | o               | Mobile and FOO |
      | Category tree | 2015 collection | FOO and BAR    |
