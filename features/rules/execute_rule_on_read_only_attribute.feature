@javascript
Feature: Display available field options
  In order to create a read only attribute
  As a product manager
  I need to see and manage the option 'Read only'

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku       | family  |
      | my-jacket | jackets |
    And I am logged in as "Julia"
    And I am on the attributes page

  Scenario: Successfully update a read only attribute through rule execution
    Given the following product values:
      | product   | attribute   | value                  | locale | scope  |
      | my-jacket | name        | White jacket           | en_US  |        |
      | my-jacket | name        | Mocassin blanc         | fr_FR  |        |
      | my-jacket | description | A stylish white jacket | en_US  | mobile |
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
    And I am on the "description" attribute page
    And I check the "Read only" switch
    And I save the "attribute"
    Given the product rule "set_description" is executed
    When I am on the "my-jacket" product page
    Then the english tablet description of "my-jacket" should be "My jacket"
