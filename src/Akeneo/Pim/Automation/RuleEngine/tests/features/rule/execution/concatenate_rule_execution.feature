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
    When I execute the concatenate rule on products
    Then no exception has been thrown
    And the product "75024" is successfully updated by the concatenate rule with two text fields

  @acceptance-back
  Scenario: Execute concatenate rule on products of a family to a text attribute value
    Given A rule with a condition on a family that concatenates given attribute values to a text attribute value
    When I execute the concatenate rule on products
    Then no exception has been thrown
    And the text attribute is successfully updated with the concatenation of the given attribute values

  @acceptance-back
  Scenario: Execute concatenate rule on products of a family to a textarea attribute value
    Given A rule with a condition on a family that concatenates given attribute values to a textarea attribute value
    When I execute the concatenate rule on products
    Then no exception has been thrown
    And the textarea attribute is successfully updated with the concatenation of the given attribute values
