Feature: Edit a text attribute of an enriched entity
  In order to edit the properties of a text attribute
  As a user
  I want to be able to edit a text attribute

  @acceptance-back
  Scenario: Updating the label
    Given an enriched entity with a text attribute 'name' and the label 'en_US' equal to 'Name'
    When the user updates the 'name' attribute label with '"Bio"' on the locale '"en_US"'
    Then the label 'en_US' of the 'name' attribute should be 'Bio'

  @acceptance-back
  Scenario: Updating is required property
    Given an enriched entity with a text attribute 'name' non required
    When the user sets the 'name' attribute required
    Then then 'name' should be required

  @acceptance-back
  Scenario Outline: Invalid is required edit
    Given an enriched entity with a text attribute 'name' non required
    When the user sets the is required property of 'name' to '<invalid_required>'
    Then there should be a validation error on the property 'required' with message '<message>'

    Examples:
      | invalid_required | message                               |
      | null             | This value should not be null.        |
      | "not_a_boolean"  | This value should be of type boolean. |

  @acceptance-back
  Scenario: Updating max length
    Given an enriched entity with a text attribute 'name' and max length 100
    When the user changes the max length of 'name' to 250
    Then then 'name' max length should be 250

  @acceptance-back
  Scenario Outline: Invalid max length
    Given an enriched entity with a text attribute 'name' and max length 100
    When the user changes the max length of 'name' to '<invalid_max_length>'
    Then there should be a validation error on the property 'maxLength' with message '<message>'

    Examples:
      | invalid_max_length | message                                           |
      | -1                 | This value should be greater than 0.              |
      | 9999999999         | This value should be less than or equal to 65535. |
      | 0                  | This value should be greater than 0.              |
      | "not_an_integer"   | This value should be an integer.                  |
      | 254.2              | This value should be an integer.                  |
