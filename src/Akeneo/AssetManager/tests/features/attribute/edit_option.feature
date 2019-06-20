Feature: Append a valid option into an attribute
  In order to have a collection of attribute option for a given option attribute or an option collection attribute
  As a user
  I want to append a new option into this collection of options

  Background:
    Given a valid reference entity

  @acceptance-back
  Scenario: Edit an existing option of an option attribute
    Given an option attribute with one option
    When the user edits the option of this attribute
    Then the option is correctly edited

  @acceptance-back
  Scenario: Edit an existing option of an option collection attribute
    Given an option collection attribute with one option
    When the user edits the option of this attribute
    Then the option is correctly edited

  @acceptance-back
  Scenario: Cannot edit an option if the option does not exist
    Given an option attribute without option
    When the user edits the option of this attribute
    Then there should be a validation error with message 'The option was not found.'
