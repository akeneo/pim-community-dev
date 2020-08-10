Feature: Execute rules with relative date conditions
  In order to update recent or old products
  As a product manager
  I need to execute rules with a filter relative to the current date

  Background:
    Given the "clothing" catalog configuration
    And the "very_old" product created at "1990-01-01 10:00:00"
    And the following product values:
      | product     | attribute    | value      | scope  |
      | very_old    | release_date | 1990-01-01 | mobile |
    And the following products:
      | sku       | family  |
      | my-jacket | jackets |


  @integration-back
  Scenario: Execute rules with products selected by a relative datetime
    Given the following product rule definitions:
      """
      disable_recent_products:
        priority: 10
        conditions:
          - field:    created
            operator: ">"
            value:    "-10 days"
        actions:
          - type:  set
            field: enabled
            value: false
      """
    When the product rule "disable_recent_products" is executed
    Then product "my-jacket" should be disabled
    But product "very_old" should be enabled

  @integration-back
  Scenario: Execute rules with products selected by a relative date
    Given the following product rule definitions:
      """
      disable_recent_products:
        priority: 10
        conditions:
          - field:    release_date
            operator: "<"
            value:    "now"
            scope: mobile
        actions:
          - type:  set
            field: enabled
            value: false
      """
    When the product rule "disable_recent_products" is executed
    And product "very_old" should be disabled
    Then product "my-jacket" should be enabled
