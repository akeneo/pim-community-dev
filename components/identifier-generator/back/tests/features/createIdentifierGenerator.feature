@acceptance-back
Feature: Create Identifier Generator

  Background:
    Given the sku identifier attribute
    And the name text attribute

  Scenario: Can create a valid identifier generator
    When I create an identifier generator
    Then The identifier generator is saved in the repository
    And I should not get any error

  Scenario: Cannot create an identifier with not existing target
    When I try to create an identifier generator with not existing target 'toto'
    Then I should get an error with message 'target: The "toto" attribute code given as target does not exist'
    And the identifier should not be created

  Scenario: Cannot create an identifier with non identifier target
    When I try to create an identifier generator with target 'name'
    Then I should get an error with message 'target: The "name" attribute code is "pim_catalog_text" type and should be of type identifier'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator if the limit is reached
    Given the identifier generator is created
    When I try to create new identifier generator
    Then I should get an error with message 'Limit of "1" identifier generators is reached'

  Scenario: Cannot create an identifier generator if property does not exist
    When I try to create an identifier generator with an unknown property
    Then I should get an error with message 'structure[0][type]: Type "unknown" can only be one of the following: "free_text", "auto_number"'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with freeText too long
    When I try to create an identifier generator with freeText too long
    Then I should get an error with message 'structure[0][string]: This value is too long. It should have 100 characters or less.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with freeText too long
    When I try to create an identifier generator with freeText too long
    Then I should get an error with message 'structure[0][string]: This value is too long. It should have 100 characters or less.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with freeText empty
    When I try to create an identifier generator with freeText empty
    Then I should get an error with message 'structure[0][string]: This value should not be blank.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with freeText without string value
    When I try to create an identifier generator with freeText without string value
    Then I should get an error with message 'structure[0][string]: This value should not be blank.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with autoNumber number min negative
    When I try to create an identifier generator with autoNumber number min negative
    Then I should get an error with message 'structure[0][numberMin]: This value should be either positive or zero.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with autoNumber digits min negative
    When I try to create an identifier generator with autoNumber digits min negative
    Then I should get an error with message 'structure[0][digitsMin]: This value should be positive.'
    And the identifier should not be created


