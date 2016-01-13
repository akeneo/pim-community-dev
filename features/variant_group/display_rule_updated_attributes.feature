@javascript
Feature: Display warning message on attributes coming from rules on variant attribute edit
  In order to warn the user about rule attributes overriding
  As a product manager
  I need to be able to see attributes updated by rules on variant group edit

  Background:
    Given a "apparel" catalog configuration
    And the following variant group values:
      | group   | attribute    | value            | locale | scope     |
      | tshirts | manufacturer | american_apparel |        |           |
      | tshirts | name         | a                | en_US  |           |
      | tshirts | description  | e                | en_US  | ecommerce |
    And the following product rule definitions:
      """
      set_description:
        priority: 10
        conditions:
          - field:    name
            operator: =
            value:    My nice tshirt
            locale:   en_US
        actions:
          - type:   set_value
            field:  description
            value:  une belle description
            locale: fr_FR
            scope:  ecommerce
      """
    And I am logged in as "Julia"
    And I am on the "tshirts" variant group page
    And I visit the "Attributes" tab

  @unstable
  Scenario: Successfully see the rule icon
    Then I should see the Name and Description fields
    Then I should see the smart icon for the attribute "Description"
    And I display the tooltip for the "Description" rule icon
    Then I should see "set_description" in the popover
