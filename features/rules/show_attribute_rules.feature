@javascript
Feature: Show all rules related to an attribute
  In order ease the enrichment of the catalog
  As a regular user
  I need to know which rules are linked to an attribute

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    Given the following product rule definitions:
      """
      copy_description:
        priority: 10
        conditions:
          - field:    name
            operator: =
            value:    My nice tshirt
            locale:   en_US
          - field:    weather_conditions.code
            operator: IN
            value:
              - dry
              - wet
          - field:    comment
            operator: STARTS WITH
            value:    promo
        actions:
          - type:   set
            field:  rating
            value:  "4"
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   en_US
            from_scope:  mobile
            to_scope:    tablet
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   fr_FR
            from_scope:  mobile
            to_scope:    mobile
          - type:        copy
            from_field:  description
            to_field:    description
            from_locale: en_US
            to_locale:   fr_FR
            from_scope:  mobile
            to_scope:    tablet
      update_tees_collection:
        priority: 20
        conditions:
          - field:    categories.code
            operator: IN
            value:
              - tees
        actions:
          - type:   set
            field:  description
            value:  une belle description
            locale: fr_FR
            scope:  mobile
          - type:  set
            field: number_in_stock
            value: 800
            scope: tablet
          - type:  set
            field: release_date
            value: "2015-05-26"
            scope:  mobile
          - type:  set
            field: price
            value:
              - data: 12
                currency: EUR
          - type:  set
            field: side_view
            value:
              originalFilename: image.jpg
              filePath: %fixtures%/akeneo.jpg
          - type:  set
            field: length
            value:
              data: 10
              unit: CENTIMETER
          - type:        copy
            from_field:  name
            to_field:    name
            from_locale: en_US
            to_locale:   fr_FR
          - type:        copy
            from_field:  name
            to_field:    name
            from_locale: en_US
            to_locale:   de_DE
      """

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
      | update_tees_collection | release_date    | 05/26/2015            |        | mobile |
      | update_tees_collection | price           | €12.00                |        |        |
      | update_tees_collection | side_view       | image.jpg             |        |        |
      | update_tees_collection | length          | 10 Centimeter         |        |        |
    Then I should see the following rule copier actions:
      | rule                   | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | copy_description       | description | description | en          | en        | mobile     | tablet   |
      | copy_description       | description | description | en          | fr        | mobile     | mobile   |
      | copy_description       | description | description | en          | fr        | mobile     | tablet   |
      | update_tees_collection | name        | name        | en          | fr        |            |          |
      | update_tees_collection | name        | name        | en          | de        |            |          |
