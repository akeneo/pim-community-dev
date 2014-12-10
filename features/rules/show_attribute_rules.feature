@javascript
Feature: Show all rules related to an attribute
  In order ease the enrichement of the catalog
  As a regular user
  I need to know which rules are

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following product rules:
      | code                       | priority |
      | copy_description_into_name | 10       |
      | copy_fallback              | 20       |
    And the following product rule conditions:
      | rule                       | field              | operator    | value          | locale | scope  |
      | copy_description_into_name | name               | =           | My nice tshirt | en_US  |        |
      | copy_description_into_name | description        | EMPTY       |                |        | mobile |
      | copy_description_into_name | weather_conditions | CONTAINS    | dry,wet        | fr_FR  | mobile |
      | copy_description_into_name | comment            | STARTS WITH | promo          | de_DE  | print  |
      | copy_fallback              | category           | IN          | men            |        |        |
    And the following product rule setter actions:
      | rule                       | field         | value                | locale | scope     |
      | copy_description_into_name | description   | a nice tshirt        | en_US  |           |
      | copy_fallback              | description   | an other description | fr_FR  | ecommerce |

  Scenario: Successfully show rules of an attribute
    Given I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule conditions:
      | rule                       | field                | operator    | value          | locale | scope  |
      | copy_description_into_name | name                 | =           | My nice tshirt | en     |        |
      | copy_description_into_name | description          | EMPTY       |                |        | mobile |
      | copy_description_into_name | weather_conditions   | CONTAINS    | dry,wet        | fr     | mobile |
      | copy_description_into_name | comment              | STARTS WITH | promo          | de     | print  |
      | copy_fallback              | category             | IN          | men            |        |        |
    Then I should see the following rule actions:
      | rule                       | type      | field       | value                | locale | scope     |
      | copy_description_into_name | set_value | description | a nice tshirt        | en     |           |
      | copy_fallback              | set_value | description | an other description | fr     | ecommerce |
