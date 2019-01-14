Feature: Append a valid option into an attribute
  In order to have a collection of attribute option for a given option attribute or an option collection attribute
  As a user
  I want to append a new option into this collection of options

  Background:
    Given a valid reference entity

  @acceptance-back
  Scenario: Append an option into an option attribute
    Given an option attribute
    When the user appends a new option for this option attribute
    Then the option is added into the option collection of this attribute

  @acceptance-back
  Scenario: Append an option into an option collection attribute
    Given an option collection attribute
    When the user appends a new option for this option collection attribute
    Then the option is added into the option collection of this attribute

  @acceptance-back
  Scenario: Cannot append an option if the maximum number of options in an option collection attribute is reached
    Given an option collection attribute with the maximum number of options
    When the user appends a new option for this option collection attribute
    Then there should be a validation error with message 'You have reached the limit of 100 attribute options per attribute.'

  @acceptance-back
  Scenario: Cannot append an option if the maximum number of options in an option attribute is reached
    Given an option attribute with the maximum number of options
    When the user appends a new option for this option attribute
    Then there should be a validation error with message 'You have reached the limit of 100 attribute options per attribute.'

  @acceptance-back
  Scenario: Cannot append an option if the option already exists
    Given an option collection attribute Color with a Red option
    When the user appends a Red option into the option collection attribute
    Then there should be a validation error with message 'The option "red" already exists'
