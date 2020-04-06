Feature: Execute clear rules
  In order to ease the enrichment of the catalog
  As an administrator
  I can execute clear rules on my products

  Background:
    Given the following locales en_US,fr_FR
    And the following ecommerce channel with locales en_US,fr_FR
    And the family "camcorders"
    And the product "75024" of the family "camcorders"
    And the product "75025" of the family "camcorders"
    And I have permission to execute rules

  @acceptance-back
  Scenario: Successfully execute a clear rule on products
    Given Rules with following configuration:
    """
    rules:
        clear_rule:
            priority: 90
            conditions:
                - field: family
                  operator: IN
                  value:
                      - camcorders
            actions:
                - type: clear
                  field: name
                  locale: en_US
    """
    When I execute the "clear_rule" rule on products
    Then no exception has been thrown
    And the en_US unscoped name of "75024" should be ""
    And the en_US unscoped name of "75025" should be ""
    And the fr_FR unscoped name of "75025" should be "75025"
    And the unlocalized unscoped pim_brand of "75025" should be "Crown Bolt"

  @acceptance-back
  Scenario: Successfully execute clear rule on several attributes of products
    Given Rules with following configuration:
    """
    rules:
        clear_rule:
            priority: 90
            conditions:
                - field: family
                  operator: IN
                  value:
                      - camcorders
            actions:
                - type: clear
                  field: name
                  locale: en_US
                - type: clear
                  field: pim_brand
                - type: clear
                  field: processor
                - type: clear
                  field: price
                - type: clear
                  field: color
                - type: clear
                  field: release_date
                - type: clear
                  field: weight
                - type: clear
                  field: sub_description
                - type: clear
                  field: description
                  locale: en_US
                  scope: ecommerce
                - type: clear
                  field: connectivity
                - type: clear
                  field: designer
                - type: clear
                  field: designers
    """
    When I execute the "clear_rule" rule on products
    Then no exception has been thrown
    And the en_US unscoped name of "75024" should be ""
    And the en_US unscoped name of "75025" should be ""
    And the fr_FR unscoped name of "75025" should be "75025"
    And there should be no unlocalized unscoped processor value for the product "75025"
    And there should be no unlocalized unscoped price value for the product "75025"
    And there should be no unlocalized unscoped color value for the product "75025"
    And there should be no unlocalized unscoped release_date value for the product "75025"
    And there should be no unlocalized unscoped weight value for the product "75025"
    And there should be no unlocalized unscoped sub_description value for the product "75025"
    And there should be no en_US ecommerce description value for the product "75025"
    And there should be no unlocalized unscoped connectivity value for the product "75025"
    And there should be no unlocalized unscoped designer value for the product "75025"
    And there should be no unlocalized unscoped designers value for the product "75025"

  @acceptance-back
  Scenario: Successfully execute a clear rule on product categories
    Given the following categories:
      | code       | parent |
      | camera     |        |
      | camcorders | camera |
    And the product 75024 has category camera
    And the product 75025 has category camera
    And the product 75025 has category camcorders
    And Rules with following configuration:
    """
    rules:
        clear_categories_rule:
            priority: 90
            conditions:
                - field: family
                  operator: IN
                  value:
                      - camcorders
            actions:
                - type: clear
                  field: categories
    """
    When I execute the "clear_categories_rule" rule on products
    Then no exception has been thrown
    And the product 75024 should not have any category
    And the product 75025 should not have any category

  @acceptance-back
  Scenario: Successfully execute a clear rule on product groups
    Given the following product groups:
      | code       | label-en_US | type  |
      | CROSS_SELL | Cross Sell  | XSELL |
      | MUG        | MUG         | XSELL |
    And the product 75024 has group CROSS_SELL
    And the product 75025 has group CROSS_SELL
    And the product 75025 has group MUG
    And Rules with following configuration:
    """
    rules:
        clear_groups_rule:
            priority: 90
            conditions:
                - field: family
                  operator: IN
                  value:
                      - camcorders
            actions:
                - type: clear
                  field: groups
    """
    When I execute the "clear_groups_rule" rule on products
    Then no exception has been thrown
    And the product 75024 should not be in any group
    And the product 75025 should not be in any group

  @acceptance-back
  Scenario: Successfully execute a clear rule on product associations
    Given the product 75024 has XSELL association with product 75025
    And the product 75025 has PACK association with product 75024
    And Rules with following configuration:
    """
    rules:
        clear_associations_rule:
            priority: 90
            conditions:
                - field: family
                  operator: IN
                  value:
                      - camcorders
            actions:
                - type: clear
                  field: associations
    """
    When I execute the "clear_associations_rule" rule on products
    Then no exception has been thrown
    And the product 75024 should not have any association
    And the product 75025 should not have any association
