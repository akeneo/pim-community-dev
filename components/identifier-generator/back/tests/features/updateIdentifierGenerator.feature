@acceptance-back
Feature: Update Identifier Generator

  Background:
    Given the 'sku' attribute of type 'pim_catalog_identifier'
    And the 'name' attribute of type 'pim_catalog_text'
    And the 'default' identifier generator
    And the 'color' attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color' attribute

  Scenario: Can update a valid identifier generator
    When I update the identifier generator
    Then I should not get any update error
    And The identifier generator is updated in the repository

  # Code
  Scenario: Cannot update an unknown identifier generator
    When I try to update an unknown identifier generator
    Then I should get an error on update with message 'Identifier generator "unknown" does not exist or you do not have permission to access it.'

  # Target
  Scenario: Cannot update an identifier generator with blank target
    When I try to update an identifier generator with target ''
    Then I should get an error on update with message 'target: This value should not be blank.'

  Scenario: Cannot update an identifier with not existing target
    When I try to update an identifier generator with target 'toto'
    Then I should get an error on update with message 'target: The "toto" attribute does not exist.'

  Scenario: Cannot update an identifier with non identifier target
    When I try to update an identifier generator with target 'name'
    Then I should get an error on update with message 'target: The "name" attribute code is "pim_catalog_text" type and should be of type "pim_catalog_identifier".'

  # Structure
  Scenario: Cannot update an identifier generator with blank structure
    When I try to update an identifier generator with blank structure
    Then I should get an error on update with message 'structure: This value should not be blank.'

  Scenario: Cannot update an identifier generator if property does not exist
    When I try to update an identifier generator with an unknown property
    Then I should get an error on update with message 'structure[0][type]: Type "unknown" can only be one of the following: "free_text", "auto_number"'

  Scenario: Cannot update an identifier generator if structure contains too many properties
    When I try to update an identifier generator with too many properties in structure
    Then I should get an error on update with message 'structure: This collection should contain 20 elements or less.'

  Scenario: Cannot update an identifier generator if structure contains multiple auto number
    When I try to update an identifier generator with multiple auto number in structure
    Then I should get an error on update with message 'structure: should contain only 1 auto number'

  # Structure : Free text
  Scenario: Cannot update an identifier generator if structure contains empty free text
    When I try to update an identifier generator with free text ''
    Then I should get an error on update with message 'structure[0][string]: This value should not be blank.'

  Scenario: Cannot update an identifier generator if structure contains too long free text
    When I try to update an identifier generator with free text 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus placerat ante id dui ornare feugiat. Nulla egestas neque eu lectus interdum congue nec at diam. Phasellus ac magna lorem. Praesent non lectus sit amet lectus sollicitudin consectetur sed non.'
    Then I should get an error on update with message 'structure[0][string]: This value is too long. It should have 100 characters or less.'

  Scenario: Cannot update an identifier generator if structure contains free text missing required field
    When I try to update an identifier generator with free text without required field
    Then I should get an error on update with message 'structure[0]: Free text should contain "string" key'

  Scenario: Cannot update an identifier generator if structure contains free text with unknown field
    When I try to update an identifier generator with free text with unknown field
    Then I should get an error on update with message 'structure[0][unknown]: This field was not expected.'

  # Structure : Auto number
  Scenario: Cannot update an identifier generator with autoNumber missing required field
    When I try to update an identifier generator with autoNumber without required field
    Then I should get an error on update with message 'structure[0]: "numberMin, digitsMin" fields are required for "auto_number" type'

  Scenario: Cannot update an identifier generator with autoNumber number min negative
    When I try to update an identifier generator with an auto number with '-2' as number min and '3' as min digits
    Then I should get an error on update with message 'structure[0][numberMin]: This value should be greater than or equal to 0.'

  Scenario: Cannot update an identifier generator with autoNumber digits min negative
    When I try to update an identifier generator with an auto number with '4' as number min and '-2' as min digits
    Then I should get an error on update with message 'structure[0][digitsMin]: This value should be greater than or equal to 1.'

  Scenario: Cannot update an identifier generator with autoNumber digits min too big
    When I try to update an identifier generator with an auto number with '4' as number min and '22' as min digits
    Then I should get an error on update with message 'structure[0][digitsMin]: This value should be less than or equal to 15.'

  # Structure : Family
  Scenario: Cannot update an identifier generator with family property without required field
    When I try to update an identifier generator with family property without required field
    Then I should get an error on update with message 'structure[0]: "process" field is required for "family" type'

  Scenario: Cannot update an identifier generator with invalid family property
    When I try to update an identifier generator with invalid family property
    Then I should get an error on update with message 'structure[0][unknown]: This field was not expected.'

  Scenario: Cannot update an identifier generator with empty family property
    When I try to update an identifier generator with empty family process property
    Then I should get an error on update with message 'structure[0][process][type]: This field is missing.'

  Scenario: Cannot update an identifier generator with family process type unknown
    When I try to update an identifier generator with a family process with type unknown and operator undefined and undefined as value
    Then I should get an error on update with message 'structure[0][process][type]: Type "unknown" can only be one of the following: "no", "truncate".'

  Scenario: Cannot update an identifier generator with family process type no and operator EQUALS and undefined as value
    When I try to update an identifier generator with a family process with type no and operator EQUALS and undefined as value
    Then I should get an error on update with message 'structure[0][operator]: This field was not expected.'

  Scenario: Cannot update an identifier generator with a family containing invalid truncate process
    When I try to update an identifier generator with a family containing invalid truncate process
    Then I should get an error on update with message 'structure[0][process][unknown]: This field was not expected.'

  Scenario: Cannot update an identifier generator with a family containing truncate process missing fields
    When I try to update an identifier generator with a family process with type truncate and operator undefined and "undefined" as value
    Then I should get an error on update with message 'structure[0][operator]: This field is missing.'
    Then I should get an error on update with message 'structure[0][value]: This field is missing.'

  Scenario: Cannot update an identifier generator with a family containing truncate process and unknown operator
    When I try to update an identifier generator with a family process with type truncate and operator ope and 1 as value
    Then I should get an error on update with message 'structure[0][operator]: Operator "ope" can only be one of the following: "EQUALS", "LOWER_OR_EQUAL_THAN".'

  Scenario: Cannot update an identifier generator with a family containing truncate process and bad value type
    When I try to update an identifier generator with a family process with type truncate and operator EQUALS and "bad" as value
    Then I should get an error on update with message 'structure[0][value]: This value should be of type integer.'

  Scenario: Cannot update an identifier generator with a family containing truncate process and value not in range
    When I try to update an identifier generator with a family process with type truncate and operator EQUALS and 0 as value
    Then I should get an error on update with message 'structure[0][value]: This value should be between 1 and 5.'

  Scenario: Can update an identifier generator with a family property and no process
    When I try to update an identifier generator with a family process with type no and operator undefined and "undefined" as value
    Then The identifier generator is updated in the repository
    And I should not get any update error

  Scenario: Can update an identifier generator with a family property and a truncate process
    When I try to update an identifier generator with a family process with type truncate and operator EQUALS and 1 as value
    Then The identifier generator is updated in the repository
    And I should not get any update error

  # Conditions
  Scenario: Cannot update another condition type than defined ones
    When I try to update an identifier generator with unknown condition type
    Then I should get an error on update with message 'conditions[0][type]: Type "unknown" can only be one of the following: "enabled"'

  # Conditions: Enabled
  Scenario: Cannot update an enabled condition without value
    When I try to update an identifier generator with enabled condition without value
    Then I should get an error on update with message 'conditions[0]: Enabled should contain "value" key'

  Scenario: Cannot update an enabled condition with a non boolean value
    When I try to update an identifier generator with enabled condition with string value
    Then I should get an error on update with message 'conditions[0].value: This value should be a boolean.'

  Scenario: Cannot update an enabled condition with an unknown property
    When I try to update an identifier generator with enabled condition with an unknown property
    Then I should get an error on update with message 'conditions[0][unknown]: This field was not expected.'

  Scenario: Cannot update several enabled conditions
    When I try to update an identifier generator with 2 enabled conditions
    Then I should get an error on update with message 'conditions: should contain only 1 enabled'

  # Conditions: Family
  Scenario: Cannot update an identifier generator with unknown operator
    When I try to update an identifier generator with a family condition with an unknown operator
    Then I should get an error on update with message 'conditions[0][operator]: Operator "unknown" can only be one of the following: "IN", "NOT IN", "EMPTY", "NOT EMPTY".'

  Scenario: Cannot update an identifier generator with operator EMPTY and a value
    When I try to update an identifier generator with a family condition with operator EMPTY and ["shirts"] as value
    Then I should get an error on update with message 'conditions[0][value]: This field was not expected.'

  Scenario: Cannot update an identifier generator with operator IN and a non array value
    When I try to update an identifier generator with a family condition with operator IN and "shirts" as value
    Then I should get an error on update with message 'conditions[0][value]: This value should be of type iterable.'

  Scenario: Cannot update an identifier generator with operator IN and a non array of string value
    When I try to update an identifier generator with a family condition with operator IN and [true] as value
    Then I should get an error on update with message 'conditions[0][value][0]: This value should be of type string.'

  Scenario: Cannot update an identifier generator with operator IN and no values
    When I try to update an identifier generator with a family condition with operator IN and [] as value
    Then I should get an error on update with message 'conditions[0][value]: This collection should contain 1 element or more.'

  Scenario: Cannot update an identifier generator with operator IN and no value
    When I try to update an identifier generator with a family condition with operator IN and undefined as value
    Then I should get an error on update with message 'conditions[0][value]: This field is missing.'

  Scenario: Cannot update an identifier generator with non existing family
    When I try to update an identifier generator with a family condition with operator IN and ["non_existing1", "non_existing_2"] as value
    Then I should get an error on update with message 'conditions[0][value]: The following families have been deleted from your catalog: "non_existing1", "non_existing_2". You can remove them from your product selection.'

  Scenario: Cannot update an identifier generator with non existing field
    When I try to update an identifier generator with a family condition with unknown property
    Then I should get an error on update with message 'conditions[0][unknown]: This field was not expected.'

  Scenario: Cannot update several family conditions
    When I try to update an identifier generator with 2 family conditions
    Then I should get an error on update with message 'conditions: should contain only 1 family'

  Scenario: Cannot update an identifier generator without operator
    When I try to update an identifier generator with a family without operator
    Then I should get an error on update with message 'conditions[0][operator]: This field is missing.'

  # Conditions: Simple Select
  Scenario: Cannot update an identifier generator with unknown values
    When I try to update an identifier generator with simple_select condition with an unknown property
    Then I should get an error on update with message 'conditions[0][unknown]: This field was not expected.'

  Scenario: Cannot update an identifier generator with operator IN and no value
    When I try to update an identifier generator with a simple_select condition with operator IN and undefined as value
    Then I should get an error on update with message 'conditions[0][value]: This field is missing.'

  Scenario: Cannot update an identifier generator with operator IN and no values
    When I try to update an identifier generator with a simple_select condition with operator IN and [] as value
    Then I should get an error on update with message 'conditions[0][value]: This collection should contain 1 element or more.'

  Scenario: Cannot update an identifier generator with operator IN and a non array value
    When I try to update an identifier generator with a simple_select condition with operator IN and "green" as value
    Then I should get an error on update with message 'conditions[0][value]: This value should be of type iterable.'

  Scenario: Cannot update an identifier generator with operator IN and a non array of string value
    When I try to update an identifier generator with a simple_select condition with operator IN and [true] as value
    Then I should get an error on update with message 'conditions[0][value][0]: This value should be of type string.'

  Scenario: Cannot update an identifier generator with unknown options
    When I try to update an identifier generator with a simple_select condition with operator IN and ["unknown1", "green", "unknown2"] as value
    Then I should get an error on update with message 'conditions[0][value]: The following attribute options do not exist for the attribute "color": "unknown1", "unknown2".'

  Scenario: Cannot update an identifier generator with operator EMPTY and a value
    When I try to update an identifier generator with a simple_select condition with operator EMPTY and ["green"] as value
    Then I should get an error on update with message 'conditions[0][value]: This field was not expected.'

  Scenario: Cannot update an identifier generator with operator EMPTY and a value
    When I try to update an identifier generator with a simple_select condition with unknown attribute
    Then I should get an error on update with message 'conditions[0][attributeCode]: The "unknown" attribute does not exist.'

  Scenario: Cannot update an identifier generator with wrong attribute type
    When I try to update an identifier generator with a simple_select condition with name attribute
    Then I should get an error on update with message 'conditions[0][attributeCode]: The "name" attribute code is "pim_catalog_text" type and should be of type "pim_catalog_simpleselect".'

  Scenario: Cannot update an identifier generator without scope
    Given the 'color_scopable' scopable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_scopable' attribute
    When I try to update an identifier generator with a simple_select condition with color_scopable attribute and undefined scope
    Then I should get an error on update with message 'conditions[0][scope]: A channel is required for the "color_scopable" attribute.'

  Scenario: Cannot update an identifier generator with scope
    When I try to update an identifier generator with a simple_select condition with color attribute and ecommerce scope
    Then I should get an error on update with message 'conditions[0][scope]: This field was not expected.'

  Scenario: Cannot update an identifier generator without locale
    Given the 'color_localizable' localizable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_localizable' attribute
    When I try to update an identifier generator with a simple_select condition with color_localizable attribute and undefined locale
    Then I should get an error on update with message 'conditions[0][locale]: A locale is required for the "color_localizable" attribute.'

  Scenario: Cannot update an identifier generator with locale
    When I try to update an identifier generator with a simple_select condition with color attribute and en_US locale
    Then I should get an error on update with message 'conditions[0][locale]: This field was not expected.'

  Scenario: Cannot update an identifier generator with undefined scope
    Given the 'color_scopable' scopable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_scopable' attribute
    When I try to update an identifier generator with a simple_select condition with color_scopable attribute and unknown scope
    Then I should get an error on update with message 'conditions[0][scope]: The "unknown" scope does not exist.'

  Scenario: Cannot update an identifier generator with undefined locale
    Given the 'color_localizable' localizable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_localizable' attribute
    When I try to update an identifier generator with a simple_select condition with color_localizable attribute and unknown locale
    Then I should get an error on update with message 'conditions[0][locale]: The "unknown" locale does not exist or is not activated.'

  Scenario: Cannot update an identifier generator with non activated locale
    Given the 'color_localizable_and_scopable' localizable and scopable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_localizable_and_scopable' attribute
    And the 'website' channel having 'en_US' as locale
    And the 'ecommerce' channel having 'de_DE' as locale
    When I try to update an identifier generator with a simple_select condition with color_localizable_and_scopable attribute and ecommerce scope and en_US locale
    Then I should get an error on update with message 'conditions[0][locale]: The "en_US" locale is not active for the "ecommerce" channel.'

  # Label
  Scenario: Can update an identifier generator without label
    When I update an identifier generator without label
    Then The identifier generator is updated without label in the repository
    And I should not get any update error

  Scenario: Cannot update an identifier generator with label too long
    When I try to update an identifier generator with 'fr_FR' label 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec suscipit nisi erat, sed tincidunt urna finibus non. Nullam id lacus et augue ullamcorper euismod sed id nibh. Praesent luctus cursus finibus. Maecenas et euismod tellus. Nunc sed est nec mi consequat consequat sit amet ac ex. '
    Then I should get an error on update with message 'labels[fr_FR]: This value is too long. It should have 255 characters or less.'

  # Delimiter
  Scenario: Cannot update an identifier generator with an empty delimiter
    When I try to update an identifier generator with delimiter ''
    Then I should get an error on update with message 'delimiter: This value should not be blank.'

  Scenario: Can update a valid identifier generator with delimiter null
    When I update the identifier generator with delimiter null
    Then The identifier generator is updated in the repository and delimiter is null
    And I should not get any error

  Scenario: Cannot update an identifier generator with delimiter too long
    When I try to update an identifier generator with delimiter 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec suscipit nisi erat, sed tincidunt urna finibus non. Nullam id lacus et augue ullamcorper euismod sed id nibh. Praesent luctus cursus finibus. Maecenas et euismod tellus. Nunc sed est nec mi consequat consequat sit amet ac ex. '
    Then I should get an error on update with message 'delimiter: This value is too long. It should have 100 characters or less.'
