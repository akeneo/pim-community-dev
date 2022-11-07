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

  Scenario: Cannot create an identifier generator with blank target
    When I try to create an identifier generator with blank target
    Then I should get an error with message 'target: This value should not be blank.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with blank structure
    When I try to create an identifier generator with blank structure
    Then I should get an error with message 'structure: This value should not be blank.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with blank code
    When I try to create an identifier generator with blank code
    Then I should get an error with message 'code: This value should not be blank.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator without label
    When I try to create an identifier generator without label
    Then I should get an error with message 'labels: This value should not be blank.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with code too long
    When I try to create an identifier generator with code 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec suscipit nisi erat, sed tincidunt urna finibus non. Nullam id lacus et augue ullamcorper euismod sed id nibh. Praesent luctus cursus finibus. Maecenas et euismod tellus. Nunc sed est nec mi consequat consequat sit amet ac ex. '
    Then I should get an error with message 'code: This value is too long. It should have 255 characters or less.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with code bad format
    When I try to create an identifier generator with code 'Lorem ipsum dolor sit amet'
    Then I should get an error with message 'code may contain only letters, numbers and underscore'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with label too long
    When I try to create an identifier generator with label 'fr' 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec suscipit nisi erat, sed tincidunt urna finibus non. Nullam id lacus et augue ullamcorper euismod sed id nibh. Praesent luctus cursus finibus. Maecenas et euismod tellus. Nunc sed est nec mi consequat consequat sit amet ac ex. '
    Then I should get an error with message 'labels[fr]: This value is too long. It should have 100 characters or less.'
    And the identifier should not be created
