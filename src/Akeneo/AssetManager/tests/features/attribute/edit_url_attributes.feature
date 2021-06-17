Feature: Edit an URL attribute of an asset family
  In order to edit the properties of an URL attribute
  As a user
  I want to be able to edit an URL attribute

  @acceptance-back
  Scenario: Updating the label
    Given an asset family with an mediaLink attribute 'dam_image' and the label 'en_US' equal to 'DAM Image'
    When the user updates the 'dam_image' attribute label with '"Image DAM"' on the locale '"en_US"'
    Then the label 'en_US' of the 'dam_image' attribute should be 'Image DAM'

  @acceptance-back
  Scenario: Updating prefix property
    Given an asset family with an mediaLink attribute 'dam_image'
    When the user sets the prefix value of 'dam_image' to '"http://www.bynder.com/"'
    Then 'dam_image' prefix should be '"http://www.bynder.com/"'

  @acceptance-back
  Scenario: Updating prefix property with a relative url
    Given an asset family with an mediaLink attribute 'dam_image'
    When the user sets the prefix value of 'dam_image' to '"/prefix/"'
    Then 'dam_image' prefix should be '"/prefix/"'

  @acceptance-back
  Scenario Outline: Invalid prefix edit
    Given  an asset family with an mediaLink attribute 'dam_image'
    When the user sets the prefix value of 'dam_image' to '<invalid_prefix>'
    Then there should be a validation error on the property 'prefix' with message '<message>'

    Examples:
      | invalid_prefix | message                                                                            |
      | ""             | The prefix cannot be an empty string.                                              |
      | "file://"      | This field should start with a valid protocol. Allowed protocols are: http, https. |

  @acceptance-back
  Scenario: Updating suffix property
    Given an asset family with an mediaLink attribute 'dam_image'
    When the user sets the suffix value of 'dam_image' to '"/500x500/"'
    Then 'dam_image' suffix should be '"/500x500/"'

  @acceptance-back
  Scenario Outline: Invalid suffix edit
    Given  an asset family with an mediaLink attribute 'dam_image'
    When the user sets the suffix value of 'dam_image' to '<invalid_suffix>'
    Then there should be a validation error on the property 'suffix' with message '<message>'

    Examples:
      | invalid_suffix | message                               |
      | ""             | The suffix cannot be an empty string. |

  @acceptance-back
  Scenario: Updating media type property
    Given an asset family with an mediaLink attribute 'dam_image'
    When the user sets the media type value of 'dam_image' to '"other"'
    Then 'dam_image' media type should be '"other"'

  @acceptance-back
  Scenario Outline: Invalid media type edit
    Given  an asset family with an mediaLink attribute 'dam_image'
    When the user sets the media type value of 'dam_image' to '<invalid_media_type>'
    Then there should be a validation error on the property '<property_path>' with message '<message>'

    Examples:
      | invalid_media_type | property_path | message                                                                                             |
      | "video"            | mediaType     | The media type given is not corresponding to the expected ones (image, pdf, youtube, vimeo, other). |
      | ""                 | mediaType     | The media type given is not corresponding to the expected ones (image, pdf, youtube, vimeo, other). |
