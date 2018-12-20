Feature: Lists all attributes related to a reference entity
  In order to see the structure of a reference entity
  As a user
  I want to list all of its attributes

  @acceptance-front
  Scenario: Edit the attribute text
    Given a valid reference entity
    When the user asks for the reference entity "designer"
    And the user edit the attribute "name"
    And the attribute property "maxFileSize" should not be visible
    And the attribute property "allowedExtensions" should not be visible
    Then the user edits the attribute property "Label" with value "Nice Name"
    And the user edits the attribute property "IsRequired" with value "true"
    And the user edits the attribute property "MaxLength" with value "12"
    And the attribute property "isRichTextEditor" should not be visible
    And the user edits the attribute property "IsTextarea" with value "true"
    And the user edits the attribute property "IsRichTextEditor" with value "true"
    And the attribute property "validationRule" should not be visible
    And the user edits the attribute property "IsTextarea" with value "false"
    And the attribute property "isRichTextEditor" should not be visible
    And the attribute property "regularExpression" should not be visible
    And the user edits the attribute property "ValidationRule" with value "email"
    And the attribute property "regularExpression" should not be visible
    And the user edits the attribute property "ValidationRule" with value "url"
    And the attribute property "regularExpression" should not be visible
    And the user edits the attribute property "ValidationRule" with value "regular_expression"
    And the user edits the attribute property "RegularExpression" with value "nice!"

  @acceptance-front
  Scenario: Edit the image attribute
    Given a valid reference entity
    When the user asks for the reference entity "designer"
    And the user edit the attribute "portrait"
    And the attribute property "isRichTextEditor" should not be visible
    And the attribute property "isTextarea" should not be visible
    And the attribute property "validationRule" should not be visible
    And the attribute property "regularExpression" should not be visible
    And the attribute property "maxLength" should not be visible
    Then the user edits the attribute property "Label" with value "Nice Name"
    And the user edits the attribute property "IsRequired" with value "true"
    And the user edits the attribute property "MaxFileSize" with value "120.4"
    And the user edits the attribute property "AllowedExtensions" with value "gif"

  @acceptance-front
  Scenario: Edit the option attribute
    Given a valid reference entity
    When the user asks for the reference entity "designer"
    And the user edit the attribute "favorite_color"
    And the attribute property "isRichTextEditor" should not be visible
    And the attribute property "isTextarea" should not be visible
    And the attribute property "validationRule" should not be visible
    And the attribute property "regularExpression" should not be visible
    And the attribute property "maxLength" should not be visible
    Then the user edits the attribute property "Label" with value "Nice Name"
    And the user edits the attribute property "IsRequired" with value "true"

  @acceptance-front
  Scenario: Edit the option collection attribute
    Given a valid reference entity
    When the user asks for the reference entity "designer"
    And the user edit the attribute "colors"
    And the attribute property "isRichTextEditor" should not be visible
    And the attribute property "isTextarea" should not be visible
    And the attribute property "validationRule" should not be visible
    And the attribute property "regularExpression" should not be visible
    And the attribute property "maxLength" should not be visible
    Then the user edits the attribute property "Label" with value "Nice Name"
    And the user edits the attribute property "IsRequired" with value "true"

  @acceptance-front
  Scenario: Manage the list of options by adding a new option code
    Given a valid reference entity
    And the user asks for the reference entity "designer"
    And the user edit the attribute "favorite_color"
    When the user manages the options of the attribute
    Then the code of the "red" option should be locked
    And the code of the "green" option should be locked
    When the user adds the new option code "blue"
    And the user saves successfully
    Then the code of the "blue" option should be locked

  @acceptance-front
  Scenario: Fill in the label of an option to the list of available options of an option attribute
    Given a valid reference entity
    And the user asks for the reference entity "designer"
    And the user edit the attribute "favorite_color"
    When the user manages the options of the attribute
    And the user adds the new option label "Blue"
    Then the code of the option "Blue" should be "blue"

  @acceptance-front
  Scenario: Remove an option from the list
    Given a valid reference entity
    And the user asks for the reference entity "designer"
    And the user edit the attribute "favorite_color"
    When the user manages the options of the attribute
    And the user removes the option "red"
    Then the option "red" should not be in the list
    And the translation helper should display "Vert"

  @acceptance-front
  Scenario: Go to the next label to enrich when the user presses enter
    Given a valid reference entity
    And the user asks for the reference entity "designer"
    And the user edit the attribute "favorite_color"
    When the user manages the options of the attribute
    And the user goes to the next option to translate with the keyboard
    Then the focus should be on the "green" option label

  @acceptance-front
  Scenario: Go to the next code to enrich when the user presses enter
    Given a valid reference entity
    And the user asks for the reference entity "designer"
    And the user edit the attribute "favorite_color"
    And the user manages the options of the attribute
    And the user adds the new option code "blue"
    And the user adds the new option code "yellow"
    And the user focuses the "blue" option code
    When the user goes to the next option to translate with the keyboard
    Then the focus should be on the "yellow" option code

  @acceptance-front
  Scenario: The user cancels the changes made to the list
    Given a valid reference entity
    And the user asks for the reference entity "designer"
    And the user edit the attribute "favorite_color"
    And the user manages the options of the attribute
    And the user adds the new option code "blue"
    When the user cancels the changes by confirming the warning message

  @acceptance-front
  Scenario: The translation helper is initialized with the first option of the list
    Given a valid reference entity
    And the user asks for the reference entity "designer"
    And the user edit the attribute "favorite_color"
    When the user manages the options of the attribute
    Then the translation helper displays "Rouge"

  @acceptance-front
  Scenario: The user switches locales updates the option list
    Given a valid reference entity
    And the user asks for the reference entity "designer"
    And the user edit the attribute "favorite_color"
    And the user manages the options of the attribute
    And the label of the option "red" should be "Red"
    When the user changes the locale to translate to "fr_FR"
    Then the label of the option "red" should be "Rouge"
    And the translation helper displays "Red"

  # @acceptance-front
  Scenario: A validation occured because the code is not valid
    Given a valid reference entity
    And the user asks for the reference entity "designer"
    And the user edit the attribute "favorite_color"
    And the user manages the options of the attribute
    And the user removes the option "red"
    And the user removes the option "green"
    When the user adds the new option code "gre-een"
    Then the user cannot save the options successfully because the option is not valid
    And there is an error message next to the "gre-een" field

  #  @acceptance-front
  Scenario: A validation occured because the code is duplicated
    Given a valid reference entity
    And the user asks for the reference entity "designer"
    And the user edit the attribute "favorite_color"
    And the user manages the options of the attribute
    And the user removes the option "green"
    And the user adds the new option code "red"
    When the user cannot save the options successfully because an option is duplicated
    Then there is an error message next to the "red" field

  @acceptance-front
  Scenario: A validation occured because the number of attribute options has reached the limit
    Given a valid reference entity
    And the user asks for the reference entity "designer"
    And the user edit the attribute "favorite_color"
    And the user manages the options of the attribute
    When the user cannot save the options successfully because the limit of options is reached
    Then there is an error message next to the translator
