@acceptance-back
Feature: Create Identifier Generator

  Background:
    Given the 'sku' attribute of type 'pim_catalog_identifier'

  Scenario: Cannot create an identifier generator with autoNumber missing required field
    When I try to create an identifier generator with autoNumber without required field
    Then I should get an error with message 'structure[0]: "numberMin, digitsMin" fields are required for "auto_number" type'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with autoNumber missing required field
    Given I create an identifier generator
    When I try to update an identifier generator with autoNumber without required field
    Then I should get an error with message 'structure[0]: "numberMin, digitsMin" fields are required for "auto_number" type'

  Scenario Outline: Cannot create an identifier generator with wrong digits
    When I try to create an identifier generator with an auto number with '<minNumber>' as number min and '<minDigits>' as min digits
    Then I should get an error with message '<message>'
    And the identifier generator should not be created
    Examples:
      | minNumber | minDigits | message                                                                   |
      | -2        | 3         | structure[0][numberMin]: This value should be greater than or equal to 0. |
      | 4         | -2        | structure[0][digitsMin]: This value should be greater than or equal to 1. |
      | 4         | 22        |structure[0][digitsMin]: This value should be less than or equal to 15.    |

  Scenario Outline: Cannot update an identifier generator with wrong digits
    Given I create an identifier generator
    When I try to update an identifier generator with an auto number with '<minNumber>' as number min and '<minDigits>' as min digits
    Then I should get an error with message '<message>'
    Examples:
      | minNumber | minDigits | message                                                                   |
      | -2        | 3         | structure[0][numberMin]: This value should be greater than or equal to 0. |
      | 4         | -2        | structure[0][digitsMin]: This value should be greater than or equal to 1. |
      | 4         | 22        |structure[0][digitsMin]: This value should be less than or equal to 15.    |

  Scenario: Cannot create an identifier generator if structure contains multiple auto number
    When I try to create an identifier generator with multiple auto number in structure
    Then I should get an error with message 'structure: should contain only 1 auto number'
    And the identifier generator should not be created
