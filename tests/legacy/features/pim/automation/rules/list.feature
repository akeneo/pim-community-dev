@javascript
Feature: List all rules
  In order ease the enrichment of the catalog
  As a regular user
  I need to know which rules are applied in the application

  Background:
    Given a "clothing" catalog configuration
    And the following attributes:
      | code         | label-en_US  | type               | localizable | scopable | group | decimals_allowed | negative_allowed |
      | total_weight | Total weight | pim_catalog_number | 1           | 1        | other | 0                | 0                |
      | weight       | Weight       | pim_catalog_number | 0           | 0        | other | 0                | 0                |
      | in_stock     | In stock     | pim_catalog_number | 0           | 1        | other | 0                | 0                |
      | booked_stock | Booked stock | pim_catalog_number | 1           | 0        | other | 0                | 0                |
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
        labels:
          en_US: Copy Description
      update_tees_collection:
        priority: 20
        conditions:
          - field:    categories
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
          - type:        set
            field:       enabled
            value:       true
        labels:
          en_US: Tees Collection Update
      unclassify_2014_collection_tees:
        priority: 30
        conditions:
          - field:    family
            operator: IN
            value:
              - tees
          - field:    enabled
            operator: =
            value:    false
        actions:
          - type:   remove
            field:  categories
            items:
              - 2014_collection
            include_children: true
        labels:
          en_US: Unclassify 2014 Collection Tees
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
          - type:   remove
            field:  categories
            items:
              - 2014_collection
            include_children: true
        labels:
          en_US: Nineties
      concatenate_rule:
        priority: 90
        conditions:
          - field: family
            operator: IN
            value:
              - tees
        actions:
          - type: concatenate
            from:
              - field: sku
              - field: release_date
                scope: mobile
            to:
              field: description
              scope: tablet
              locale: en_US
        labels:
          en_US: Concatenate Rule
      clear_rule:
        priority: 90
        conditions:
          - field: family
            operator: IN
            value:
              - tees
        actions:
          - type: clear
            field: description
            scope: tablet
            locale: en_US
        labels:
          en_US: Clear Rule
      calculate_rule:
        priority: 90
        conditions:
          - field: family
            operator: IN
            value:
              - tees
        actions:
          - type: calculate
            destination:
              field: total_weight
              scope: tablet
              locale: en_US
            source:
              field: weight
            operation_list:
              - operator: multiply
                value: 5
              - operator: divide
                field: in_stock
                scope: tablet
              - operator: add
                value: 1
              - operator: subtract
                field: booked_stock
                locale: en_US
              - operator: subtract
                field: booked_stock
                locale: fr_FR
        labels:
          en_US: Calculate Rule
      """
    And I am logged in as "Julia"

  Scenario: Successfully show rules
    When I am on the rules page
    Then the rows should be sorted ascending by label
    And the grid should contain 7 elements
    And I should be able to sort the rows by label
    And I should be able to sort the rows by priority

  Scenario: Successfully search rules
    Given I am on the rules page
    When I search "description"
    Then the grid should contain 1 element
    And I should see entity Copy Description

  Scenario: successfully filter by code
    Given I am on the rules page
    When I filter by "code" with operator "contains" and value "copy_description"
    Then the grid should contain 1 element
    And I should see entity Copy Description

  Scenario: Successfully delete a rule
    Given I am on the rules page
    When I click on the "Delete" action of the row which contains "Copy Description"
    And I should see the text "Confirm deletion"
    And I should see the text "Are you sure you want to delete this rule?"
    And I confirm the deletion
    And the grid should contain 6 elements

  Scenario: Successfully delete a set of rules using bulk action
    Given I am on the rules page
    When I select rows Calculate Rule, Clear Rule, Concatenate Rule, Copy Description, Nineties, Tees Collection Update and Unclassify 2014 Collection Tees
    And I press the "Delete" bottom button
    Then I should see the text "Confirm deletion"
    And I should see the text "Are you sure you want to delete the selected rules?"
    When I confirm the deletion
    Then the grid should contain 0 elements

  Scenario: Successfully execute a set of rules using bulk action
    Given the following products:
      | sku       | family  |
      | my-jacket | jackets |
    And the following product values:
      | product   | attribute | value    | locale | scope |
      | my-jacket | name      | Original | en_US  |       |
    And the following product rule definitions:
      """
      set_name_to_Ipsum:
        priority: 30
        conditions:
          - field:    sku
            operator: =
            value:    my-jacket
        actions:
          - type:   set
            field:  name
            value:  Ipsum
            locale: en_US
      copy_en_to_fr:
        priority: 20
        conditions:
          - field:    sku
            operator: =
            value:    my-jacket
        actions:
          - type:        copy
            from_field:  name
            from_locale: en_US
            to_field:    name
            to_locale:   fr_FR
      set_name_to_Lorem:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value:    my-jacket
        actions:
          - type:   set
            field:  name
            value:  Lorem
            locale: en_US
      """
    And I am on the rules page
    When I select rows set_name_to_Lorem and copy_en_to_fr
    And I press the "Execute" bottom button
    Then I should see the text "Confirm execution"
    And I should see the text "Are you sure you want to execute the rules? (It could take a while)"
    When I confirm the execution
    And I am on the "my-jacket" product page
    And I visit the "Attributes" column tab
    And I visit the "Product Information" group
    Then the product "my-jacket" should have the following values:
      | name-fr_FR | Original |
      | name-en_US | Lorem    |
