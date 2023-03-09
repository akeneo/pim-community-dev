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

  # Target
  Scenario: Cannot create an identifier generator with blank target
    When I try to create an identifier generator with target ''
    Then I should get an error with message 'target: This value should not be blank.'
    And the identifier should not be created

  Scenario: Cannot create an identifier with not existing target
    When I try to create an identifier generator with target 'toto'
    Then I should get an error with message 'target: The "toto" attribute does not exist.'
    And the identifier should not be created

  Scenario: Cannot create an identifier with non identifier target
    When I try to create an identifier generator with target 'name'
    Then I should get an error with message 'target: The "name" attribute code is "pim_catalog_text" type and should be of type "pim_catalog_identifier".'
    And the identifier should not be created

  # Structure
  Scenario: Cannot create an identifier generator with blank structure
    When I try to create an identifier generator with blank structure
    Then I should get an error with message 'structure: This value should not be blank.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator if property does not exist
    When I try to create an identifier generator with an unknown property
    Then I should get an error with message 'structure[0][type]: Type "unknown" can only be one of the following: "free_text", "auto_number", "family".'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator if structure contains too many properties
    When I try to create an identifier generator with 21 properties in structure
    Then I should get an error with message 'structure: This collection should contain 20 elements or less.'
    And the identifier should not be created

  # Structure : Free text
  Scenario: Cannot create an identifier generator if structure contains empty free text
    When I try to create an identifier generator with free text ''
    Then I should get an error with message 'structure[0][string]: This value should not be blank.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator if structure contains too long free text
    When I try to create an identifier generator with free text 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus placerat ante id dui ornare feugiat. Nulla egestas neque eu lectus interdum congue nec at diam. Phasellus ac magna lorem. Praesent non lectus sit amet lectus sollicitudin consectetur sed non.'
    Then I should get an error with message 'structure[0][string]: This value is too long. It should have 100 characters or less.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator if structure contains free text missing required field
    When I try to create an identifier generator with free text without required field
    Then I should get an error with message 'structure[0]: Free text should contain "string" key'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator if structure contains free text with unknown field
    When I try to create an identifier generator with free text with unknown field
    Then I should get an error with message 'structure[0][unknown]: This field was not expected.'
    And the identifier should not be created

  # Structure : Auto number
  Scenario: Cannot create an identifier generator with autoNumber missing required field
    When I try to create an identifier generator with autoNumber without required field
    Then I should get an error with message 'structure[0]: "numberMin, digitsMin" fields are required for "auto_number" type'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with autoNumber number min negative
    When I try to create an identifier generator with an auto number with '-2' as number min and '3' as min digits
    Then I should get an error with message 'structure[0][numberMin]: This value should be greater than or equal to 0.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with autoNumber digits min negative
    When I try to create an identifier generator with an auto number with '4' as number min and '-2' as min digits
    Then I should get an error with message 'structure[0][digitsMin]: This value should be greater than or equal to 1.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with autoNumber digits min too big
    When I try to create an identifier generator with an auto number with '4' as number min and '22' as min digits
    Then I should get an error with message 'structure[0][digitsMin]: This value should be less than or equal to 15.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator if structure contains multiple auto number
    When I try to create an identifier generator with multiple auto number in structure
    Then I should get an error with message 'structure: should contain only 1 auto number'
    And the identifier should not be created

  # Structure : Family
  Scenario: Cannot create an identifier generator with family property without required field
    When I try to create an identifier generator with family property without required field
    Then I should get an error with message 'structure[0]: "process" field is required for "family" type'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with invalid family property
    When I try to create an identifier generator with invalid family property
    Then I should get an error with message 'structure[0][unknown]: This field was not expected.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with empty family property
    When I try to create an identifier generator with empty family process property
    Then I should get an error with message 'structure[0][process][type]: This field is missing.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with family process type unknown
    When I try to create an identifier generator with a family process with type unknown and operator undefined and undefined as value
    Then I should get an error with message 'structure[0][process][type]: Type "unknown" can only be one of the following: "no", "truncate", "nomenclature".'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with family process type no and operator = and undefined as value
    When I try to create an identifier generator with a family process with type no and operator = and undefined as value
    Then I should get an error with message 'structure[0][operator]: This field was not expected.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with a family containing invalid truncate process
    When I try to create an identifier generator with a family containing invalid truncate process
    Then I should get an error with message 'structure[0][process][unknown]: This field was not expected.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with a family containing truncate process missing fields
    When I try to create an identifier generator with a family process with type truncate and operator undefined and "undefined" as value
    Then I should get an error with message 'structure[0][operator]: This field is missing.'
    Then I should get an error with message 'structure[0][value]: This field is missing.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with a family containing truncate process and unknown operator
    When I try to create an identifier generator with a family process with type truncate and operator ope and 1 as value
    Then I should get an error with message 'structure[0][operator]: Operator "ope" can only be one of the following: "=", "<=".'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with a family containing truncate process and bad value type
    When I try to create an identifier generator with a family process with type truncate and operator = and "bad" as value
    Then I should get an error with message 'structure[0][value]: This value should be of type integer.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with a family containing truncate process and value not in range
    When I try to create an identifier generator with a family process with type truncate and operator = and 0 as value
    Then I should get an error with message 'structure[0][value]: This value should be between 1 and 5.'
    And the identifier should not be created

  Scenario: Can create an identifier generator with a family property and no process
    When I try to create an identifier generator with a family process with type no and operator undefined and "undefined" as value
    Then The identifier generator is saved in the repository
    And I should not get any error

  Scenario: Can create an identifier generator with a family property and a truncate process
    When I try to create an identifier generator with a family process with type truncate and operator = and 1 as value
    Then I should not get any error
    Then The identifier generator is saved in the repository
    And I should not get any error

  Scenario: Can create an identifier generator with a family property and a nomenclature process
    When I try to create an identifier generator with a family process with type nomenclature and operator undefined and "undefined" as value
    Then I should not get any error
    And The identifier generator is saved in the repository

  Scenario: Cannot create an identifier generator with family process type nomenclature and operator = and undefined as value
    When I try to create an identifier generator with a family process with type nomenclature and operator = and undefined as value
    Then I should get an error with message 'structure[0][operator]: This field was not expected.'
    And the identifier should not be created

  # Conditions
  Scenario: Cannot create another condition type than defined ones
    When I try to create an identifier generator with unknown condition type
    Then I should get an error with message 'conditions[0][type]: Type "unknown" can only be one of the following: "enabled"'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with too many conditions
    When I try to create an identifier generator with 11 conditions
    Then I should get an error with message 'conditions: This collection should contain 10 elements or less.'
    And the identifier should not be created

  # Conditions: enabled
  Scenario: Cannot create an enabled condition without value
    When I try to create an identifier generator with an enabled condition and undefined as value
    Then I should get an error with message 'conditions[0]: Enabled should contain "value" key'
    And the identifier should not be created

  Scenario: Cannot create an enabled condition with a non boolean value
    When I try to create an identifier generator with an enabled condition and "true" as value
    Then I should get an error with message 'conditions[0].value: This value should be a boolean.'
    And the identifier should not be created

  Scenario: Cannot create an enabled condition with an unknown property
    When I try to create an identifier generator with an enabled condition and an unknown property
    Then I should get an error with message 'conditions[0][unknown]: This field was not expected.'
    And the identifier should not be created

  Scenario: Cannot create several enabled conditions
    When I try to create an identifier generator with 2 enabled conditions
    Then I should get an error with message 'conditions: should contain only 1 enabled'
    And the identifier should not be created

  # Conditions: Family
  Scenario: Cannot create an identifier generator with unknown operator
    When I try to create an identifier generator with a family condition and unknown operator
    Then I should get an error with message 'conditions[0][operator]: Operator "unknown" can only be one of the following: "IN", "NOT IN", "EMPTY", "NOT EMPTY".'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with EMPTY operator and a value
    When I try to create an identifier generator with a family condition, EMPTY operator and ["shirts"] as value
    Then I should get an error with message 'conditions[0][value]: This field was not expected.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with IN operator and a non array value
    When I try to create an identifier generator with a family condition, IN operator and "shirts" as value
    Then I should get an error with message 'conditions[0][value]: This value should be of type iterable.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with IN operator and a non array of string value
    When I try to create an identifier generator with a family condition, IN operator and [true] as value
    Then I should get an error with message 'conditions[0][value][0]: This value should be of type string.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with IN operator and no values
    When I try to create an identifier generator with a family condition, IN operator and [] as value
    Then I should get an error with message 'conditions[0][value]: This collection should contain 1 element or more.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with IN operator and no value
    When I try to create an identifier generator with a family condition, IN operator and undefined as value
    Then I should get an error with message 'conditions[0][value]: This field is missing.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with non existing families
    When I try to create an identifier generator with a family condition, IN operator and ["non_existing1", "non_existing_2"] as value
    Then I should get an error with message 'conditions[0][value]: The following families have been deleted from your catalog: "non_existing1", "non_existing_2". You can remove them from your product selection.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with non existing field
    When I try to create an identifier generator with a family condition and an unknown property
    Then I should get an error with message 'conditions[0][unknown]: This field was not expected.'
    And the identifier should not be created

  Scenario: Cannot create several family conditions
    When I try to create an identifier generator with 2 family conditions
    Then I should get an error with message 'conditions: should contain only 1 family'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator without operator
    When I try to create an identifier generator with a family condition and undefined operator
    Then I should get an error with message 'conditions[0][operator]: This field is missing.'
    And the identifier should not be created

  # Conditions: Simple Select
  Scenario: Cannot create an identifier generator with unknown values
    When I try to create an identifier generator with a simple_select condition and an unknown property
    Then I should get an error with message 'conditions[0][unknown]: This field was not expected.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with IN operator and no value
    When I try to create an identifier generator with a simple_select condition, IN operator and undefined as value
    Then I should get an error with message 'conditions[0][value]: This field is missing.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with IN operator and no values
    When I try to create an identifier generator with a simple_select condition, IN operator and [] as value
    Then I should get an error with message 'conditions[0][value]: This collection should contain 1 element or more.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with IN operator and a non array value
    When I try to create an identifier generator with a simple_select condition, IN operator and "green" as value
    Then I should get an error with message 'conditions[0][value]: This value should be of type iterable.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with IN operator and a non array of string value
    When I try to create an identifier generator with a simple_select condition, IN operator and [true] as value
    Then I should get an error with message 'conditions[0][value][0]: This value should be of type string.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with unknown options
    When I try to create an identifier generator with a simple_select condition, IN operator and ["unknown1", "green", "unknown2"] as value
    Then I should get an error with message 'conditions[0][value]: The following attribute options do not exist for the attribute "color": "unknown1", "unknown2".'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with EMPTY operator and a value
    When I try to create an identifier generator with a simple_select condition, EMPTY operator and ["green"] as value
    Then I should get an error with message 'conditions[0][value]: This field was not expected.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with an unknown attribute
    When I try to create an identifier generator with a simple_select condition and unknown attribute
    Then I should get an error with message 'conditions[0][attributeCode]: The "unknown" attribute does not exist.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with wrong attribute type
    When I try to create an identifier generator with a simple_select condition and name attribute
    Then I should get an error with message 'conditions[0][attributeCode]: The "name" attribute code is "pim_catalog_text" type and should be of type "pim_catalog_simpleselect".'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator without scope
    Given the 'color_scopable' scopable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_scopable' attribute
    When I try to create an identifier generator with a simple_select condition, color_scopable attribute and undefined scope
    Then I should get an error with message 'conditions[0][scope]: A channel is required for the "color_scopable" attribute.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with scope
    When I try to create an identifier generator with a simple_select condition, color attribute and ecommerce scope
    Then I should get an error with message 'conditions[0][scope]: This field was not expected.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator without locale
    Given the 'color_localizable' localizable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_localizable' attribute
    When I try to create an identifier generator with a simple_select condition, color_localizable attribute and undefined locale
    Then I should get an error with message 'conditions[0][locale]: A locale is required for the "color_localizable" attribute.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with locale
    When I try to create an identifier generator with a simple_select condition, color attribute and en_US locale
    Then I should get an error with message 'conditions[0][locale]: This field was not expected.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with undefined scope
    Given the 'color_scopable' scopable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_scopable' attribute
    When I try to create an identifier generator with a simple_select condition, color_scopable attribute and unknown scope
    Then I should get an error with message 'conditions[0][scope]: The "unknown" scope does not exist.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with undefined locale
    Given the 'color_localizable' localizable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_localizable' attribute
    When I try to create an identifier generator with a simple_select condition, color_localizable attribute and unknown locale
    Then I should get an error with message 'conditions[0][locale]: The "unknown" locale does not exist or is not activated.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with non activated locale
    Given the 'color_localizable_and_scopable' localizable and scopable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_localizable_and_scopable' attribute
    And the 'website' channel having 'en_US' as locale
    And the 'ecommerce' channel having 'de_DE' as locale
    When I try to create an identifier generator with a simple_select condition, color_localizable_and_scopable attribute, ecommerce scope and en_US locale
    Then I should get an error with message 'conditions[0][locale]: The "en_US" locale is not active for the "ecommerce" channel.'
    And the identifier should not be created

  Scenario: Cannot create a multiselect condition by using the simple select attribute
    When I try to create an identifier generator with a multi_select condition and color attribute
    Then I should get an error with message 'conditions[0][attributeCode]: The "color" attribute code is "pim_catalog_simpleselect" type and should be of type "pim_catalog_multiselect".'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator using multi_select with IN operator and array of simple_select options
    When I try to create an identifier generator with a multi_select condition, a_multi_select attribute, IN operator and ["red", "green"] as value
    Then I should get an error with message 'conditions[0][value]: The following attribute options do not exist for the attribute "a_multi_select": "red", "green".'
    And the identifier should not be created

  # Label
  Scenario: Can create an identifier generator without label
    When I create an identifier generator without label
    Then The identifier generator is saved in the repository
    And I should not get any error

  Scenario: Cannot create an identifier generator with label too long
    When I try to create an identifier generator with 'fr_FR' label 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec suscipit nisi erat, sed tincidunt urna finibus non. Nullam id lacus et augue ullamcorper euismod sed id nibh. Praesent luctus cursus finibus. Maecenas et euismod tellus. Nunc sed est nec mi consequat consequat sit amet ac ex. '
    Then I should get an error with message 'labels[fr_FR]: This value is too long. It should have 255 characters or less.'
    And the identifier should not be created

  # Delimiter
  Scenario: Cannot create an identifier generator with an empty delimiter
    When I try to create an identifier generator with delimiter ''
    Then I should get an error with message 'delimiter: This value should not be blank.'
    And the identifier should not be created

  Scenario: Can create an identifier generator with delimiter null
    When I create an identifier generator with delimiter null
    Then The identifier generator is saved in the repository
    And I should not get any error

  Scenario: Cannot create an identifier generator with delimiter too long
    When I try to create an identifier generator with delimiter 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec suscipit nisi erat, sed tincidunt urna finibus non. Nullam id lacus et augue ullamcorper euismod sed id nibh. Praesent luctus cursus finibus. Maecenas et euismod tellus. Nunc sed est nec mi consequat consequat sit amet ac ex. '
    Then I should get an error with message 'delimiter: This value is too long. It should have 100 characters or less.'
    And the identifier should not be created

  # Text transformation
  Scenario: Cannot create an identifier generator with unknown text transformation
    When I try to create an identifier generator with text transformation unknown
    Then I should get an error with message 'textTransformation: Text transformation "unknown" can only be one of the following: "no", "uppercase", "lowercase".'
    And the identifier should not be created

  # Code
  Scenario: Cannot create an identifier generator with blank code
    When I try to create an identifier generator with code ''
    Then I should get an error with message 'code: This value should not be blank.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with code too long
    When I try to create an identifier generator with code 'Lorem_ipsum_dolor_sit_amet__consectetur_adipiscing_elit__Donec_suscipit_nisi_erat__sed_tincidunt_urna_finibus_non__Nullam_id_lacus_et_augue_ullamcorper_euismod_sed_id_nibh__Praesent_luctus_cursus_finibus__Maecenas_et_euismod_tellus__Nunc_sed_est_nec_mi_consequat_consequat_sit_amet_ac_ex__'
    Then I should get an error with message 'code: This value is too long. It should have 100 characters or less.'
    And the identifier should not be created

  Scenario: Cannot create an identifier generator with code bad format
    When I try to create an identifier generator with code 'Lorem ipsum dolor sit amet'
    Then I should get an error with message 'code: Code may contain only letters, numbers and underscore, "Lorem ipsum dolor sit amet" given.'
    And the identifier should not be created
