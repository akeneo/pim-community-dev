@acceptance-back
Feature: Create Family Nomenclature

  Scenario: Can create the nomenclature operator
    When I create the family nomenclature operator to <=, value to 5 and generation if empty
    Then I should not get any error
    Then the family nomenclature operator should be <=
    And the family nomenclature value should be 5
