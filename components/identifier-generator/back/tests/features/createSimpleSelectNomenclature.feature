@acceptance-back
Feature: Create Simple Select Nomenclature
  Background:
    Given the 'color' attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color' attribute

  Scenario: Can create the simple select nomenclature
    When I create the simple select nomenclature of attribute color operator to <=, value to 5 and generation if empty
    Then I should not get any error
    Then the simple select nomenclature operator for color should be <=
    And the color simple select nomenclature value should be 5
