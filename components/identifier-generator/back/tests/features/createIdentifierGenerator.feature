@acceptance-back
Feature: Create Identifier Generator

  Scenario: Can create an identifier generator
    When I create an identifier generator
    Then The identifier generator is saved in the repository
