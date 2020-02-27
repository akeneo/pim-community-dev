Feature: Execute concatenate rules
  In order ease the enrichment of the catalog
  As an administrator
  I can execute concatenate rules on my products

  Background:
    Given the following locales en_US
    And the following ecommerce channel with locales en_US
    And some currencies
    And the family "camcorders"
    And the product "75024" of the family "camcorders"
    And the product "75025" of the family "camcorders"
    And I have permission to execute rules

  @acceptance-back
  Scenario: Execute simple concatenate rule on product
    Given A rule with concatenate action with two text fields
    When I execute the concatenate rule on product "75024"
    Then no exception has been thrown
    And the product "75024" is successfully updated by the concatenate rule with two text fields

  @acceptance-back
  Scenario: Execute complex concatenate rule on product
    Given A rule with complex concatenate action
    When I execute the concatenate rule on product "75025"
    Then no exception has been thrown
    And the product "75025" is successfully updated by the complex concatenate rule
