@javascript
Feature: Execute rules from the user interface
  In order to run the rules
  As a product manager
  I need to be able to launch their execution from the "Settings/Rules" screen without permissions applied

  Background:
    Given a "clothing" catalog configuration
    And the following products:
      | sku        | categories | family  |
      | navy_blue  | jackets    | jackets |
      | light_blue | jeans      | pants   |
    And I am logged in as "Julia"
    And the following product rule definitions:
      """
      copy_description:
        conditions: []
        actions:
          - type:   set
            field:  description
            value:  Nice description
            locale: en_US
            scope:  mobile
      """

  Scenario: Successfully execute all rules from the user interface on all products
    Given the product rule "copy_description" is executed
    Then the product "navy_blue" should have the following values:
      | description-en_US-mobile | Nice description |
    Then the product "light_blue" should have the following values:
      | description-en_US-mobile | Nice description |
