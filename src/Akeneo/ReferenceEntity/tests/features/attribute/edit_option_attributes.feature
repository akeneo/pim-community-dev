Feature: Edit the options of a list attribute
  In order to edit a list attribute (single or multiselect)
  As a user
  I want to edit the options of a list attribute

  @acceptance-back
  Scenario: Setting an available option to the option attribute
    Given a reference entity with an option attribute with some options
    When the user adds the option 'red' with label 'Rouge' for locale 'fr_FR' to this attribute
    Then the option attribute should have an option 'red' with label 'Rouge' for the locale 'fr_FR'
    And the option attribute has 1 option

  @acceptance-back
  Scenario: Set too many options
    Given a reference entity with an option attribute 'favorite_color' and the label 'en_US' equal to 'Favorite color'
    When the user adds 101 options to this attribute
    Then there should be a validation error on the property 'options' with message 'You have reached the limit of 100 attribute options per attribute.'

  @acceptance-back
  Scenario: Set duplicated options
    Given a reference entity with an option attribute 'favorite_color' and the label 'en_US' equal to 'Favorite color'
    When the user adds the 'red' option twice
    Then there should be a validation error on the property 'options.red' with message 'The option "red" already exists'

  @acceptance-back
  Scenario Outline: Set an option with an invalid code
    Given a reference entity with an option attribute 'favorite_color' and the label 'en_US' equal to 'Favorite color'
    When the user sets the '<invalid_option_code>' option
    Then there should be a validation error on the property '<property_path>' with message '<message>'

    Examples:
      | invalid_option_code | property_path  | message                                                       |
      | null                | options        | The code of the option should not be blank                    |
      | 255                 | options.255    | This value should be of type string.                          |
      | "gre-en"            | options.gre-en | This field may only contain letters, numbers and underscores. |

  @acceptance-back
  Scenario: Set an option with a code too long
    Given a reference entity with an option attribute 'favorite_color' and the label 'en_US' equal to 'Favorite color'
    When the user sets an option with a code too long
    Then there should be a validation error on the property 'options.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa' with message 'This value is too long. It should have 255 characters or less.'

  @acceptance-back
  Scenario Outline: Set an option with an invalid label
    Given a reference entity with an option attribute 'favorite_color' and the label 'en_US' equal to 'Favorite color'
    When the user sets an option with a label '<invalid_option_label>'
    Then there should be a validation error on the property 'options.option_code' with message '<message>'

    Examples:
      | invalid_option_label | message                                                                                  |
      | null                 | invalid label for locale code "fr_FR": This value should not be null., "" given          |
      | 255                  | invalid label for locale code "fr_FR": This value should be of type string., "255" given |
