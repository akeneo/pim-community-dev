Feature: Edit an URL attribute of a reference entity
  In order to edit the properties of an URL attribute
  As a user
  I want to be able to edit an URL attribute

  @acceptance-back
  Scenario: Updating the label
    Given a reference entity with an url attribute 'dam_image' and the label 'en_US' equal to 'DAM Image'
    When the user updates the 'dam_image' attribute label with '"Image DAM"' on the locale '"en_US"'
    Then the label 'en_US' of the 'dam_image' attribute should be 'Image DAM'

  @acceptance-back
  Scenario: Updating prefix property
    Given a reference entity with an url attribute 'dam_image'
    When the user sets the prefix value of 'dam_image' to '"http://www.bynder.com/"'
    Then 'dam_image' prefix should be '"http://www.bynder.com/"'

  @acceptance-back
  Scenario Outline: Invalid prefix edit
    Given  a reference entity with an url attribute 'dam_image'
    When the user sets the prefix value of 'dam_image' to '<invalid_prefix>'
    Then there should be a validation error on the property 'prefix' with message '<message>'

    Examples:
      | invalid_prefix | message                               |
      | ""             | The prefix cannot be an empty string. |

  @acceptance-back
  Scenario: Updating suffix property
    Given a reference entity with an url attribute 'dam_image'
    When the user sets the suffix value of 'dam_image' to '"/500x500/"'
    Then 'dam_image' suffix should be '"/500x500/"'

  @acceptance-back
  Scenario Outline: Invalid suffix edit
    Given  a reference entity with an url attribute 'dam_image'
    When the user sets the suffix value of 'dam_image' to '<invalid_suffix>'
    Then there should be a validation error on the property 'suffix' with message '<message>'

    Examples:
      | invalid_suffix | message                               |
      | ""             | The suffix cannot be an empty string. |

  @acceptance-back
  Scenario: Updating preview type property
    Given a reference entity with an url attribute 'dam_image'
    When the user sets the preview type value of 'dam_image' to '"other"'
    Then 'dam_image' preview type should be '"other"'

  @acceptance-back
  Scenario Outline: Invalid preview type edit
    Given  a reference entity with an url attribute 'dam_image'
    When the user sets the preview type value of 'dam_image' to '<invalid_preview_type>'
    Then there should be a validation error on the property '<property_path>' with message '<message>'

    Examples:
      | invalid_preview_type | property_path | message                                                                          |
      | "video"              | previewType   | The preview type given is not corresponding to the expected ones (image, other). |
      | ""                   | previewType   | The preview type given is not corresponding to the expected ones (image, other). |
