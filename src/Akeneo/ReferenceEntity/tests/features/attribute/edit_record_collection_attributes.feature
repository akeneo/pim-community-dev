Feature: Edit an attribute of an reference entity
  In order to edit an attribute of an reference entity
  As a user
  I want to edit an attribute of an reference entity

  @acceptance-back
  Scenario: Updating the label
    Given a reference entity with a record collection attribute 'brands' and the label 'en_US' equal to 'Brands'
    When the user updates the 'brands' attribute label with '"Past Brands"' on the locale '"en_US"'
    Then the label 'en_US' of the 'brands' attribute should be 'Past Brands'

  @acceptance-back
  Scenario Outline: Invalid label edit
    Given a reference entity with a record collection attribute 'brands' and the label 'en_US' equal to 'Brands'
    When the user updates the 'brands' attribute label with '<label>' on the locale '<localeCode>'
    Then there should be a validation error on the property 'labels' with message '<message>'

    Examples:
      | localeCode | label           | message                                                                                  |
      | 150        | "A valid label" | invalid locale code: This value should be of type string.                                |
      | null       | "A valid label" | invalid locale code: This value should not be blank.                                     |
      | "fr_FR"    | 200             | invalid label for locale code "fr_FR": This value should be of type string., "200" given |
      | "fr_FR"    | null            | invalid label for locale code "fr_FR": This value should not be null., "" given          |

  @acceptance-back
  Scenario: Updating is required property
    Given a reference entity with a record collection attribute 'brands' non required
    When the user sets the 'brands' attribute required
    Then 'brands' should be required

  @acceptance-back
  Scenario Outline: Invalid is required edit
    Given a reference entity with a record collection attribute 'brands' non required
    When the user sets the is_required property of 'brands' to '<invalid_required>'
    Then there should be a validation error on the property 'isRequired' with message '<message>'

    Examples:
      | invalid_required | message                               |
      | null             | This value should not be null.        |
      | "not_a_boolean"  | This value should be of type boolean. |
