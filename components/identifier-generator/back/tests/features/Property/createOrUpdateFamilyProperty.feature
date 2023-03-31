@acceptance-back
Feature: Create Identifier Generator

  Background:
    Given the 'sku' attribute of type 'pim_catalog_identifier'
    And the 'tshirt' family

  Scenario Outline: Cannot create an identifier generator with a wrong configuration
    When I try to create an identifier generator with a family process with type <type> and operator <operator> and <value> as value
    Then I should get an error with message '<message>'
    And the identifier generator should not be created
    Examples:
    | type         | operator  | value       | message                                                                                                         |
    | unknown      | undefined | "undefined" | structure[0][process][type]: Type "unknown" can only be one of the following: "no", "truncate", "nomenclature". |
    | no           | =         | "undefined" | structure[0][process][operator]: This field was not expected.                                                   |
    | truncate     | undefined | 1           | structure[0][process][operator]: This field is missing.                                                         |
    | truncate     | =         | "undefined" | structure[0][process][value]: This field is missing.                                                            |
    | truncate     | ope       | 1           | structure[0][process][operator]: Operator "ope" can only be one of the following: "=", "<=".                    |
    | truncate     | =         | "bad"       | structure[0][process][value]: This value should be of type integer.                                             |
    | truncate     | =         | 0           | structure[0][process][value]: This value should be between 1 and 5.                                             |
    | nomenclature | =         | "undefined" | structure[0][process][operator]: This field was not expected.                                                   |

  Scenario Outline: Cannot update an identifier generator with wrong configuration
    Given I create an identifier generator
    When I try to update an identifier generator with a family process with type <type> and operator <operator> and <value> as value
    Then I should get an error with message '<message>'
    Examples:
    | type         | operator  | value       | message                                                                                                         |
    | unknown      | undefined | "undefined" | structure[0][process][type]: Type "unknown" can only be one of the following: "no", "truncate", "nomenclature". |
    | no           | =         | "undefined" | structure[0][process][operator]: This field was not expected.                                                   |
    | truncate     | undefined | 1           | structure[0][process][operator]: This field is missing.                                                         |
    | truncate     | =         | "undefined" | structure[0][process][value]: This field is missing.                                                            |
    | truncate     | ope       | 1           | structure[0][process][operator]: Operator "ope" can only be one of the following: "=", "<=".                    |
    | truncate     | =         | "bad"       | structure[0][process][value]: This value should be of type integer.                                             |
    | truncate     | =         | 0           | structure[0][process][value]: This value should be between 1 and 5.                                             |
    | nomenclature | =         | "undefined" | structure[0][process][operator]: This field was not expected.                                                   |

  Scenario Outline: Can create an identifier generator
    When I try to create an identifier generator with a family process with type <type> and operator <operator> and <value> as value
    Then The identifier generator is saved in the repository
    And I should not get any error
    Examples:
    | type         | operator  | value       |
    | no           | undefined | "undefined" |
    | truncate     | =         | 1           |
    | nomenclature | undefined | "undefined" |

  Scenario Outline: Can create an identifier generator
    Given I create an identifier generator
    When I try to update an identifier generator with a family process with type <type> and operator <operator> and <value> as value
    Then The identifier generator is saved in the repository
    And I should not get any error
    Examples:
    | type         | operator  | value       |
    | no           | undefined | "undefined" |
    | truncate     | =         | 1           |
    | nomenclature | undefined | "undefined" |

  Scenario: Cannot create an identifier generator with family property without required field
    When I try to create an identifier generator with family property without required field
    Then I should get an error with message 'structure[0]: "process" field is required for "family" type'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with family property without required field
    Given I create an identifier generator
    When I try to update an identifier generator with family property without required field
    Then I should get an error with message 'structure[0]: "process" field is required for "family" type'

  Scenario: Cannot create an identifier generator with invalid family property
    When I try to create an identifier generator with invalid family property
    Then I should get an error with message 'structure[0][unknown]: This field was not expected.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with invalid family property
    Given I create an identifier generator
    When I try to update an identifier generator with invalid family property
    Then I should get an error with message 'structure[0][unknown]: This field was not expected.'

  Scenario: Cannot create an identifier generator with empty family property
    When I try to create an identifier generator with empty family process property
    Then I should get an error with message 'structure[0][process][type]: This field is missing.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with empty family property
    Given I create an identifier generator
    When I try to update an identifier generator with empty family process property
    Then I should get an error with message 'structure[0][process][type]: This field is missing.'

  Scenario: Cannot create an identifier generator with a family containing invalid truncate process
    When I try to create an identifier generator with a family containing invalid truncate process
    Then I should get an error with message 'structure[0][process][unknown]: This field was not expected.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with a family containing invalid truncate process
    Given I create an identifier generator
    When I try to update an identifier generator with a family containing invalid truncate process
    Then I should get an error with message 'structure[0][process][unknown]: This field was not expected.'
