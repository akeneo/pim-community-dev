@javascript
Feature: Export rules
  In order to be able to access and modify rules outside PIM
  As an administrator
  I need to be able to export rules

  Scenario: Successfully export rules
    Given a "clothing" catalog configuration
    And the following product rule definitions:
    """
    set_name:
      priority: 10
      conditions:
        - field: sku
          operator: '='
          value: my-loafer
      actions:
        - type: set
          field: name
          value: 'My loafer'
          locale: en_US
    set_another_name:
      priority: 20
      conditions:
        - field: sku
          operator: 'STARTS WITH'
          value: my
      actions:
        - type: set
          field: description
          value: 'A stylish white loafer'
          locale: en_US
          scope: mobile
    copy_name_loafer:
      priority: 30
      conditions:
        - field: sku
          operator: '='
          value: my-loafer
        -
          field: sku
          operator: '='
          value: my-loafer
      actions:
        - type: copy
          from_field: name
          to_field: name
          from_locale: en_US
          to_locale: fr_FR
    remove_categories:
      priority: 40
      conditions:
        - field:    enabled
          operator: =
          value:    false
      actions:
        - type:  remove
          field: categories
          items:
            - 2014_collection
          include_children: true
    """
    And the following job "clothing_rule_export" configuration:
      | filePath | %tmp%/rule_export/rule_export.yml |
    And I am logged in as "Peter"
    And I am on the "clothing_rule_export" export job page
    When I launch the export job
    And I wait for the "clothing_rule_export" job to finish
    Then exported yaml file of "clothing_rule_export" should contain:
    """
    rules:
        set_name:
            priority: 10
            conditions:
                -
                    field: sku
                    operator: '='
                    value: my-loafer
            actions:
                -
                    type: set
                    field: name
                    value: 'My loafer'
                    locale: en_US
        set_another_name:
            priority: 20
            conditions:
                -
                    field: sku
                    operator: 'STARTS WITH'
                    value: my
            actions:
                -
                    type: set
                    field: description
                    value: 'A stylish white loafer'
                    locale: en_US
                    scope: mobile
        copy_name_loafer:
            priority: 30
            conditions:
                -
                    field: sku
                    operator: '='
                    value: my-loafer
                -
                    field: sku
                    operator: '='
                    value: my-loafer
            actions:
                -
                    type: copy
                    from_field: name
                    to_field: name
                    from_locale: en_US
                    to_locale: fr_FR
        remove_categories:
            priority: 40
            conditions:
                - field:    enabled
                  operator: =
                  value:    false
            actions:
                - type:  remove
                  field: categories
                  items:
                      - 2014_collection
                  include_children: true
    """
