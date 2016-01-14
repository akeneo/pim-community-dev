@javascript
Feature: List all rules
  In order ease the enrichment of the catalog
  As a regular user
  I need to know which rules are applied in the application

  Background:
    Given a "clothing" catalog configuration
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
    And I am logged in as "Julia"
    And I am on the rules page

  Scenario: Successfully show rules
    Then the rows should be sorted ascending by code
    And the grid should contain 2 elements
    And I should be able to sort the rows by code

    And the row "copy_description" should contain the texts:
      | column    | value                                                                         |
      | Condition | If name equals My nice tshirt [ en ]                                          |
      | Condition | If weather_conditions.code in dry, wet                                        |
      | Condition | If comment starts with promo                                                  |
      | Action    | Then 4 is set into rating                                                     |
      | Action    | Then description [ en \| mobile ] is copied into description [ en \| tablet ] |
      | Action    | Then description [ en \| mobile ] is copied into description [ fr \| mobile ] |
      | Action    | Then description [ en \| mobile ] is copied into description [ fr \| tablet ] |

    And the row "update_tees_collection" should contain the texts:
      | column    | value                                                               |
      | Condition | If categories.code in tees                                          |
      | Action    | Then une belle description is set into description [ fr \| mobile ] |
      | Action    | Then 800 is set into number_in_stock [ tablet ]                     |
      | Action    | Then 05/26/2015 is set into release_date [ mobile ]                 |
      | Action    | Then €12.00 is set into price                                       |
      | Action    | Then image.jpg is set into side_view                                |
      | Action    | Then 10 Centimeter is set into length                               |
      | Action    | Then name [ en ] is copied into name [ fr ]                         |
      | Action    | Then name [ en ] is copied into name [ de ]                         |

    And I should be able to use the following filters:
      | filter | value       | result           |
      | Code   | description | copy_description |

  Scenario: Successfully delete a rule
    When I click on the "Delete" action of the row which contains "copy_description"
    And I should see the text "Delete Confirmation"
    And I should see the text "Are you sure you want to delete this item?"
    And I confirm the deletion
    And the grid should contain 1 elements
