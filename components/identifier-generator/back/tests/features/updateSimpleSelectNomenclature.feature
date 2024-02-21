@acceptance-back
Feature: Update Simple Select Nomenclature

  Background:
    Given the 'color' attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color' attribute
    And a simple select nomenclature for color with the following values
      | attributeOptionCode | value |
      | red                 | Foo   |
      | blue                | Bar   |

  Scenario: Can add a new value in simple select nomenclature
    And I add Baz value for green option of the color simple select
    Then The value for option blue in color simple select should be Bar
    And The value for option red in color simple select should be Foo
    And The value for option green in color simple select should be Baz

  Scenario: Can update an existing value in simple select nomenclature
    When I update Toto value for blue option of the color simple select
    Then The value for option red in color simple select should be Foo
    And The value for option green in color simple select should not be defined
    And The value for option blue in color simple select should be Toto

  Scenario: Can remove an existing value in simple select nomenclature
    When I remove the red value from color simple select nomenclature
    Then The value for option blue in color simple select should be Bar
    And The value for option red in color simple select should not be defined

  Scenario: Can update an existing nomenclature operator
    When I update the simple select nomenclature of attribute color operator to =, value to 5 and no generation if empty
    Then the simple select nomenclature operator for color should be =

  Scenario: Can update an existing nomenclature value
    When I update the simple select nomenclature of attribute color operator to <=, value to 5 and no generation if empty
    Then the color simple select nomenclature value should be 5

  Scenario: Can update an existing nomenclature generation if empty
    When I update the simple select nomenclature of attribute color operator to <=, value to 3 and generation if empty
    Then the simple select color nomenclature generation if empty should be true

  Scenario: Can update an existing nomenclature no generation if empty
    When I update the simple select nomenclature of attribute color operator to <=, value to 3 and no generation if empty
    Then the simple select color nomenclature generation if empty should be false

  Scenario: Cannot update the nomenclature value
    When I update the simple select nomenclature of attribute color operator to <=, value to 6 and no generation if empty
    Then I should have a simple select nomenclature error 'value: This value should be less than or equal to 5.'

  Scenario: Cannot update the nomenclature operator
    When I update the simple select nomenclature of attribute color operator to foo, value to 3 and no generation if empty
    Then I should have a simple select nomenclature error 'operator: The value you selected is not a valid choice.'

  Scenario: Can update an existing nomenclature generation while ignoring case
    When I update the simple select nomenclature of attribute cOlOr operator to =, value to 4 and no generation if empty
    Then the simple select nomenclature operator for color should be =
    And the color simple select nomenclature value should be 4
