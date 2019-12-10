Feature: Edit an attribute of an asset family
  In order to edit an attribute of an asset family
  As a user
  I want to edit an attribute of an asset family

  @acceptance-back
  Scenario: Updating the label
    Given an asset family with a media file attribute 'image' and the label 'en_US' equal to 'Image'
    When the user updates the 'image' attribute label with '"Portrait"' on the locale '"en_US"'
    Then the label 'en_US' of the 'image' attribute should be 'Portrait'

  @acceptance-back
  Scenario Outline: Invalid label edit
    Given an asset family with a media file attribute 'image' and the label 'en_US' equal to 'Name'
    When the user updates the 'image' attribute label with '<label>' on the locale '<localeCode>'
    Then there should be a validation error on the property 'labels' with message '<message>'

    Examples:
      | localeCode | label           | message                                                                                  |
      | 150        | "A valid label" | invalid locale code: This value should be of type string.                                |
      | null       | "A valid label" | invalid locale code: This value should not be blank.                                     |
      | "fr_FR"    | 200             | invalid label for locale code "fr_FR": This value should be of type string., "200" given |
      | "fr_FR"    | null            | invalid label for locale code "fr_FR": This value should not be null., "" given          |

  @acceptance-back
  Scenario: Updating is required property
    Given an asset family with a media file attribute 'image' non required
    When the user sets the 'image' attribute required
    Then 'image' should be required

  @acceptance-back
  Scenario Outline: Invalid is required edit
    Given an asset family with a media file attribute 'image' non required
    When the user sets the is_required property of 'image' to '<invalid_required>'
    Then there should be a validation error on the property 'isRequired' with message '<message>'

    Examples:
      | invalid_required | message                               |
      | null             | This value should not be null.        |

  # Max file size
  @acceptance-back
  Scenario: Updating max file size
    Given an asset family with a media file attribute 'image' with max file size '3000'
    When the user changes the max file size of 'image' to '"200"'
    Then the max file size of 'image' should be '200'

  @acceptance-back
  Scenario: Updating max file size to no limit
    Given an asset family with a media file attribute 'name' with max file size '250'
    When the user changes the max file size of 'name' to no limit
    Then there should be no limit for the max file size of 'name'

  @acceptance-back
  Scenario Outline: Invalid max file size edit
    Given an asset family with a media file attribute 'image' with max file size '3000'
    When the user changes the max file size of 'image' to '<invalid_max_file_size>'
    Then there should be a validation error on the property 'maxFileSize' with message '<message>'

    Examples:
      | invalid_max_file_size | message                                             |
      | "not_a_boolean"       | This value should be a number.                      |
      | ""                    | This value should be a number.                      |
      | "-3.4"                | This value should be greater than 0.                |

  # TODO: imports
  # Scenario: Updating the max file size of an attribute that is not image fails

  # Allowed extensions
  @acceptance-back
  Scenario: Updating allowed extensions
    Given an asset family with a media file attribute 'image' with allowed extensions: '["png"]'
    When the user changes adds '["png"]' to the allowed extensions of 'image'
    Then the 'image' should have '["png"]' as an allowed extension

  @acceptance-back
  Scenario: Updating allowed extensions to extensions all allowed
    Given an asset family with a media file attribute 'image' with allowed extensions: '[]'
    When the user changes adds '[]' to the allowed extensions of 'image'
    Then the 'image' should have '[]' as an allowed extension

# Should we accept leading '.' ? should the VO try to remove it on its own if it exists ?
#  @acceptance-back
#  Scenario Outline: Invalid allowed extensions
#    Given an asset family with a media file attribute 'image' with allowed extensions: '["png", "jpeg"]'
#    When the user changes adds '<invalid_allowed_extensions>' to the allowed extensions of 'image'
#    Then there should be a validation error on the property 'allowedExtensions' with message '<message>'
#
#    Examples:
#      | invalid_allowed_extensions | message                                     |
#      | [".not_a_valid_extension"] | One or more of the given values is invalid. |

  @acceptance-back
  Scenario: Updating media type
    Given an asset family with a media file attribute image with media type image
    When the user changes the media type to pdf
    Then the media type should be pdf

  @acceptance-back
  Scenario: Updating with an invalid media type
    Given an asset family with a media file attribute image with media type image
    When the user changes the media type to an unknown media type
    Then there should be a validation error on the property 'mediaType' with message 'The media type given is not corresponding to the expected ones (image, pdf, other).'
