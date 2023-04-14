@acceptance-back
Feature: Create Identifier Generator

  Background:
    Given the 'sku' attribute of type 'pim_catalog_identifier'

  Scenario: Can create a valid identifier generator
    When I create an identifier generator
    Then I should not get any error
    And The identifier generator is saved in the repository

  # Class
  Scenario: Cannot create an identifier generator if the limit is reached
    When I create 20 identifier generators
    Then I should not get any error
    When I try to create an identifier generator with code 'another_generator'
    Then I should get an error with message 'Limit of "20" identifier generators is reached'

  Scenario: Cannot create an identifier generator with an existing code
    Given I create an identifier generator
    When I try to create an identifier generator with code 'generator_0'
    Then I should get an error with message 'This code is already used'

  # Target
  Scenario: Cannot create an identifier generator with blank target
    When I try to create an identifier generator with target ''
    Then I should get an error with message 'target: This value should not be blank.'
    And the identifier generator should not be created

  Scenario: Cannot create an identifier with not existing target
    When I try to create an identifier generator with target 'toto'
    Then I should get an error with message 'target: The "toto" attribute does not exist.'
    And the identifier generator should not be created

  Scenario: Cannot create an identifier with non identifier target
    Given the 'name' attribute of type 'pim_catalog_text'
    When I try to create an identifier generator with target 'name'
    Then I should get an error with message 'target: The "name" attribute code is "pim_catalog_text" type and should be of type "pim_catalog_identifier".'
    And the identifier generator should not be created

  # Structure
  Scenario: Cannot create an identifier generator with blank structure
    When I try to create an identifier generator with blank structure
    Then I should get an error with message 'structure: This value should not be blank.'
    And the identifier generator should not be created

  Scenario: Cannot create an identifier generator if property does not exist
    When I try to create an identifier generator with an unknown property
    Then I should get an error with message 'structure[0][type]: Type "unknown" can only be one of the following: "free_text", "auto_number", "family", "simple_select", "reference_entity".'
    And the identifier generator should not be created

  Scenario: Cannot create an identifier generator if structure contains too many properties
    When I try to create an identifier generator with 21 properties in structure
    Then I should get an error with message 'structure: This collection should contain 20 elements or less.'
    And the identifier generator should not be created

  # Conditions
  Scenario: Cannot create another condition type than defined ones
    When I try to create an identifier generator with unknown condition type
    Then I should get an error with message 'conditions[0][type]: Type "unknown" can only be one of the following: "enabled"'
    And the identifier generator should not be created

  Scenario: Cannot create an identifier generator with too many conditions
    When I try to create an identifier generator with 11 conditions
    Then I should get an error with message 'conditions: This collection should contain 10 elements or less.'
    And the identifier generator should not be created

  # Label
  Scenario: Can create an identifier generator without label
    When I create an identifier generator without label
    Then The identifier generator is saved in the repository
    And I should not get any error

  Scenario: Cannot create an identifier generator with label too long
    When I try to create an identifier generator with 'fr_FR' label 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec suscipit nisi erat, sed tincidunt urna finibus non. Nullam id lacus et augue ullamcorper euismod sed id nibh. Praesent luctus cursus finibus. Maecenas et euismod tellus. Nunc sed est nec mi consequat consequat sit amet ac ex. '
    Then I should get an error with message 'labels[fr_FR]: This value is too long. It should have 255 characters or less.'
    And the identifier generator should not be created

  Scenario: Can create an identifier generator with empty label
    When I try to create an identifier generator with 'de_DE' label ''
    Then The identifier generator is saved in the repository
    And I should not get any error
    And there should be no 'de_DE' label for the 'generator_0' generator

  # Delimiter
  Scenario: Cannot create an identifier generator with an empty delimiter
    When I try to create an identifier generator with delimiter ''
    Then I should get an error with message 'delimiter: This value should not be blank.'
    And the identifier generator should not be created

  Scenario: Can create an identifier generator with delimiter null
    When I create an identifier generator with delimiter null
    Then The identifier generator is saved in the repository
    And I should not get any error

  Scenario: Cannot create an identifier generator with delimiter too long
    When I try to create an identifier generator with delimiter 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec suscipit nisi erat, sed tincidunt urna finibus non. Nullam id lacus et augue ullamcorper euismod sed id nibh. Praesent luctus cursus finibus. Maecenas et euismod tellus. Nunc sed est nec mi consequat consequat sit amet ac ex. '
    Then I should get an error with message 'delimiter: This value is too long. It should have 100 characters or less.'
    And the identifier generator should not be created

  # Text transformation
  Scenario: Cannot create an identifier generator with unknown text transformation
    When I try to create an identifier generator with text transformation unknown
    Then I should get an error with message 'textTransformation: Text transformation "unknown" can only be one of the following: "no", "uppercase", "lowercase".'
    And the identifier generator should not be created

  # Code
  Scenario: Cannot create an identifier generator with blank code
    When I try to create an identifier generator with code ''
    Then I should get an error with message 'code: This value should not be blank.'
    And the identifier generator should not be created

  Scenario: Cannot create an identifier generator with code too long
    When I try to create an identifier generator with code 'Lorem_ipsum_dolor_sit_amet__consectetur_adipiscing_elit__Donec_suscipit_nisi_erat__sed_tincidunt_urna_finibus_non__Nullam_id_lacus_et_augue_ullamcorper_euismod_sed_id_nibh__Praesent_luctus_cursus_finibus__Maecenas_et_euismod_tellus__Nunc_sed_est_nec_mi_consequat_consequat_sit_amet_ac_ex__'
    Then I should get an error with message 'code: This value is too long. It should have 100 characters or less.'
    And the identifier generator should not be created

  Scenario: Cannot create an identifier generator with code bad format
    When I try to create an identifier generator with code 'Lorem ipsum dolor sit amet'
    Then I should get an error with message 'code: Code may contain only letters, numbers and underscore, "Lorem ipsum dolor sit amet" given.'
    And the identifier generator should not be created
