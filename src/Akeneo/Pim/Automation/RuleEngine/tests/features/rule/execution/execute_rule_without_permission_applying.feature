Feature: Execute rules without permissions applied
  In order to run the rules
  As a product manager
  I need to be able to launch their execution without permissions applied

  Background:
    Given a "clothing" catalog configuration
    And the following products:
      | sku        | categories | family  |
      | navy_blue  | jackets    | jackets |
      | light_blue | jeans      | pants   |
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

  @integration-back
  Scenario: Successfully execute all rules from the user interface on all products
    Given the product rule "copy_description" is executed
    Then the en_US mobile description of "navy_blue" should be "Nice description"
    And the en_US mobile description of "light_blue" should be "Nice description"
