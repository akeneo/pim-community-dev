@acceptance-back
Feature: Create Identifier Generator

  Background:
    Given the 'sku' attribute of type 'pim_catalog_identifier'
    And the 'tshirts', 'shoes' categories

  Scenario: Cannot create an identifier generator with category condition and unknown operator
    When I try to create an identifier generator with a category condition and unknown operator
    Then I should get an error with message 'conditions[0][operator]: Operator "unknown" can only be one of the following: "IN", "NOT IN", "IN CHILDREN", "NOT IN CHILDREN", "CLASSIFIED", "UNCLASSIFIED".'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with category condition and unknown operator
    Given I create an identifier generator
    When I try to update an identifier generator with a category condition and unknown operator
    Then I should get an error with message 'conditions[0][operator]: Operator "unknown" can only be one of the following: "IN", "NOT IN", "IN CHILDREN", "NOT IN CHILDREN", "CLASSIFIED", "UNCLASSIFIED".'

  Scenario: Cannot create an identifier generator with category condition and CLASSIFIED operator and a value
    When I try to create an identifier generator with a category condition, CLASSIFIED operator and ["tshirts"] as value
    Then I should get an error with message 'conditions[0][value]: This field was not expected.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with category condition and CLASSIFIED operator and a value
    Given I create an identifier generator
    When I try to update an identifier generator with a category condition, CLASSIFIED operator and ["tshirts"] as value
    Then I should get an error with message 'conditions[0][value]: This field was not expected.'

  Scenario: Cannot create an identifier generator with category condition and IN operator and a non array value
    When I try to create an identifier generator with a family condition, IN operator and "shirts" as value
    Then I should get an error with message 'conditions[0][value]: This value should be of type iterable.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with category condition and IN operator and a non array value
    Given I create an identifier generator
    When I try to update an identifier generator with a family condition, IN operator and "shirts" as value
    Then I should get an error with message 'conditions[0][value]: This value should be of type iterable.'

  Scenario: Cannot create an identifier generator with category condition and IN operator and a non array of string value
    When I try to create an identifier generator with a category condition, IN operator and [true] as value
    Then I should get an error with message 'conditions[0][value][0]: This value should be of type string.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with category condition and IN operator and a non array of string value
    Given I create an identifier generator
    When I try to update an identifier generator with a category condition, IN operator and [true] as value
    Then I should get an error with message 'conditions[0][value][0]: This value should be of type string.'

  Scenario: Cannot create an identifier generator with category condition and IN operator and no values
    When I try to create an identifier generator with a category condition, IN operator and [] as value
    Then I should get an error with message 'conditions[0][value]: This collection should contain 1 element or more.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with category condition and IN operator and no values
    Given I create an identifier generator
    When I try to update an identifier generator with a category condition, IN operator and [] as value
    Then I should get an error with message 'conditions[0][value]: This collection should contain 1 element or more.'

  Scenario: Cannot create an identifier generator with category condition and IN operator and no value
    When I try to create an identifier generator with a category condition, IN operator and undefined as value
    Then I should get an error with message 'conditions[0][value]: This field is missing.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with category condition and IN operator and no value
    Given I create an identifier generator
    When I try to update an identifier generator with a category condition, IN operator and undefined as value
    Then I should get an error with message 'conditions[0][value]: This field is missing.'

  Scenario: Cannot create an identifier generator with category condition and non existing families
    When I try to create an identifier generator with a category condition, IN operator and ["non_existing1", "non_existing_2"] as value
    Then I should get an error with message 'conditions[0][value]: The following categories have been deleted from your catalog: "non_existing1", "non_existing_2". You can remove them from your product selection.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with category condition and non existing families
    Given I create an identifier generator
    When I try to update an identifier generator with a category condition, IN operator and ["non_existing1", "non_existing_2"] as value
    Then I should get an error with message 'conditions[0][value]: The following categories have been deleted from your catalog: "non_existing1", "non_existing_2". You can remove them from your product selection.'

  Scenario: Cannot create an identifier generator with category condition and non existing field
    When I try to create an identifier generator with a category condition and an unknown property
    Then I should get an error with message 'conditions[0][unknown]: This field was not expected.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with category condition and non existing field
    Given I create an identifier generator
    When I try to update an identifier generator with a category condition and an unknown property
    Then I should get an error with message 'conditions[0][unknown]: This field was not expected.'

  Scenario: Can create several category conditions
    When I try to create an identifier generator with 2 category conditions
    Then The identifier generator is saved in the repository
    And I should not get any error

  Scenario: Can update several category conditions
    Given I create an identifier generator
    When I try to update an identifier generator with 2 category conditions
    Then The identifier generator is updated in the repository
    And I should not get any error

  Scenario: Cannot create an identifier generator with category condition without operator
    When I try to create an identifier generator with a category condition and undefined operator
    Then I should get an error with message 'conditions[0][operator]: This field is missing.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with category condition without operator
    Given I create an identifier generator
    When I try to update an identifier generator with a category condition and undefined operator
    Then I should get an error with message 'conditions[0][operator]: This field is missing.'
