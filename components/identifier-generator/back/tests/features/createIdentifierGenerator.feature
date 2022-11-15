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

  Scenario: Cannot create an identifier generator if structure contains more than 20 properties
    When I try to create an identifier generator with too many properties in structure
    Then I should get an error with message 'structure: This collection should contain 20 elements or less.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator if structure contains empty free text
    When I try to create an identifier generator with free text ''
    Then I should get an error with message 'structure[0][string]: This value should not be blank.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator if structure contains too long free text
    When I try to create an identifier generator with free text 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus placerat ante id dui ornare feugiat. Nulla egestas neque eu lectus interdum congue nec at diam. Phasellus ac magna lorem. Praesent non lectus sit amet lectus sollicitudin consectetur sed non.'
    Then I should get an error with message 'structure[0][string]: This value is too long. It should have 100 characters or less.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator if structure contains free text without required field
    When I try to create an identifier generator with free text without required field
    Then I should get an error with message 'structure[0]: The "string" field is required for "free_text" type'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator if structure contains free text with unknown field
    When I try to create an identifier generator with free text with unknown field
    Then I should get an error with message 'structure[0][unknown]: This field was not expected.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with autoNumber number min negative
    When I try to create an identifier generator with autoNumber number min negative
    Then I should get an error with message 'structure[0][numberMin]: This value should be either positive or zero.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with autoNumber without required field
    When I try to create an identifier generator with autoNumber without required field
    Then I should get an error with message 'structure[0]: "numberMin, digitsMin" fields are required for "auto_number" type'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with autoNumber digits min negative
    When I try to create an identifier generator with autoNumber digits min negative
    Then I should get an error with message 'structure[0][digitsMin]: This value should be positive.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with autoNumber digits min too big
    When I try to create an identifier generator with autoNumber digits min too big
    Then I should get an error with message 'structure[0][digitsMin]: This value should be less than 15.'
    And the identifier should not be created
