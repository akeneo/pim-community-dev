Feature: Execute concatenate rules
  In order to ease the enrichment of the catalog
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
    Given A rule that concatenates given attribute values to a text attribute value
    When I execute the concatenate rule on products
    Then no exception has been thrown
    And the en_US unscoped name of "75025" should be "Crown Bolt this is the "description" SKU75025 100 MEGAHERTZ 99 EUR 2015-01-01 01/01/2015 40"
    And the en_US unscoped name of "75024" should be "75024"

  @acceptance-back
  Scenario: Execute concatenate rule on products of a family to a textarea attribute value
    Given A rule that concatenates given attribute values to a textarea attribute value
    When I execute the concatenate rule on products
    Then no exception has been thrown
    And the en_US ecommerce description of "75025" should be "Crown Bolt 75025 SKU75025 100 MEGAHERTZ 99 EUR 2015-01-01 01/01/2015 40 this<br/>is<br/>the<br/>sub<br/>description"
    And the en_US ecommerce description of "75024" should be "this is the description"

  @acceptance-back
  Scenario: Execute concatenate rule on products of a family to a simple textarea attribute value
    Given A rule that concatenates rich textarea attribute value to a simple textarea attribute value
    When I execute the concatenate rule on products
    Then no exception has been thrown
    And the unlocalized unscoped sub_description of "75025" should be "Crown Bolt this is the|NL|"description""

  @acceptance-back
  Scenario: Execute concatenate rule on products of a family to a rich textarea attribute value
    Given A rule that concatenates simple textarea attribute value to a rich textarea attribute value
    When I execute the concatenate rule on products
    Then no exception has been thrown
    And the en_US ecommerce description of "75025" should be "Crown Bolt this<br/>is<br/>the<br/>sub<br/>description"
