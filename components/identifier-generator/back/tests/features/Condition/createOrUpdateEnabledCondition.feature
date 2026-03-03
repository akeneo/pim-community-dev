@acceptance-back
Feature: Create Identifier Generator

  Background:
    Given the 'sku' attribute of type 'pim_catalog_identifier'

  Scenario: Cannot create an enabled condition without value
    When I try to create an identifier generator with an enabled condition and undefined as value
    Then I should get an error with message 'conditions[0]: Enabled should contain "value" key'
    And the identifier generator should not be created

  Scenario: Cannot update an enabled condition without value
    Given I create an identifier generator
    When I try to update an identifier generator with an enabled condition and undefined as value
    Then I should get an error with message 'conditions[0]: Enabled should contain "value" key'

  Scenario: Cannot create an enabled condition with a non boolean value
    When I try to create an identifier generator with an enabled condition and "true" as value
    Then I should get an error with message 'conditions[0].value: This value should be a boolean.'
    And the identifier generator should not be created

  Scenario: Cannot update an enabled condition with a non boolean value
    Given I create an identifier generator
    When I try to update an identifier generator with an enabled condition with "true" as value
    Then I should get an error with message 'conditions[0].value: This value should be a boolean.'

  Scenario: Cannot create an enabled condition with an unknown property
    When I try to create an identifier generator with an enabled condition and an unknown property
    Then I should get an error with message 'conditions[0][unknown]: This field was not expected.'
    And the identifier generator should not be created

  Scenario: Cannot update an enabled condition with an unknown property
    Given I create an identifier generator
    When I try to update an identifier generator with an enabled condition with an unknown property
    Then I should get an error with message 'conditions[0][unknown]: This field was not expected.'

  Scenario: Cannot create several enabled conditions
    When I try to create an identifier generator with 2 enabled conditions
    Then I should get an error with message 'conditions: should contain only 1 enabled'
    And the identifier generator should not be created

  Scenario: Cannot update several enabled conditions
    Given I create an identifier generator
    When I try to update an identifier generator with 2 enabled conditions
    Then I should get an error with message 'conditions: should contain only 1 enabled'
