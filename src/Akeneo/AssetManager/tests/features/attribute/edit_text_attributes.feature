Feature: Edit a text attribute of a reference entity
  In order to edit the properties of a text attribute
  As a user
  I want to be able to edit a text attribute

  # Readonly values
  @acceptance-back
  Scenario: ValuePerLocale is not editable
    Given a reference entity with an attribute 'name' having a single value for all locales
    When the user updates the value_per_locale of 'name' to 'true'
    Then the value_per_locale of 'name' should be 'false'

  @acceptance-back
  Scenario: ValuePerChannel is not editable
    Given a reference entity with an attribute 'name' having a single value for all channels
    When the user updates the value_per_channel of 'name' to 'true'
    Then the value_per_locale of 'name' should be 'false'

  # labels
  @acceptance-back
  Scenario: Updating the label
    Given a reference entity with a text attribute 'name' and the label 'en_US' equal to 'Name'
    When the user updates the 'name' attribute label with '"Bio"' on the locale '"en_US"'
    Then the label 'en_US' of the 'name' attribute should be 'Bio'

  # is required
  @acceptance-back
  Scenario: Updating is required property
    Given a reference entity with a text attribute 'name' non required
    When the user sets the 'name' attribute required
    Then 'name' should be required

  @acceptance-back
  Scenario Outline: Updating with an invalid is required edit
    Given a reference entity with a text attribute 'name' non required
    When the user sets the is_required property of 'name' to '<invalid_required>'
    Then there should be a validation error on the property 'isRequired' with message '<message>'

    Examples:
      | invalid_required | message                               |
      | null             | This value should not be null.        |

  # max length
  @acceptance-back
  Scenario: Updating max length
    Given a reference entity with a text attribute 'name' and max length 100
    When the user changes the max length of 'name' to '250'
    Then 'name' max length should be 250

  @acceptance-back
  Scenario: Updating max length to no limit
    Given a reference entity with a text attribute 'name' and max length 100
    When the user changes the max length of 'name' to no limit
    Then there should be no limit for the max length of 'name'

  @acceptance-back
  Scenario Outline: Updating with an invalid max length
    Given a reference entity with a text attribute 'name' and max length 100
    When the user changes the max length of 'name' to '<invalid_max_length>'
    Then there should be a validation error on the property 'maxLength' with message '<message>'

    Examples:
      | invalid_max_length | message                                           |
      | -1                 | This value should be greater than 0.              |
      | 9999999999         | This value should be less than or equal to 65535. |
      | 0                  | This value should be greater than 0.              |

  # is text area
  @acceptance-back
  Scenario: Updating the is text area flag to false on a text area will set the is rich text editor to false
    Given a reference entity with a text area attribute 'description'
    When the user changes the is text area flag of 'description' to 'false'
    Then the 'description' attribute should be a simple text

  @acceptance-back
  Scenario: Updating the is text area flag to true on a simple text will set the validation rule and regular expression to an empty value
    Given a reference entity with a text attribute 'name'
    When the user changes the is text area flag of 'name' to 'true'
    Then the 'name' attribute should be a text area

  @acceptance-back
  Scenario Outline: Updating with an invalid is text area
    Given a reference entity with a text attribute 'name'
    When the user changes the is text area flag of 'name' to '<invalid_is_textarea_flag>'
    Then there should be a validation error on the property 'isTextarea' with message '<message>'

    Examples:
      | invalid_is_textarea_flag | message                               |
      | null                      | This value should not be null.        |

  # TODO: imports
  # Scenario: Updating the is text area flag of an attribute that is not a text fails

  # Validation rule
  @acceptance-back
  Scenario: Updating the validation rule
    Given a reference entity with a text attribute 'email' with no validation rule
    When the user changes the validation rule of 'email' to '"url"'
    Then the validation rule of 'email' should be 'url'

  @acceptance-back
  Scenario: Remove a validation rule from a text attribute (also removes the regular expression)
    Given a reference entity with a text attribute 'regex' with a regular expression '"[0-9]+"'
    When the user removes the validation rule of 'regex'
    Then there is no validation rule set on 'regex'
    And there is no regular expression set on 'regex'

  @acceptance-back
  Scenario: Updating the validation rule when it already has a regular expression removes the regular expression
    Given a reference entity with a text attribute 'regex' with a regular expression '"[0-9]+"'
    When the user changes the validation rule of 'regex' to '"url"'
    Then the regular expression of 'regex' should be empty

  @acceptance-back
  Scenario Outline: Updating with an invalid validation rule
    Given a reference entity with a text attribute 'email' with no validation rule
    When the user changes the validation rule of 'email' to '<invalid_validation_rule>'
    Then there should be a validation error on the property 'validationRule' with message '<message>'

    Examples:
      | invalid_validation_rule | message                                       |
      | "wrong_validation_rule" | The value you selected is not a valid choice. |

  @acceptance-back
  Scenario: Updating the validation rule if it's not a simple text fails
    Given a reference entity with a text area attribute 'description'
    When the user changes the validation rule of 'description' to '"url"'
    Then there should be a validation error with message 'A text area attribute cannot have a validation rule'

  @acceptance-back
  Scenario: Updating the validation rule and text area if it is not a simple text
    Given a reference entity with a text area attribute 'description'
    When the user changes the text area flag to 'false' and the validation rule of 'description' to 'url'
    Then the validation rule of 'description' should be 'url'

  # Regular expression
  @acceptance-back
  Scenario: Updating the regular expression
    Given a reference entity with a text attribute 'regex' with a regular expression '"/[0-9]+/"'
    When the user changes the regular expression of 'regex' to '"/a*/"'
    Then the regular expression of 'regex' should be '/a*/'

  @acceptance-back
  Scenario: Remove a regular expression from a text attribute
    Given a reference entity with a text attribute 'regex' with a regular expression '"/[0-9]+/"'
    When the user removes the regular expression of 'regex'
    Then there is no regular expression set on 'regex'

  @acceptance-back
  Scenario Outline: Updating with an invalid regular expression
    Given a reference entity with a text attribute 'regex' with a regular expression '/[0-9]+/'
    When the user changes the regular expression of 'regex' to '<invalid_regular_expression>'
    Then there should be a validation error on the property 'regularExpression' with message '<message>'

    Examples:
      | invalid_regular_expression | message                                                                                                  |
      | "a*"                       | This regular expression is not valid. Here is an example of a valid regular expression: "/[a-z]+[0-9]*/" |
      | ""                         | This regular expression is not valid. Here is an example of a valid regular expression: "/[a-z]+[0-9]*/" |

  @acceptance-back
  Scenario: Updating the regular expression if it's not a simple text fails
    Given a reference entity with a text area attribute 'description'
    When the user changes the regular expression of 'description' to '"/a+/"'
    Then there should be a validation error with message 'The attribute should not have a regular expression'

  @acceptance-back
  Scenario: Updating the regular expression if it's not a validation by regular expression fails
    Given a reference entity with a text attribute 'email' with no validation rule
    When the user changes the regular expression of 'email' to '"/[0-9]+[a-z]/"'
    Then there should be a validation error with message 'Cannot set a regular expression on the text attribute'

  @acceptance-back
  Scenario: Updating the regular expression on a text area without updating the validation rule to regular expression and the is_textarea flag to false will fail
    Given a reference entity with a text area attribute 'description'
    When the user changes the regular expression of 'description' to '"/[0-9]*/"'
    Then there should be a validation error with message 'The attribute should not have a regular expression'

  # Rich text editor
  @acceptance-back
  Scenario: Updating the is_rich_text_editor flag
    Given a reference entity with a text area attribute 'description' with no rich text editor
    When the user changes the is_rich_text_editor flag of 'description' to 'true'
    Then the attribute 'description' should have a text editor

  @acceptance-back
  Scenario Outline: Updating the is rich text editor flag with an invalid value will fail
    Given a reference entity with a text area attribute 'description'
    When the user changes the is_rich_text_editor flag of 'description' to '<invalid_is_rich_text_editor>'
    Then there should be a validation error on the property 'isRichTextEditor' with message '<message>'

    Examples:
      | invalid_is_rich_text_editor | message                               |
      | null                        | This value should not be null.        |

  @acceptance-back
  Scenario: Updating the is rich text editor flag if the attribute is not a text area fails
    Given a reference entity with a text attribute 'name'
    When the user changes the is_rich_text_editor flag of 'name' to 'true'
    Then there should be a validation error with message 'A simple text attribute cannot have a rich text editor'

  @acceptance-back
  Scenario: Updating the is_textarea flag and the is_rich_text_editor flag on a simple text attribute
    Given a reference entity with a text attribute 'name'
    When the user changes the is_textarea flag and the is_rich_text_editor of 'name' to 'true'
    Then the 'name' attribute should have a text editor
    And the 'name' attribute should be a text area

  @acceptance-back
  Scenario: Cannot update the attribute if it does not exist
    When the user updates the 'name' attribute label with '"Bio"' on the locale '"en_US"'
    Then there should be a validation error with message 'The attribute was not found'
