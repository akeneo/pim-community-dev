@acceptance-back @only-ee @reference-entity-feature-enabled
Feature: Update Identifier Generator

  Background:
    Given the 'sku' attribute of type 'pim_catalog_identifier'
    And the 'brand' attribute of type 'akeneo_reference_entity'

  Scenario: Cannot update an identifier generator with reference entity property without attribute code
    When I try to update an identifier generator with a reference entity property without attribute code
    Then I should get an error with message 'structure[0]: "attributeCode" field is required for "reference_entity" type.'

  Scenario: Cannot update an identifier generator with reference entity property without process field
    When I try to update an identifier generator with reference entity property without process field
    Then I should get an error with message 'structure[0]: "process" field is required for "reference_entity" type.'

  Scenario: Cannot update an identifier generator with an unknown attribute in a reference entity property
    Given I create an identifier generator
    When I try to update an identifier generator with a reference_entity property with unknown attribute
    Then I should get an error with message 'structure[0][attributeCode]: The "unknown" attribute does not exist.'

  Scenario: Can update an identifier generator with a reference entity property and a truncate process
    Given I create an identifier generator
    When I try to update an identifier generator with a reference entity process with type truncate and operator = and 1 as value
    Then The identifier generator is updated in the repository
    And I should not get any error
