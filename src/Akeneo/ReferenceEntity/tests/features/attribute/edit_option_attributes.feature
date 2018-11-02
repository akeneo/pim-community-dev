Feature: Edit the options of a list attribute
  In order to edit a list attribute (single or multiselect)
  As a user
  I want to edit the options of a list attribute

  @acceptance-back
  Scenario: Adding a new available option to the option attribute
    Given a reference entity with an option attribute with some options
    When the user adds the option 'red' with label 'Rouge' for locale 'fr_FR' to this attribute
    Then the option attribute should have an option 'red' with label 'Rouge' for the locale 'fr_FR'
    And the option attribute has 1 option

  @acceptance-back
  Scenario: Cannot update because there are too many options
    Given a reference entity with an option attribute
    When the user adds 101 options to this attribute
    Then there should be a validation error on the property 'options' with message 'Expected to have 100 at most, found 101'

  @acceptance-back
  Scenario: Cannot update the options of a list attribute because there is a duplicate option
    Given a reference entity with an option attribute
    When the user adds the same option twice 'red'
    Then there should be a validation error on the property 'options.red' with message 'The option "red" already exists'

  @acceptance-back
  Scenario Outline: Invalid update: an invalid option code
    Given a reference entity with an option attribute 'favorite_color' with no available options
    When the user adds the option '<invalid_option_code>'
    Then there should be a validation error on the property 'attribute_options' with message '<message>'

    Examples:
      | invalid_option_code | message                               |
      | null                | This value should not be null.        |
      | 255                 | This value should be of type boolean. |
      | gre/\en             | This value should be of type boolean. |

  @acceptance-back
  Scenario: Invalid update: the code is too long
    Given a reference entity with an option attribute 'favorite_color' with no available options
    When the user adds an option with a code too long
    Then there should be a validation error on the property 'isRequired' with message '<message>'

#  Scenario: Invalid update: the label of the option is too long


  @acceptance-back
  Scenario Outline: Invalid label edit
    Given a reference entity with an option attribute 'favorite_color' with no available options
    When the user updates the 'favorite_color' attribute label with '<label>' on the locale '<localeCode>'
    Then there should be a validation error on the property 'labels' with message '<message>'

    Examples:
      | localeCode | label           | message                                                                                  |
      | 150        | "A valid label" | invalid locale code: This value should be of type string.                                |
      | null       | "A valid label" | invalid locale code: This value should not be blank.                                     |
      | "fr_FR"    | 200             | invalid label for locale code "fr_FR": This value should be of type string., "200" given |
      | "fr_FR"    | null            | invalid label for locale code "fr_FR": This value should not be null., "" given          |

