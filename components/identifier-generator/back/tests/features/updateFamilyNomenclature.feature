@acceptance-back
Feature: Update Family Nomenclature

  Background:
    Given a family nomenclature with the following values
      | familyCode | value |
      | family1    | Foo   |
      | family2    | Bar   |

  Scenario: Can add a new value in family nomenclature
    When I add Baz value for family3
    Then The value for family1 should be Foo
    And The value for family2 should be Bar
    And The value for family3 should be Baz

  Scenario: Can update an existing value in family nomenclature
    When I update Baz value for family2
    Then The value for family1 should be Foo
    And The value for family2 should be Baz

  Scenario: Can remove an existing value in family nomenclature
    When I remove the family1 value
    Then The value for family1 should be undefined
    And The value for family2 should be Bar

  Scenario: Can update an existing nomenclature operator
    When I update the family nomenclature operator to = and value to 3
    Then the family nomenclature operator should be =

  Scenario: Can update an existing nomenclature value
    When I update the family nomenclature operator to <= and value to 5
    Then the family nomenclature value should be 5

  Scenario: Cannot update the nomenclature value
    When I update the family nomenclature operator to <= and value to 6
    Then I should have an error 'value: This value should be less than or equal to 5.'

  Scenario: Cannot update the nomenclature operator
    When I update the family nomenclature operator to foo and value to 3
    Then I should have an error 'operator: The value you selected is not a valid choice.'
