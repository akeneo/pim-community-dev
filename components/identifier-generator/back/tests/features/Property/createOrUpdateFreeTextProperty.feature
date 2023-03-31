@acceptance-back
Feature: Create Identifier Generator

  Background:
    Given the 'sku' attribute of type 'pim_catalog_identifier'

  Scenario: Cannot create an identifier generator if structure contains empty free text
    When I try to create an identifier generator with free text ''
    Then I should get an error with message 'structure[0][string]: This value should not be blank.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator if structure contains empty free text
    Given I create an identifier generator
    When I try to update an identifier generator with free text ''
    Then I should get an error with message 'structure[0][string]: This value should not be blank.'

  Scenario: Cannot create an identifier generator if structure contains too long free text
    When I try to create an identifier generator with free text 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus placerat ante id dui ornare feugiat. Nulla egestas neque eu lectus interdum congue nec at diam. Phasellus ac magna lorem. Praesent non lectus sit amet lectus sollicitudin consectetur sed non.'
    Then I should get an error with message 'structure[0][string]: This value is too long. It should have 100 characters or less.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator if structure contains too long free text
    Given I create an identifier generator
    When I try to update an identifier generator with free text 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus placerat ante id dui ornare feugiat. Nulla egestas neque eu lectus interdum congue nec at diam. Phasellus ac magna lorem. Praesent non lectus sit amet lectus sollicitudin consectetur sed non.'
    Then I should get an error with message 'structure[0][string]: This value is too long. It should have 100 characters or less.'

  Scenario: Cannot create an identifier generator if structure contains free text missing required field
    When I try to create an identifier generator with free text without required field
    Then I should get an error with message 'structure[0]: Free text should contain "string" key'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator if structure contains free text missing required field
    Given I create an identifier generator
    When I try to update an identifier generator with free text without required field
    Then I should get an error with message 'structure[0]: Free text should contain "string" key'

  Scenario: Cannot create an identifier generator if structure contains free text with unknown field
    When I try to create an identifier generator with free text with unknown field
    Then I should get an error with message 'structure[0][unknown]: This field was not expected.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator if structure contains free text with unknown field
    Given I create an identifier generator
    When I try to update an identifier generator with free text with unknown field
    Then I should get an error with message 'structure[0][unknown]: This field was not expected.'
