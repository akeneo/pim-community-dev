@acceptance-back
Feature: Create Identifier Generator

  Background:
    Given the 'sku' attribute of type 'pim_catalog_identifier'

  Scenario: Cannot create an identifier generator with unknown operator
    When I try to create an identifier generator with a family condition and unknown operator
    Then I should get an error with message 'conditions[0][operator]: Operator "unknown" can only be one of the following: "IN", "NOT IN", "EMPTY", "NOT EMPTY".'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with unknown operator
    Given I create an identifier generator
    When I try to update an identifier generator with a family condition with unknown operator
    Then I should get an error with message 'conditions[0][operator]: Operator "unknown" can only be one of the following: "IN", "NOT IN", "EMPTY", "NOT EMPTY".'

  Scenario: Cannot create an identifier generator with EMPTY operator and a value
    When I try to create an identifier generator with a family condition, EMPTY operator and ["shirts"] as value
    Then I should get an error with message 'conditions[0][value]: This field was not expected.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with EMPTY operator and a value
    Given I create an identifier generator
    When I try to update an identifier generator with a family condition with EMPTY operator and ["shirts"] as value
    Then I should get an error with message 'conditions[0][value]: This field was not expected.'

  Scenario: Cannot create an identifier generator with IN operator and a non array value
    When I try to create an identifier generator with a family condition, IN operator and "shirts" as value
    Then I should get an error with message 'conditions[0][value]: This value should be of type iterable.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with IN operator and a non array value
    Given I create an identifier generator
    When I try to update an identifier generator with a family condition with IN operator and "shirts" as value
    Then I should get an error with message 'conditions[0][value]: This value should be of type iterable.'

  Scenario: Cannot create an identifier generator with IN operator and a non array of string value
    When I try to create an identifier generator with a family condition, IN operator and [true] as value
    Then I should get an error with message 'conditions[0][value][0]: This value should be of type string.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with IN operator and a non array of string value
    Given I create an identifier generator
    When I try to update an identifier generator with a family condition with IN operator and [true] as value
    Then I should get an error with message 'conditions[0][value][0]: This value should be of type string.'

  Scenario: Cannot create an identifier generator with IN operator and no values
    When I try to create an identifier generator with a family condition, IN operator and [] as value
    Then I should get an error with message 'conditions[0][value]: This collection should contain 1 element or more.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with IN operator and no values
    Given I create an identifier generator
    When I try to update an identifier generator with a family condition with IN operator and [] as value
    Then I should get an error with message 'conditions[0][value]: This collection should contain 1 element or more.'

  Scenario: Cannot create an identifier generator with IN operator and no value
    When I try to create an identifier generator with a family condition, IN operator and undefined as value
    Then I should get an error with message 'conditions[0][value]: This field is missing.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with IN operator and no value
    Given I create an identifier generator
    When I try to update an identifier generator with a family condition with IN operator and undefined as value
    Then I should get an error with message 'conditions[0][value]: This field is missing.'

  Scenario: Cannot create an identifier generator with non existing families
    When I try to create an identifier generator with a family condition, IN operator and ["non_existing1", "non_existing_2"] as value
    Then I should get an error with message 'conditions[0][value]: The following families have been deleted from your catalog: "non_existing1", "non_existing_2". You can remove them from your product selection.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with non existing family
    Given I create an identifier generator
    When I try to update an identifier generator with a family condition with IN operator and ["non_existing1", "non_existing_2"] as value
    Then I should get an error with message 'conditions[0][value]: The following families have been deleted from your catalog: "non_existing1", "non_existing_2". You can remove them from your product selection.'

  Scenario: Cannot create an identifier generator with non existing field
    When I try to create an identifier generator with a family condition and an unknown property
    Then I should get an error with message 'conditions[0][unknown]: This field was not expected.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator with non existing field
    Given I create an identifier generator
    When I try to update an identifier generator with a family condition and an unknown property
    Then I should get an error with message 'conditions[0][unknown]: This field was not expected.'

  Scenario: Cannot create several family conditions
    When I try to create an identifier generator with 2 family conditions
    Then I should get an error with message 'conditions: should contain only 1 family'
    And the identifier generator should not be created

  Scenario: Cannot update several family conditions
    Given I create an identifier generator
    When I try to update an identifier generator with 2 family conditions
    Then I should get an error with message 'conditions: should contain only 1 family'

  Scenario: Cannot create an identifier generator without operator
    When I try to create an identifier generator with a family condition and undefined operator
    Then I should get an error with message 'conditions[0][operator]: This field is missing.'
    And the identifier generator should not be created

  Scenario: Cannot update an identifier generator without operator
    Given I create an identifier generator
    When I try to update an identifier generator with a family condition with undefined operator
    Then I should get an error with message 'conditions[0][operator]: This field is missing.'
