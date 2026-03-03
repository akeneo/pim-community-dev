@acceptance-back
Feature: Create Identifier Generator

  Background:
    Given the 'sku' attribute of type 'pim_catalog_identifier'
    And the 'color' attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color' attribute

  Scenario: Cannot create an identifier generator with unknown values
    When I try to create an identifier generator with a simple_select condition and an unknown property
    Then I should get an error with message 'conditions[0][unknown]: This field was not expected.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with unknown values
    Given I create an identifier generator
    When I try to update an identifier generator with a simple_select condition and an unknown property
    Then I should get an error with message 'conditions[0][unknown]: This field was not expected.'

  Scenario: Cannot create an identifier generator with IN operator and no value
    When I try to create an identifier generator with a simple_select condition, IN operator and undefined as value
    Then I should get an error with message 'conditions[0][value]: This field is missing.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with IN operator and no value
    Given I create an identifier generator
    When I try to update an identifier generator with a simple_select condition with IN operator and undefined as value
    Then I should get an error with message 'conditions[0][value]: This field is missing.'

  Scenario: Cannot create an identifier generator with IN operator and no values
    When I try to create an identifier generator with a simple_select condition, IN operator and [] as value
    Then I should get an error with message 'conditions[0][value]: This collection should contain 1 element or more.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with IN operator and no values
    Given I create an identifier generator
    When I try to update an identifier generator with a simple_select condition with IN operator and [] as value
    Then I should get an error with message 'conditions[0][value]: This collection should contain 1 element or more.'

  Scenario: Cannot create an identifier generator with IN operator and a non array value
    When I try to create an identifier generator with a simple_select condition, IN operator and "green" as value
    Then I should get an error with message 'conditions[0][value]: This value should be of type iterable.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with IN operator and a non array value
    Given I create an identifier generator
    When I try to update an identifier generator with a simple_select condition with IN operator and "green" as value
    Then I should get an error with message 'conditions[0][value]: This value should be of type iterable.'

  Scenario: Cannot create an identifier generator with IN operator and a non array of string value
    When I try to create an identifier generator with a simple_select condition, IN operator and [true] as value
    Then I should get an error with message 'conditions[0][value][0]: This value should be of type string.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with IN operator and a non array of string value
    Given I create an identifier generator
    When I try to update an identifier generator with a simple_select condition with IN operator and [true] as value
    Then I should get an error with message 'conditions[0][value][0]: This value should be of type string.'

  Scenario: Cannot create an identifier generator with unknown options
    When I try to create an identifier generator with a simple_select condition, IN operator and ["unknown1", "green", "unknown2"] as value
    Then I should get an error with message 'conditions[0][value]: The following attribute options do not exist for the attribute "color": "unknown1", "unknown2".'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with unknown options
    Given I create an identifier generator
    When I try to update an identifier generator with a simple_select condition with IN operator and ["unknown1", "green", "unknown2"] as value
    Then I should get an error with message 'conditions[0][value]: The following attribute options do not exist for the attribute "color": "unknown1", "unknown2".'

  Scenario: Cannot create an identifier generator with EMPTY operator and a value
    When I try to create an identifier generator with a simple_select condition, EMPTY operator and ["green"] as value
    Then I should get an error with message 'conditions[0][value]: This field was not expected.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with EMPTY operator and a value
    Given I create an identifier generator
    When I try to update an identifier generator with a simple_select condition with EMPTY operator and ["green"] as value
    Then I should get an error with message 'conditions[0][value]: This field was not expected.'

  Scenario: Cannot create an identifier generator with an unknown attribute
    When I try to create an identifier generator with a simple_select condition and unknown attribute
    Then I should get an error with message 'conditions[0][attributeCode]: The "unknown" attribute does not exist.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with EMPTY operator and a value
    Given I create an identifier generator
    When I try to update an identifier generator with a simple_select condition with unknown attribute
    Then I should get an error with message 'conditions[0][attributeCode]: The "unknown" attribute does not exist.'

  Scenario: Cannot create an identifier generator with wrong attribute type
    Given the 'name' attribute of type 'pim_catalog_text'
    When I try to create an identifier generator with a simple_select condition and name attribute
    Then I should get an error with message 'conditions[0][attributeCode]: The "name" attribute code is "pim_catalog_text" type and should be of type "pim_catalog_simpleselect".'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with wrong attribute type
    Given I create an identifier generator
    And the 'name' attribute of type 'pim_catalog_text'
    When I try to update an identifier generator with a simple_select condition with name attribute
    Then I should get an error with message 'conditions[0][attributeCode]: The "name" attribute code is "pim_catalog_text" type and should be of type "pim_catalog_simpleselect".'

  Scenario: Cannot create an identifier generator without scope
    Given the 'color_scopable' scopable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_scopable' attribute
    When I try to create an identifier generator with a simple_select condition, color_scopable attribute and undefined scope
    Then I should get an error with message 'conditions[0][scope]: A channel is required for the "color_scopable" attribute.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator without scope
    Given I create an identifier generator
    And the 'color_scopable' scopable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_scopable' attribute
    When I try to update an identifier generator with a simple_select condition with color_scopable attribute and undefined scope
    Then I should get an error with message 'conditions[0][scope]: A channel is required for the "color_scopable" attribute.'

  Scenario: Cannot create an identifier generator with scope
    When I try to create an identifier generator with a simple_select condition, color attribute and ecommerce scope
    Then I should get an error with message 'conditions[0][scope]: This field was not expected.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with scope
    Given I create an identifier generator
    When I try to update an identifier generator with a simple_select condition with color attribute and ecommerce scope
    Then I should get an error with message 'conditions[0][scope]: This field was not expected.'

  Scenario: Cannot create an identifier generator without locale
    Given the 'color_localizable' localizable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_localizable' attribute
    When I try to create an identifier generator with a simple_select condition, color_localizable attribute and undefined locale
    Then I should get an error with message 'conditions[0][locale]: A locale is required for the "color_localizable" attribute.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator without locale
    Given I create an identifier generator
    And the 'color_localizable' localizable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_localizable' attribute
    When I try to update an identifier generator with a simple_select condition with color_localizable attribute and undefined locale
    Then I should get an error with message 'conditions[0][locale]: A locale is required for the "color_localizable" attribute.'

  Scenario: Cannot create an identifier generator with locale
    When I try to create an identifier generator with a simple_select condition, color attribute and en_US locale
    Then I should get an error with message 'conditions[0][locale]: This field was not expected.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with locale
    Given I create an identifier generator
    When I try to update an identifier generator with a simple_select condition with color attribute and en_US locale
    Then I should get an error with message 'conditions[0][locale]: This field was not expected.'

  Scenario: Cannot create an identifier generator with undefined scope
    Given the 'color_scopable' scopable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_scopable' attribute
    When I try to create an identifier generator with a simple_select condition, color_scopable attribute and unknown scope
    Then I should get an error with message 'conditions[0][scope]: The "unknown" scope does not exist.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with undefined scope
    Given I create an identifier generator
    And the 'color_scopable' scopable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_scopable' attribute
    When I try to update an identifier generator with a simple_select condition with color_scopable attribute and unknown scope
    Then I should get an error with message 'conditions[0][scope]: The "unknown" scope does not exist.'

  Scenario: Cannot create an identifier generator with undefined locale
    Given the 'color_localizable' localizable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_localizable' attribute
    When I try to create an identifier generator with a simple_select condition, color_localizable attribute and unknown locale
    Then I should get an error with message 'conditions[0][locale]: The "unknown" locale does not exist or is not activated.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with undefined locale
    Given I create an identifier generator
    And the 'color_localizable' localizable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_localizable' attribute
    When I try to update an identifier generator with a simple_select condition with color_localizable attribute and unknown locale
    Then I should get an error with message 'conditions[0][locale]: The "unknown" locale does not exist or is not activated.'

  Scenario: Cannot create an identifier generator with non activated locale
    Given the 'color_localizable_and_scopable' localizable and scopable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_localizable_and_scopable' attribute
    And the 'website' channel having 'en_US' as locale
    And the 'ecommerce' channel having 'de_DE' as locale
    When I try to create an identifier generator with a simple_select condition, color_localizable_and_scopable attribute, ecommerce scope and en_US locale
    Then I should get an error with message 'conditions[0][locale]: The "en_US" locale is not active for the "ecommerce" channel.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with non activated locale
    Given I create an identifier generator
    And the 'color_localizable_and_scopable' localizable and scopable attribute of type 'pim_catalog_simpleselect'
    And the 'red', 'green' and 'blue' options for 'color_localizable_and_scopable' attribute
    And the 'website' channel having 'en_US' as locale
    And the 'ecommerce' channel having 'de_DE' as locale
    When I try to update an identifier generator with a simple_select condition with color_localizable_and_scopable attribute and ecommerce scope and en_US locale
    Then I should get an error with message 'conditions[0][locale]: The "en_US" locale is not active for the "ecommerce" channel.'

  Scenario: Cannot create a multiselect condition by using the simple select attribute
    When I try to create an identifier generator with a multi_select condition and color attribute
    Then I should get an error with message 'conditions[0][attributeCode]: The "color" attribute code is "pim_catalog_simpleselect" type and should be of type "pim_catalog_multiselect".'
    And the identifier generator should not be created

  Scenario: Cannot update a multiselect condition by using the simple select attribute
    Given I create an identifier generator
    When I try to update an identifier generator with a multi_select condition with color attribute
    Then I should get an error with message 'conditions[0][attributeCode]: The "color" attribute code is "pim_catalog_simpleselect" type and should be of type "pim_catalog_multiselect".'

  Scenario: Cannot create an identifier generator using multi_select with IN operator and array of simple_select options
    When I try to create an identifier generator with a multi_select condition, a_multi_select attribute, IN operator and ["red", "green"] as value
    Then I should get an error with message 'conditions[0][value]: The following attribute options do not exist for the attribute "a_multi_select": "red", "green".'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator using multi_select with IN operator and array of simple_select options
    Given I create an identifier generator
    When I try to update an identifier generator with a multi_select condition with IN operator and ["red", "green"] as value
    Then I should get an error with message 'conditions[0][value]: The following attribute options do not exist for the attribute "a_multi_select": "red", "green".'
