Feature: Execute rules on read only attribute
  In order to update automatically a read only attribute
  As a product manager
  I need to execute rules that update a read only attribute

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku       | family  |
      | my-jacket | jackets |

  @integration-back
  Scenario: Successfully update a read only attribute through rule execution
    Given the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-jacket | name        | White jacket           | en_US  |        |
      | my-jacket | name        | Mocassin blanc         | fr_FR  |        |
      | my-jacket | description | A stylish white jacket | en_US  | mobile |
      | my-jacket | description | whatever               | en_US  | tablet |
    And the following product rule definitions:
      """
      set_description:
        priority: 10
        conditions:
          - field: sku
            operator: =
            value: my-jacket
        actions:
          - type: set
            field: description
            value: My jacket
            locale: en_US
            scope: tablet
      """
    And I set the description attribute in read only
    When the product rule "set_description" is executed
    Then the en_US tablet description of "my-jacket" should be "My jacket"
