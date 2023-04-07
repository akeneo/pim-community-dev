@acceptance-back
Feature: Update Identifier Generator

  Background:
    Given the 'sku' attribute of type 'pim_catalog_identifier'
    And I create an identifier generator

  Scenario: Can update a valid identifier generator
    When I update the identifier generator
    Then I should not get any error
    And The identifier generator is updated in the repository

  # Code
  Scenario: Cannot update an unknown identifier generator
    When I try to update an unknown identifier generator
    Then I should get an error with message 'Identifier generator "unknown" does not exist or you do not have permission to access it.'

  # Target
  Scenario: Cannot update an identifier generator with blank target
    When I try to update an identifier generator with target ''
    Then I should get an error with message 'target: This value should not be blank.'

  Scenario: Cannot update an identifier with not existing target
    When I try to update an identifier generator with target 'toto'
    Then I should get an error with message 'target: The "toto" attribute does not exist.'

  Scenario: Cannot update an identifier with non identifier target
    Given the 'name' attribute of type 'pim_catalog_text'
    When I try to update an identifier generator with target 'name'
    Then I should get an error with message 'target: The "name" attribute code is "pim_catalog_text" type and should be of type "pim_catalog_identifier".'

  # Structure
  Scenario: Cannot update an identifier generator with blank structure
    When I try to update an identifier generator with blank structure
    Then I should get an error with message 'structure: This value should not be blank.'

  Scenario: Cannot update an identifier generator if property does not exist
    When I try to update an identifier generator with an unknown property
    Then I should get an error with message 'structure[0][type]: Type "unknown" can only be one of the following: "free_text", "auto_number"'

  Scenario: Cannot update an identifier generator if structure contains too many properties
    When I try to update an identifier generator with too many properties in structure
    Then I should get an error with message 'structure: This collection should contain 20 elements or less.'

  Scenario: Cannot update an identifier generator if structure contains multiple auto number
    When I try to update an identifier generator with multiple auto number in structure
    Then I should get an error with message 'structure: should contain only 1 auto number'

  # Conditions
  Scenario: Cannot update another condition type than defined ones
    When I try to update an identifier generator with unknown condition type
    Then I should get an error with message 'conditions[0][type]: Type "unknown" can only be one of the following: "enabled"'

  Scenario: Cannot update an identifier generator with too many conditions
    When I try to update an identifier generator with 11 conditions
    Then I should get an error with message 'conditions: This collection should contain 10 elements or less.'

  # Label
  Scenario: Can update an identifier generator without label
    When I update an identifier generator without label
    Then The identifier generator is updated without label in the repository
    And I should not get any error

  Scenario: Cannot update an identifier generator with label too long
    When I try to update an identifier generator with 'fr_FR' label 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec suscipit nisi erat, sed tincidunt urna finibus non. Nullam id lacus et augue ullamcorper euismod sed id nibh. Praesent luctus cursus finibus. Maecenas et euismod tellus. Nunc sed est nec mi consequat consequat sit amet ac ex. '
    Then I should get an error with message 'labels[fr_FR]: This value is too long. It should have 255 characters or less.'

  Scenario: Can update an identifier generator with empty label
    When I try to update an identifier generator with 'de_DE' label ''
    Then I should not get any error
    And there should be no 'de_DE' label for the 'generator_0' generator

  # Delimiter
  Scenario: Cannot update an identifier generator with an empty delimiter
    When I try to update an identifier generator with delimiter ''
    Then I should get an error with message 'delimiter: This value should not be blank.'

  Scenario: Can update a valid identifier generator with delimiter null
    When I update the identifier generator with delimiter null
    Then The identifier generator is updated in the repository and delimiter is null
    And I should not get any error

  Scenario: Cannot update an identifier generator with delimiter too long
    When I try to update an identifier generator with delimiter 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec suscipit nisi erat, sed tincidunt urna finibus non. Nullam id lacus et augue ullamcorper euismod sed id nibh. Praesent luctus cursus finibus. Maecenas et euismod tellus. Nunc sed est nec mi consequat consequat sit amet ac ex. '
    Then I should get an error with message 'delimiter: This value is too long. It should have 100 characters or less.'

  # Text transformation
  Scenario: Cannot update an identifier generator with unknown text transformation
    When I try to update an identifier generator with text transformation unknown
    Then I should get an error with message 'textTransformation: Text transformation "unknown" can only be one of the following: "no", "uppercase", "lowercase".'

  Scenario: Being able to update the text transformation property of an identifier generator
    When I update an identifier generator with text transformation lowercase
    Then The identifier generator is updated in the repository and text transformation is lowercase
    And I should not get any error

  Scenario: Being able to reorder identifier generators
    Given the 'ig_1' identifier generator
    And the 'ig_2' identifier generator
    When I reorder the identifier generators as 'ig_1', 'ig_2' and 'generator_0'
    Then the identifier generators should be ordered as 'ig_1', 'ig_2' and 'generator_0'
