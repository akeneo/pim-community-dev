@acceptance-back
Feature: Create Identifier Generator

  Background:
    Given the 'sku' attribute of type 'pim_catalog_identifier'
    And the 'tshirt' family
    And the 'name' attribute of type 'pim_catalog_text'
    And the 'color' attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color' attribute
    And the 'a_multi_select' attribute of type 'pim_catalog_multiselect'
    And the 'option_a', 'option_b' and 'option_c' options for 'a_multi_select' attribute
    And the 'tshirts', 'shoes' categories

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
    Then I should get an error with message 'structure[0][type]: Type "unknown" can only be one of the following: "free_text", "auto_number", "family", "simple_select".'
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

  # Conditions: Category
  Scenario: Cannot create an identifier generator with category condition and unknown operator
    When I try to create an identifier generator with a category condition and unknown operator
    Then I should get an error with message 'conditions[0][operator]: Operator "unknown" can only be one of the following: "IN", "NOT IN", "IN CHILDREN", "NOT IN CHILDREN", "CLASSIFIED", "UNCLASSIFIED".'
    And the identifier generator should not be created

  Scenario: Cannot create an identifier generator with category condition and CLASSIFIED operator and a value
    When I try to create an identifier generator with a category condition, CLASSIFIED operator and ["tshirts"] as value
    Then I should get an error with message 'conditions[0][value]: This field was not expected.'
    And the identifier generator should not be created

  Scenario: Cannot create an identifier generator with category condition and IN operator and a non array value
    When I try to create an identifier generator with a family condition, IN operator and "shirts" as value
    Then I should get an error with message 'conditions[0][value]: This value should be of type iterable.'
    And the identifier generator should not be created

  Scenario: Cannot create an identifier generator with category condition and IN operator and a non array of string value
    When I try to create an identifier generator with a category condition, IN operator and [true] as value
    Then I should get an error with message 'conditions[0][value][0]: This value should be of type string.'
    And the identifier generator should not be created

  Scenario: Cannot create an identifier generator with category condition and IN operator and no values
    When I try to create an identifier generator with a category condition, IN operator and [] as value
    Then I should get an error with message 'conditions[0][value]: This collection should contain 1 element or more.'
    And the identifier generator should not be created

  Scenario: Cannot create an identifier generator with category condition and IN operator and no value
    When I try to create an identifier generator with a category condition, IN operator and undefined as value
    Then I should get an error with message 'conditions[0][value]: This field is missing.'
    And the identifier generator should not be created

  Scenario: Cannot create an identifier generator with category condition and non existing families
    When I try to create an identifier generator with a category condition, IN operator and ["non_existing1", "non_existing_2"] as value
    Then I should get an error with message 'conditions[0][value]: The following categories have been deleted from your catalog: "non_existing1", "non_existing_2". You can remove them from your product selection.'
    And the identifier generator should not be created

  Scenario: Cannot create an identifier generator with category condition and non existing field
    When I try to create an identifier generator with a category condition and an unknown property
    Then I should get an error with message 'conditions[0][unknown]: This field was not expected.'
    And the identifier generator should not be created

  Scenario: Can create several category conditions
    When I try to create an identifier generator with 2 category conditions
    Then The identifier generator is saved in the repository
    And I should not get any error

  Scenario: Cannot create an identifier generator with category condition without operator
    When I try to create an identifier generator with a category condition and undefined operator
    Then I should get an error with message 'conditions[0][operator]: This field is missing.'
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
