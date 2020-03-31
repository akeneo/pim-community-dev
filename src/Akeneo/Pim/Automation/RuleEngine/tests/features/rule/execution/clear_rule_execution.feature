Feature: Execute clear rules
  In order to ease the enrichment of the catalog
  As an administrator
  I can execute clear rules on my products

  Background:
    Given the following locales en_US,fr_FR
    And the following ecommerce channel with locales en_US,fr_FR
    And some currencies
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
