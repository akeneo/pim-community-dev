@acceptance-back
Feature: Update Family Nomenclature

  Background:
    Given a family nomenclature with the following values
      | familyCode | value |
      | family1    | Foo   |
      | family2    | Bar   |

  Scenario: Can add a new value in family nomenclature
    When I add the value Baz for family3
    Then The value for family1 should be Foo
    And The value for family2 should be Bar
    And The value for family3 should be Baz

  Scenario: Can update an existing value in family nomenclature
    When I update family2 value to Baz
    Then The value for family1 should be Foo
    And The value for family2 should be Baz

  Scenario: Can remove an existing value in family nomenclature
    When I remove the family1 value
    Then The value for family1 should be undefined
    And The value for family2 should be Bar

#  Scenario: Can update the nomenclature operator
#
#  Scenario: Can update the nomenclature value
#
#  Scenario: Can update the family nomenclature to generate if empty
#
#  Scenario: Can not update the nomenclature process if value is undefined
#
#  Scenario: Can not update the nomenclature process if operator is undefined




