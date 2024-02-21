@acceptance-back
Feature: Update Family Nomenclature

  Background:
    Given a family nomenclature with the following values
      | familyCode | value |
      | family1    | Foo   |
      | family2    | Bar   |

  Scenario: Can add a new value in family nomenclature
    When I add Baz value for family3 family
    Then The value for family1 should be Foo
    And The value for family2 should be Bar
    And The value for family3 should be Baz

  Scenario: Can update an existing value in family nomenclature
    When I update Baz value for family2 family
    Then The value for family1 should be Foo
    And The value for family2 should be Baz

  Scenario: Can remove an existing value in family nomenclature
    When I remove the family1 value
    Then The value for family1 should be undefined
    And The value for family2 should be Bar

  Scenario: Can update an existing nomenclature operator
    When I update the family nomenclature operator to =, value to 3 and no generation if empty
    Then the family nomenclature operator should be =
    And the family nomenclature generation if empty should be false

  Scenario: Can update an existing nomenclature value
    When I update the family nomenclature operator to <=, value to 5 and no generation if empty
    Then the family nomenclature value should be 5

  Scenario: Can update an existing nomenclature generation if empty
    When I update the family nomenclature operator to <=, value to 3 and generation if empty
    Then the family nomenclature generation if empty should be true

  Scenario: Can update an existing nomenclature generation if empty
    When I update the family nomenclature operator to <=, value to 3 and no generation if empty
    Then the family nomenclature generation if empty should be false

  Scenario: Cannot update the nomenclature value
    When I update the family nomenclature operator to <=, value to 6 and no generation if empty
    Then I should get an error with message 'value: This value should be less than or equal to 5.'

  Scenario: Cannot update the nomenclature operator
    When I update the family nomenclature operator to foo, value to 3 and no generation if empty
    Then I should get an error with message 'operator: The value you selected is not a valid choice.'

  Scenario: Can update an existing value in family nomenclature ignoring family case
    When I update Qux value for FaMilY2 family
    Then The value for family1 should be Foo
    And The value for family2 should be Qux
