@acceptance-back
Feature: Create Identifier Generator

  Background:
    Given the 'sku' attribute of type 'pim_catalog_identifier'
    And the 'name' attribute of type 'pim_catalog_text'
    And the 'color' attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color' attribute

  Scenario: Cannot create an identifier generator with simple select property without attribute code
    When I try to create an identifier generator with a simple select property without attribute code
    Then I should get an error with message 'structure[0]: "attributeCode" field is required for "simple_select" type.'
    And the identifier generator should not be created

  Scenario: Cannot create an identifier generator with simple select property without process field
    When I try to create an identifier generator with simple select property without process field
    Then I should get an error with message 'structure[0]: "process" field is required for "simple_select" type.'
    And the identifier generator should not be created

  Scenario: Cannot create an identifier generator with an unknown attribute in a simple select property
    When I try to create an identifier generator with a simple_select property with unknown attribute
    Then I should get an error with message 'structure[0][attributeCode]: The "unknown" attribute does not exist.'
    And the identifier generator should not be created

  Scenario: Can create an identifier generator with a simple select property and a truncate process
    When I try to create an identifier generator with a simple select process with type truncate and operator = and 1 as value
    Then I should not get any error
    And The identifier generator is saved in the repository

  Scenario: Cannot create an identifier generator with wrong attribute type in a simple select property
    When I try to create an identifier generator with a simple_select property with name attribute
    Then I should get an error with message 'structure[0][attributeCode]: The "name" attribute code is "pim_catalog_text" type and should be of type "pim_catalog_simpleselect".'
    And the identifier generator should not be created

  Scenario: Can create an identifier generator with localizable and scopable simple select
    Given the 'mobile' channel having 'en_US', 'fr_FR' as locales
    And the 'brand' localizable and scopable attribute of type 'pim_catalog_simpleselect'
    When I try to create an identifier generator with a simple_select property with brand attribute and mobile scope and en_US locale
    Then I should not get any error
    And The identifier generator is saved in the repository

  Scenario: Cannot update an identifier generator with simple select property without attribute code
    Given I create an identifier generator
    When I try to update an identifier generator with a simple select property without attribute code
    Then I should get an error with message 'structure[0]: "attributeCode" field is required for "simple_select" type.'

  Scenario: Cannot update an identifier generator with simple select property without process field
    Given I create an identifier generator
    When I try to update an identifier generator with simple select property without process field
    Then I should get an error with message 'structure[0]: "process" field is required for "simple_select" type.'

  Scenario: Cannot update an identifier generator with an unknown attribute in a simple select property
    Given I create an identifier generator
    When I try to update an identifier generator with a simple_select property with unknown attribute
    Then I should get an error with message 'structure[0][attributeCode]: The "unknown" attribute does not exist.'

  Scenario: Can update an identifier generator with a simple select property and a truncate process
    Given I create an identifier generator
    When I try to update an identifier generator with a simple select process with type truncate and operator = and 1 as value
    Then The identifier generator is updated in the repository
    And I should not get any error

  Scenario: Cannot update an identifier generator with wrong attribute type in a simple select property
    Given I create an identifier generator
    When I try to update an identifier generator with a simple_select property with name attribute
    Then I should get an error with message 'structure[0][attributeCode]: The "name" attribute code is "pim_catalog_text" type and should be of type "pim_catalog_simpleselect".'

  Scenario: Can update an identifier generator with localizable and scopable simple select
    Given I create an identifier generator
    And the 'mobile' channel having 'en_US', 'fr_FR' as locales
    And the 'brand' localizable and scopable attribute of type 'pim_catalog_simpleselect'
    When I try to update an identifier generator with a simple_select property with brand attribute and mobile scope and en_US locale
    Then I should not get any error
