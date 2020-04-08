Feature: Execute clear rules
  In order to ease the enrichment of the catalog
  As an administrator
  I can execute calculate rules on my products

  Background:
    Given the following locales en_US,fr_FR
    And the following ecommerce channel with locales en_US,fr_FR
    And the USD currency is added to the ecommerce channel
    And the family "camcorders"
    And the product "75024" of the family "camcorders"
    And the product "75025" of the family "camcorders"
    And I have permission to execute rules

  @acceptance-back
  Scenario: Successfully execute a calculate rule on products
    Given Rules with following configuration:
    """
    rules:
      calculate_rule:
        priority: 90
        conditions:
          - field: family
            operator: IN
            value:
              - camcorders
        actions:
          - type: calculate
            destination:
              field: item_weight
            source:
              field: weight
            operation_list:
              - operator: multiply
                value: 5
              - operator: divide
                field: in_stock
    """
    When I execute the "calculate_rule" rule on products
    Then no exception has been thrown
    And the unlocalized unscoped item_weight of "75024" should be "10.375"
    But the unlocalized unscoped item_weight of "75025" should be ""

  @acceptance-back
  Scenario: Successfully execute a calculate rule with price attributes
    Given Rules with following configuration:
    """
    rules:
      calculate_rule:
        priority: 90
        conditions:
          - field: family
            operator: IN
            value:
              - camcorders
        actions:
          - type: calculate
            destination:
              field: price
              currency: USD
            source:
              field: price
              currency: EUR
            operation_list:
              - operator: multiply
                value: 1.08
    """
    When I execute the "calculate_rule" rule on products
    Then no exception has been thrown
    And the unlocalized unscoped price of "75024" should be ""
    But the unlocalized unscoped price of "75025" should be "99.00 EUR, 106.92 USD"
