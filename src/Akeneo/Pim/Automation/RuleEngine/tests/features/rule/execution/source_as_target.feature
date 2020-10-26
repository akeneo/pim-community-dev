Feature: Execute rule with linked actions
  In order to ease the enrichment of the catalog
  As a manager
  I can make rules having source a previous target

  Background:
    Given the following locales en_US,fr_FR
    And the following ecommerce channel with locales en_US,fr_FR
    And the family "camcorders"
    And the product "75024" of the family "camcorders"
    And I have permission to execute rules

  @acceptance-back
  Scenario: Successfully concatenate with a source as previously generated target
    Given Rules with following configuration:
      """
      rules:
        source_as_target_rule:
          conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
          actions:
            - type: concatenate
              from:
                - field: pim_brand
                - field: name
                  locale: en_US
              to:
                field: description
                locale: en_US
                scope: ecommerce
            - type: concatenate
              from:
                - field: pim_brand
                - text: " ; "
                - field: description
                  locale: en_US
                  scope: ecommerce
              to:
                field: sub_description
      """
    When I execute the "source_as_target_rule" rule on products
    Then no exception has been thrown
    And the en_US ecommerce description of "75024" should be "Crown Bolt 75024"
    And the unlocalized unscoped sub_description of "75024" should be "Crown Bolt ; Crown Bolt 75024"
