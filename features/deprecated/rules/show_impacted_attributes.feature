@javascript
Feature: On a product edit/show display impacted attributes
  In order to know which attributes are affected or not
  As a regular user
  I need to see which attributes are affected by a rule or not

  Background:
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And I am logged in as "Julia"

  @deprecated
  Scenario: Successfully create, edit and save a product
    Given the following products:
      | sku       | family  |
      | my-loafer | sandals |
    And the following product rule definitions:
      """
      set_rule:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value:    my-loafer
        actions:
          - type:   set_value
            field:  name
            value:  My loafer
            locale: en_US
      """
    Then I am on the "my-loafer" product page
    And I should see that Name is a smart
