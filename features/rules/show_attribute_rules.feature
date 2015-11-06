@javascript
Feature: Show all rules related to an attribute
  In order ease the enrichment of the catalog
  As a regular user
  I need to know which rules are linked to an attribute

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And the following product rules:
      | code                   | priority |
      | copy_description       | 10       |
      | update_tees_collection | 20       |
    And the following product rule conditions:
      | rule                   | field                   | operator    | value          | locale | scope |
      | copy_description       | name                    | =           | My nice tshirt | en_US  |       |
      | copy_description       | weather_conditions.code | IN          | dry, wet       |        |       |
      | copy_description       | comment                 | STARTS WITH | promo          |        |       |
      | update_tees_collection | categories.code         | IN          | tees           |        |       |
    And the following product rule setter actions:
      | rule                   | field           | value                                                   | locale | scope  |
      | copy_description       | rating.code     | 4                                                       |        |        |
      | update_tees_collection | description     | une belle description                                   | fr_FR  | mobile |
      | update_tees_collection | number_in_stock | 800                                                     |        | tablet |
      | update_tees_collection | release_date    | 2015-05-26                                              |        | mobile |
      | update_tees_collection | price           | 12 EUR                                                  |        |        |
      | update_tees_collection | side_view       | image.jpg,%fixtures%/akeneo.jpg                         |        |        |
      | update_tees_collection | length          | 10 CENTIMETER                                           |        |        |
    And the following product rule copier actions:
      | rule                   | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | copy_description       | description | description | en_US       | en_US     | mobile     | tablet   |
      | copy_description       | description | description | en_US       | fr_FR     | mobile     | mobile   |
      | copy_description       | description | description | en_US       | fr_FR     | mobile     | tablet   |
      | update_tees_collection | name        | name        | en_US       | fr_FR     |            |          |
      | update_tees_collection | name        | name        | en_US       | de_DE     |            |          |

  Scenario: Successfully show rules of an attribute
    Given I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule conditions:
      | rule                   | field                   | operator    | value          | locale | scope |
      | copy_description       | name                    | =           | My nice tshirt | en     |       |
      | copy_description       | weather_conditions.code | IN          | dry, wet       |        |       |
      | copy_description       | comment                 | STARTS WITH | promo          |        |       |
      | update_tees_collection | categories.code         | IN          | tees           |        |       |
    Then I should see the following rule setter actions:
      | rule                   | field           | value                 | locale | scope  |
      | copy_description       | rating          | 4                     |        |        |
      | update_tees_collection | description     | une belle description | fr     | mobile |
      | update_tees_collection | number_in_stock | 800                   |        | tablet |
      | update_tees_collection | release_date    | 5/26/15               |        | mobile |
      | update_tees_collection | price           | 12 EUR                |        |        |
      | update_tees_collection | side_view       | image.jpg             |        |        |
      | update_tees_collection | length          | 10 CENTIMETER         |        |        |
    Then I should see the following rule copier actions:
      | rule                   | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | copy_description       | description | description | en          | en        | mobile     | tablet   |
      | copy_description       | description | description | en          | fr        | mobile     | mobile   |
      | copy_description       | description | description | en          | fr        | mobile     | tablet   |
      | update_tees_collection | name        | name        | en          | fr        |            |          |
      | update_tees_collection | name        | name        | en          | de        |            |          |
