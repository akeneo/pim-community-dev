Feature: Execute all rules on a set of products
  In order to ease the enrichment of the catalog
  As an administrator
  I can execute only enabled rules on my products

  Background:
    Given a "clothing" catalog configuration
    And the following products:
      | sku        | categories | family  |
      | navy_blue  | jackets    | jackets |
      | light_blue | jeans      | pants   |
    And the following product rule definitions:
      """
      set_US_description:
        enabled: true
        conditions: []
        actions:
          - type:   set
            field:  description
            value:  Nice description
            locale: en_US
            scope:  mobile
      set_FR_description:
        enabled: false
        conditions: []
        actions:
          - type:   set
            field:  description
            value:  Belle description
            locale: fr_FR
            scope:  mobile
      """

  @integration-back
  Scenario: The job rule execution executes enabled jobs only
    Given I launch the rule execution job
    Then the en_US mobile description of "navy_blue" should be "Nice description"
    And the fr_FR mobile description of "navy_blue" should be ""
    And the en_US mobile description of "light_blue" should be "Nice description"
    And the fr_FR mobile description of "light_blue" should be ""
