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
      enabled: false
      conditions:
        - field: sku
          operator: '='
          value: my-loafer
      actions:
        - type: set
          field: name
          value: 'My loafer'
          locale: en_US
      labels:
        en_US: 'Set name'
        fr_FR: 'Met le nom'
    set_another_name:
      priority: 20
      enabled: true
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
            enabled: false
            conditions:
                -
                    field: sku
                    operator: '='
                    value: my-loafer
            actions:
                -
                    field: name
                    locale: en_US
                    type: set
                    value: 'My loafer'
            labels:
                en_US: 'Set name'
                fr_FR: 'Met le nom'
        set_another_name:
            priority: 20
            enabled: true
            conditions:
                -
                    field: sku
                    operator: 'STARTS WITH'
                    value: my
            actions:
                -
                    field: description
                    locale: en_US
                    scope: mobile
                    type: set
                    value: 'A stylish white loafer'
            labels: {}
        copy_name_loafer:
            priority: 30
            enabled: true
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
                    from_field: name
                    from_locale: en_US
                    to_field: name
                    to_locale: fr_FR
                    type: copy
            labels: {}
        remove_categories:
            priority: 40
            enabled: true
            conditions:
                -
                    field: enabled
                    operator: '='
                    value: false
            actions:
                -
                    field: categories
                    include_children: true
                    items:
                        - 2014_collection
                    type:  remove
            labels: {}
    """
