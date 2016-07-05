@javascript
Feature: Execute rules from the user interface
  In order to run the rules
  As a product manager
  I need to be able to launch their execution from the "Settings/Rules" screen

  Background:
    Given the "clothing" catalog configuration
    And the following product rule definitions:
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
          - field:    enabled
            operator: =
            value:    false
          - field: description
            locale: en_US
            scope: mobile
            operator: EMPTY
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
          - type:        set
            field:       enabled
            value:       true
      """
    And I am logged in as "Julia"
    And I am on the rules page

  Scenario: Successfully execute all rules from the user interface
    Given I press the "Execute rules" button
    When I confirm the rules execution
    And I am on the rules page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                           |
      | success | Execution of the rule(s) finished |
