@javascript
Feature: Export rules
  In order to be able to access and modify rules outside PIM
  As an administrator
  I need to be able to export rules

  Scenario: Successfully export rules
    Given a "clothing" catalog configuration
    And the following product rules:
      | code             | priority |
      | set_name         | 10       |
      | set_another_name | 20       |
      | copy_name_loafer | 30       |
    And the following product rule conditions:
      | rule             | field | operator    | value     |
      | set_name         | sku   | =           | my-loafer |
      | set_another_name | sku   | STARTS WITH | my        |
      | copy_name_loafer | sku   | =           | my-loafer |
    And the following product rule setter actions:
      | rule             | field       | value                  | locale | scope  |
      | set_name         | name        | My loafer              | en_US  |        |
      | set_another_name | description | A stylish white loafer | en_US  | mobile |
    And the following product rule conditions:
      | rule             | field | operator | value     |
      | copy_name_loafer | sku   | =        | my-loafer |
    And the following product rule copier actions:
      | rule             | from_field | to_field | from_locale | to_locale | from_scope | to_scope |
      | copy_name_loafer | name       | name     | en_US       | fr_FR     |            |          |
    And the following job "clothing_rule_export" configuration:
      | filePath | %tmp%/rule_export/rule_export.yml |
    And I am logged in as "Peter"
    And I am on the "clothing_rule_export" export job page
    When I launch the export job
    And I wait for the "clothing_rule_export" job to finish
    Then exported file of "clothing_rule_export" should contain:
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
                    type: set_value
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
                    type: set_value
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
                    type: copy_value
                    from_field: name
                    to_field: name
                    from_locale: en_US
                    to_locale: fr_FR
    """
