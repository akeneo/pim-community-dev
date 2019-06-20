Feature: Edit the options of a list attribute
  In order to edit a list attribute (single or multiselect)
  As a user
  I want to edit the options of a list attribute

  @acceptance-back
  Scenario: Setting an available option to the option collection attribute
    Given an asset family with an option collection attribute with some options
    When the user adds the option 'red' with label 'Rouge' for locale 'fr_FR' to this attribute
    Then the option collection attribute should have an option 'red' with label 'Rouge' for the locale 'fr_FR'
    And the option attribute has 1 option
