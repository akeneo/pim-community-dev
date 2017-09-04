@javascript
Feature: Show all rules related to an attribute
  In order ease the enrichment of the catalog
  As a regular user
  I need to know which rules are linked to an attribute

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And the following products:
      | sku     |
      | BOOTBXS |
    And the following product values:
      | product | attribute | value                 |
      | BOOTBXS | side_view | %fixtures%/akeneo.jpg |
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
          - field:    categories
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
              - amount: 12
                currency: EUR
          - type:  set
            field: side_view
            value: %fixtures%/akeneo.jpg
          - type:  set
            field: length
            value:
              amount: 10
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
      nineties:
        priority: 30
        conditions:
          - field: release_date
            operator: BETWEEN
            scope: mobile
            value:
              - "1990-01-15"
              - "2000-01-15"
        actions:
          - type:        copy
            from_field:  name
            to_field:    name
            from_locale: en_US
            to_locale:   de_DE
      """

  Scenario: Successfully show rules of an attribute
    Given I am on the "description" attribute page
    And I visit the "Rules" tab

    Then the row "copy_description" should contain the texts:
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
      | Condition | If categories in tees                                               |
      | Action    | Then une belle description is set into description [ fr \| mobile ] |
      | Action    | Then 800 is set into number_in_stock [ tablet ]                     |
      | Action    | Then 05/26/2015 is set into release_date [ mobile ]                 |
      | Action    | Then €12.00 is set into price                                       |
      | Action    | Then akeneo.jpg is set into side_view                               |
      | Action    | Then 10 Centimeter is set into length                               |
      | Action    | Then name [ en ] is copied into name [ fr ]                         |
      | Action    | Then name [ en ] is copied into name [ de ]                         |

  @jira https://akeneo.atlassian.net/browse/PIM-6269
  Scenario: Successfully display rules containing an array of date
    Given I am on the rules page
    Then the row "nineties" should contain the texts:
      | column    | value                                                     |
      | Condition | If release_date between 01/15/1990, 01/15/2000 [ mobile ] |
