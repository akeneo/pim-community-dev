@acceptance-back
Feature: Create Identifier Generator

  Background:
    Given the sku identifier attribute
    And the name text attribute

  Scenario: Can create a valid identifier generator
    When I create an identifier generator
    Then The identifier generator is saved in the repository

  Scenario: Cannot create an identifier with not existing target
    When I try to create an identifier generator with not existing target 'toto'
    Then I should get an error with message 'toto'
    And the identifier should not be created

  Scenario: Cannot create an identifier with non identifier target
    When I try to create an identifier generator with target 'name'
    Then I should get an error with message 'name'
    And the identifier should not be created
