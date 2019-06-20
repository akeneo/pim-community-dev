Feature: Edit an attribute of an asset family
  In order to edit an attribute of an asset family
  As a user
  I want to edit an attribute of an asset family

  @acceptance-back
  Scenario: Updating the label
    Given an asset family with a asset attribute 'mentor' and the label 'en_US' equal to 'Mentor'
    When the user updates the 'mentor' attribute label with '"Designer Mentor"' on the locale '"en_US"'
    Then the label 'en_US' of the 'mentor' attribute should be 'Designer Mentor'

  @acceptance-back
  Scenario Outline: Invalid label edit
    Given an asset family with a asset attribute 'mentor' and the label 'en_US' equal to 'Mentor'
    When the user updates the 'mentor' attribute label with '<label>' on the locale '<localeCode>'
    Then there should be a validation error on the property 'labels' with message '<message>'

    Examples:
      | localeCode | label           | message                                                                                  |
      | 150        | "A valid label" | invalid locale code: This value should be of type string.                                |
      | null       | "A valid label" | invalid locale code: This value should not be blank.                                     |
      | "fr_FR"    | 200             | invalid label for locale code "fr_FR": This value should be of type string., "200" given |
      | "fr_FR"    | null            | invalid label for locale code "fr_FR": This value should not be null., "" given          |

  @acceptance-back
  Scenario: Updating is required property
    Given an asset family with a asset attribute 'mentor' non required
    When the user sets the 'mentor' attribute required
    Then 'mentor' should be required

  @acceptance-back
  Scenario Outline: Invalid is required edit
    Given an asset family with a asset attribute 'mentor' non required
    When the user sets the is_required property of 'mentor' to '<invalid_required>'
    Then there should be a validation error on the property 'isRequired' with message '<message>'

    Examples:
      | invalid_required | message                               |
      | null             | This value should not be null.        |
