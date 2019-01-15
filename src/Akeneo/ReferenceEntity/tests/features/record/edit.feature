Feature: Edit an record
  In order to update the information of an record
  As a user
  I want see the details of an record and update them

  @acceptance-back
  Scenario: Updating a record label
    Given a reference entity and a record with french label "My label"
    When the user updates the french label to "My updated label"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the french label "My updated label"

  @acceptance-back
  Scenario: Emptying a record label
    Given a reference entity and a record with french label "My label"
    When the user empties the french label
    Then there is no exception thrown
    And there is no violations errors
    And the record should not have a french label

  @acceptance-back
  Scenario: Record is not valid when enriching a record label by specifying a not activated locale for the label
    Given a reference entity and a record with french label "My label"
    When the user updates the german label to "My updated label"
    Then there should be a validation error on the property labels with message "The locale "de_DE" is not activated."

  @acceptance-back
  Scenario: Updating a record default image
    Given a referenceEntity and a record with an image
    When the user updates the record default image with a valid file
    And the record should have the new default image

  @acceptance-back
  Scenario: Updating a record with an empty image
    Given a referenceEntity and a record with an image
    When the user updates the record default image with an empty image
    And the record should have an empty image

  @acceptance-back
  Scenario Outline: Updating a record default image with an invalid file
    Given a referenceEntity and a record with an image
    When the user updates the record default image with path '<wrong_path>' and filename '<wrong_filename>'
    Then there should be a validation error on the property 'image' with message '<message>'

  Examples:
  | wrong_path        | wrong_filename | message                              |
  | false             | "image.jpg"    | This value should not be blank.      |
  | 150               | "image.jpg"    | This value should be of type string. |
  | "/path/image.jpg" | false          | This value should not be blank.      |
  | "/path/image.jpg" | 150            | This value should be of type string. |

  # ValuePerChannel / ValuePerLocale
  @acceptance-back
  Scenario: Updating a localizable value of a record
    Given a reference entity with a localizable attribute
    And a record belonging to this reference entity with a value for the french locale
    When the user updates the attribute of the record for the french locale
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the updated value for this attribute and the french locale

  @acceptance-back
  Scenario: Record is not valid when enriching a localizable attribute value of a record without specifying the locale in the value
    Given a reference entity with a localizable attribute
    When the user updates the localizable attribute value of the record without specifying the locale
    Then there should be a validation error on the property text attribute with message "A locale is expected for attribute "name" because it has a value per locale."

  @acceptance-back
  Scenario: Record is not valid when enriching a not localizable attribute value of a record by specifying the locale in the value
    Given a reference entity with a not localizable attribute
    When the user updates the not localizable attribute value of the record by specifying the locale
    Then there should be a validation error on the property text attribute with message "A locale is not expected for attribute "name" because it has not a value per locale."

  @acceptance-back
  Scenario: Record is not valid when enriching a localizable attribute value of a record by specifying a not activated locale in the value
    Given a reference entity with a localizable attribute
    When the user updates the attribute value of the record by specifying a not activated locale
    Then there should be a validation error on the property text attribute with message "The locale "de_DE" is not activated."

  @acceptance-back
  Scenario: Updating a scopable value of a record
    Given a reference entity with a scopable attribute
    And a record belonging to this reference entity with a value for the ecommerce channel
    When the user updates the attribute of the record for the ecommerce channel
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the updated value for this attribute and the ecommerce channel

  @acceptance-back
  Scenario: Updating a scopable value of a record with an invalid channel
    Given a reference entity with a scopable attribute
    And a record belonging to this reference entity with a value for the ecommerce channel
    When the user updates the attribute of the record with an invalid channel
    Then there should be a validation error on the property text attribute with message "This value should be of type string."

  @acceptance-back
  Scenario: Record is not valid when enriching a scopable attribute value without specifying the channel in the value
    Given a reference entity with a scopable attribute
    And a record belonging to this reference entity with a value for the ecommerce channel
    When the user enriches a scopable attribute value of a record without specifying the channel
    Then there should be a validation error on the property text attribute with message "A channel is expected for attribute "name" because it has a value per channel."

  @acceptance-back
  Scenario: Record is not valid when enriching a not scopable attribute value by specifying the channel in the value
    Given a reference entity with a not scopable attribute
    And a record belonging to this reference entity with a value for the not scopable attribute
    When the user updates the not scopable attribute of the record by specifying a channel
    Then there should be a validation error on the property text attribute with message "A channel is not expected for attribute "name" because it has not a value per channel."

  @acceptance-back
  Scenario: Record is not valid when enriching a scopable attribute value by specifying an unknown channel in the value
    Given a reference entity with a scopable attribute
    And a record belonging to this reference entity with a value for the ecommerce channel
    When the user updates the attribute value of the record by specifying an unknown channel
    Then there should be a validation error on the property text attribute with message "The channel "unknown_channel" does not exist."

  @acceptance-back
  Scenario: Updating a scopable and localizable value of a record
    Given a reference entity with a scopable and localizable attribute
    And a record belonging to this reference entity with a value for the ecommerce channel and french locale
    When the user updates the attribute of the record for the ecommerce channel and french locale
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the updated value for this attribute and the ecommerce channel and the french locale

  @acceptance-back
  Scenario: Record is not valid when enriching a scopable and localizable attribute value by specifying a locale not activated for the channel in the value
    Given a reference entity with a scopable and localizable attribute
    When the user updates the attribute value of the record by specifying a locale not activated for the ecommerce channel
    Then there should be a validation error on the property text attribute with message "The locale "de_DE" is not activated for the channel "ecommerce"."

  # Text
  @acceptance-back
  Scenario: Updating the text value of a record
    Given a reference entity with a text attribute
    And a record belonging to this reference entity with a value of "Philippe stark" for the text attribute
    When the user updates the text attribute of the record to "Stark"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the text value "Stark" for this attribute

  @acceptance-back
  Scenario: Emptying a text value
    Given a reference entity with a text attribute
    And a record belonging to this reference entity with a value of "Philippe stark" for the text attribute
    When the user empties the text attribute of the record
    Then there is no exception thrown
    And there is no violations errors
    And the record should have an empty value for this attribute

  @acceptance-back
  Scenario: Setting a text value to empty string empties this value
    Given a reference entity with a text attribute
    And a record belonging to this reference entity with a value of "Philippe stark" for the text attribute
    When the user updates the text attribute of the record to ""
    Then there is no exception thrown
    And there is no violations errors
    And the record should have an empty value for this attribute

  @acceptance-back
  Scenario: Updating the text value of a record with an invalid value type
    Given a reference entity with a text attribute
    And a record belonging to this reference entity with a value of "Philippe stark" for the text attribute
    When the user updates the text attribute of the record to an invalid value type
    Then an exception is thrown with message "There was no factory found to create the edit record value command of the attribute "name_designer_fingerprint""

  @acceptance-back
  Scenario: Updating the text value with more characters than the attribute's max length
    Given a reference entity with a text attribute with max length 10
    And a record belonging to this reference entity with a value of "Philippe" for the text attribute
    When the user updates the text attribute of the record to "Philippe Starck, né le 18 janvier 1949 à Paris"
    Then there should be a validation error on the property text attribute with message "This value is too long. It should have 10 characters or less."

  @acceptance-back
  Scenario: Updating the text value with less characters than the attribute's max length
    Given a reference entity with a text attribute with max length 10
    And a record belonging to this reference entity with a value of "Philippe" for the text attribute
    When the user updates the text attribute of the record to "Didier"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the text value "Didier" for this attribute

  @acceptance-back
  Scenario: Updating an email with an invalid email value
    Given a reference entity with a text attribute with an email validation rule
    And a record belonging to this reference entity with a value of "jean-pierre@dummy.com" for the text attribute
    When the user updates the text attribute of the record to "Hello my name is jean-pierre."
    Then there should be a validation error on the property text attribute with message "This value is not a valid email address."

  @acceptance-back
  Scenario: Updating an email with an valid email value
    Given a reference entity with a text attribute with an email validation rule
    And a record belonging to this reference entity with a value of "jean-pierre@dummy.com" for the text attribute
    When the user updates the text attribute of the record to "didier@dummy.com"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the text value "didier@dummy.com" for this attribute

  @acceptance-back
  Scenario: Updating an url with an invalid url value
    Given a reference entity with a text attribute with an url validation rule
    And a record belonging to this reference entity with a value of "https://www.akeneo.com/" for the text attribute
    When the user updates the text attribute of the record to "htt://akeneo.com/"
    Then there should be a validation error on the property text attribute with message "This value is not a valid URL."

  @acceptance-back
  Scenario: Updating an url with an valid url value
    Given a reference entity with a text attribute with an url validation rule
    And a record belonging to this reference entity with a value of "https://www.akeneo.com/" for the text attribute
    When the user updates the text attribute of the record to "http://akeneo.com/"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the text value "http://akeneo.com/" for this attribute

  @acceptance-back
  Scenario: Updating a text with regular expression with an incompatible value
    Given a reference entity with a text attribute with a regular expression validation rule like "/\d+\|\d+/"
    And a record belonging to this reference entity with a value of "15|25" for the text attribute
    When the user updates the text attribute of the record to "15-25"
    Then there should be a validation error on the property text attribute with message "The text is incompatible with the regular expression "/\d+\|\d+/""

  @acceptance-back
  Scenario: Updating a text with regular expression with an compatible value
    Given a reference entity with a text attribute with a regular expression validation rule like "/\d+\|\d+/"
    And a record belonging to this reference entity with a value of "15|25" for the text attribute
    When the user updates the text attribute of the record to "15|25"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the text value "15|25" for this attribute

  # Image
  @acceptance-back
  Scenario: Updating the image value of a record by uploading a new one
    Given a reference entity with an image attribute
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record with a valid uploaded file
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the valid image for this attribute

  @acceptance-back
  Scenario: Emptying the file value of a record
    Given a reference entity with an image attribute
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user removes an image from the record for this attribute
    Then there is no exception thrown
    And there is no violations errors
    And the record should not have any image for this attribute

  @acceptance-back
  Scenario: Updating the image value of a record with an invalid uploaded file path
    Given a reference entity with an image attribute
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record to an invalid uploaded file path
    Then there should be a validation error on the property image attribute with message "This value should be of type string."

  @acceptance-back
  Scenario: Updating the image value of a record with an invalid stored file path
    Given a reference entity with an image attribute
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record to an invalid stored file path
    Then there should be a validation error on the property image attribute with message "This value should be of type string."

  @acceptance-back
  Scenario: Updating the image value of a record with an invalid uploaded original filename
    Given a reference entity with an image attribute
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record to an invalid uploaded file name
    Then there should be a validation error on the property image attribute with message "This value should be of type string."

  @acceptance-back
  Scenario: Updating the image value of a record with an invalid stored original filename
    Given a reference entity with an image attribute
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record to an invalid stored file name
    Then there should be a validation error on the property image attribute with message "This value should be of type string."

  @acceptance-back
  Scenario: Updating the image value of a record with an invalid stored size
    Given a reference entity with an image attribute
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record to an invalid stored file size
    Then there should be a validation error on the property image attribute with message "This value should be of type int."

  @acceptance-back
  Scenario: Updating the image value of a record with an invalid stored mime type
    Given a reference entity with an image attribute
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record to an invalid stored file mime type
    Then there should be a validation error on the property image attribute with message "This value should be of type string."

  @acceptance-back
  Scenario: Updating the image value of a record with an invalid stored extension
    Given a reference entity with an image attribute
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record to an invalid stored file extension
    Then there should be a validation error on the property image attribute with message "This value should be of type string."

  @acceptance-back
  Scenario: Updating the image value of a record with an uploaded file having an extension not allowed
    Given a reference entity with an image attribute allowing only files with extension png
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record with an uploaded gif file which is a denied extension
    Then there should be a validation error on the property image attribute with message '".gif" files are not allowed for this attribute. Allowed extensions are: png'

  @acceptance-back
  Scenario: Updating the image value of a record with an stored file having an extension not allowed
    Given a reference entity with an image attribute allowing only files with extension png
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record with a stored gif file which is a denied extension
    Then there should be a validation error on the property image attribute with message '".gif" files are not allowed for this attribute. Allowed extensions are: png'

  @acceptance-back
  Scenario: Updating the image value of a record with a file having an extension not allowed
    Given a reference entity with an image attribute allowing only files with extension png
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record with an uploaded png file
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the valid image for this attribute

  @acceptance-back
  Scenario: Updating the image value of a record with an uploaded file bigger than the limit
    Given a reference entity with an image attribute having a max file size of 15ko
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record with a bigger uploaded file than the limit
    Then there should be a validation error on the property image attribute with message "The file exceeds the max file size set for the attribute."

  @acceptance-back
  Scenario: Updating the image value of a record with a stored file bigger than the limit
    Given a reference entity with an image attribute having a max file size of 15ko
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record with a bigger stored file than the limit
    Then there should be a validation error on the property image attribute with message "The file exceeds the max file size set for the attribute."

  @acceptance-back
  Scenario: Updating the image value of a record with an invalid mime type
    Given a reference entity with an image attribute allowing only files with extension png
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record with a stored gif file which is a denied extension
    Then there should be a validation error on the property image attribute with message '".gif" files are not allowed for this attribute. Allowed extensions are: png'

  @acceptance-back
  Scenario: Updating the image value of a record with a file smaller than the limit
    Given a reference entity with an image attribute having a max file size of 15ko
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record with a smaller file than the limit
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the valid image for this attribute

  # Record value
  @acceptance-back
  Scenario: Updating the record value of a record
    Given a reference entity with a record attribute
    And a record belonging to this reference entity with a value of "ikea" for the record attribute
    When the user updates the record attribute of the record to "made"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the record value "made" for this attribute

  @acceptance-back
  Scenario: Updating the record value of a record with an invalid record value
    Given a reference entity with a record attribute
    And a record belonging to this reference entity with a value of "ikea" for the record attribute
    When the user updates the record attribute of the record to an invalid record value
    Then an exception is thrown with message "There was no factory found to create the edit record value command of the attribute "brand_linked_designer_fingerprint""

  @acceptance-back
  Scenario: Updating the record value of a record with a non-existent record
    Given a reference entity with a record attribute
    And a record belonging to this reference entity with a value of "ikea" for the record attribute
    When the user tries to update the record attribute of the record with an unknown value
    Then there should be a validation error on the property record attribute with message "The record "unknown_brand" was not found."

  # Record collection value
  @acceptance-back
  Scenario: Updating the record collection value of a record
    Given a reference entity with a record collection attribute
    And a record belonging to this reference entity with values of "ikea,made" for the record collection attribute
    When the user updates the record collection attribute of the record to "stork,cogip"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the record collection value "stork,cogip" for this attribute

  @acceptance-back
  Scenario: Updating the record collection value of a record with an invalid record value
    Given a reference entity with a record collection attribute
    And a record belonging to this reference entity with values of "ikea,made" for the record collection attribute
    When the user updates the record collection attribute of the record to an invalid record value
    Then an exception is thrown with message "There was no factory found to create the edit record value command of the attribute "brand_linked_designer_fingerprint""

  @acceptance-back
  Scenario: Updating the record collection value of a record with non-existent records
    Given a reference entity with a record collection attribute
    And a record belonging to this reference entity with values of "ikea,made" for the record collection attribute
    When the user updates the record collection attribute of the record with unknown values
    Then there should be a validation error on the property record attribute with message "The records "unknown_brand,wrong_brand" were not found."

  # Option value
  @acceptance-back
  Scenario: Updating the option value of a record
    Given a reference entity with an option attribute
    And a record belonging to this reference entity with values of "green" for the option attribute
    When the user updates the option attribute of the record to "red"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the option value "red" for this attribute

  @acceptance-back
  Scenario: Updating the option value of a record with non-existent option
    Given a reference entity with an option attribute
    And a record belonging to this reference entity with values of "green" for the option attribute
    When the user updates the option attribute of the record to "blue"
    Then there should be a validation error on the property option attribute with message "The option with code "blue" does not exist for this attribute"
    And the record should have the option value "green" for this attribute

  # Option collection value
  @acceptance-back
  Scenario: Updating the option collection value of a record
    Given a reference entity with an option collection attribute
    And a record belonging to this reference entity with values of "vodka, whisky" for the option collection attribute
    When the user updates the option collection attribute of the record to "vodka, rhum"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the option collection value "vodka, rhum" for this attribute

  @acceptance-back
  Scenario: Updating the option collection value of a record with non-existent option
    Given a reference entity with an option collection attribute
    And a record belonging to this reference entity with values of "vodka, whisky" for the option collection attribute
    When the user updates the option collection attribute of the record to "vodka, water"
    Then there should be a validation error on the property option collection attribute with message "The following option codes don't exist for this attribute : "water""
    And the record should have the option collection value "vodka, whisky" for this attribute

  @acceptance-front
  Scenario: Display a record labels in the edit form
    Given a valid record
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | true |
    When the user ask for the record
    Then the record should be:
      | labels                                    |
      | {"en_US": "", "fr_FR": "Philippe Starck"} |

  @acceptance-front
  Scenario: Updating a record details
    Given a valid record
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | true |
    When the user saves the valid record
    Then the user should see a success message on the edit page

  @acceptance-front
  Scenario: User can't update a record details without the edit rights
    Given a valid record
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | false |
    Then the user cannot save the record

  @acceptance-front
  Scenario: Updating a record with a simple text value
    Given a valid record
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | true |
    When the user saves the valid record with a simple text value
    Then the user should see a success message on the edit page

  @acceptance-front
  Scenario: Updating a record with an invalid simple text value
    Given a valid record
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | true |
    When the user saves the valid record with an invalid simple text value
    Then the user should see the validation error on the edit page : "This value is not a valid URL."

  @acceptance-front
  Scenario: User can't update a simple text value without the edit rights
    Given a valid record
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | false |
    Then the user cannot update the simple text value

  @acceptance-front
  Scenario: Updating a record with a simple option value
    Given a valid record with an option attribute
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | true |
    When the user saves the valid record with a simple option value
    Then the user should see a success message on the edit page

  @acceptance-front
  Scenario: Updating a record with an invalid simple option value
    Given a valid record with an option attribute
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | true |
    When the user saves the valid record with an invalid simple option value
    Then the user should see the validation error on the edit page : "The option with code \"red\" does not exist for this attribute"

  @acceptance-front
  Scenario: User can't update a simple option value without the edit rights
    Given a valid record with an option attribute
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | false |
    Then the user cannot update the simple option value

  @acceptance-front
  Scenario: Updating a record with a multiple option value
    Given a valid record with an option collection attribute
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | true |
    When the user saves the valid record with a multiple option value
    Then the user should see a success message on the edit page

  @acceptance-front
  Scenario: Updating a record with an invalid multiple option value
    Given a valid record with an option collection attribute
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | true |
    When the user saves the valid record with an invalid multiple option value
    Then the user should see the validation error on the edit page : "The option with code \"red\" does not exist for this attribute"

  @acceptance-front
  Scenario: User can't update a multiple option value without the edit rights
    Given a valid record with an option collection attribute
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | false |
    Then the user cannot update the multiple option value

  @acceptance-front
  Scenario: Display bullet point for the completeness when a required field isn't filled
    Given a valid record
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | true |
    When the user ask for the record
    Then the user should see a completeness bullet point on the required field: "website"
    When the user fill the "website" field with: "http://the-website.com"
    Then the user should not see a completeness bullet point on the required field: "website"

  @acceptance-front
  Scenario: Display completeness percentage on a record with required fields
    Given a valid record
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | true |
    When the user ask for the record
    Then the user should see the completeness percentage with a value of "0%"
    When the user fill the "website" field with: "http://the-website.com"
    Then the user should see the completeness percentage with a value of "100%"

  @acceptance-front
  Scenario: Updating a record with a single record linked
    Given a valid record with a reference entity single link attribute
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | true |
    When the user saves the valid record with a single record linked
    Then the user should see a success message on the edit page

  @acceptance-front
  Scenario: User can't update a single record linked value without the edit rights
    Given a valid record with a reference entity single link attribute
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | false |
    Then the user cannot update the single record linked value

  @acceptance-front
  Scenario: Updating a record with a multiple record linked
    Given a valid record with a reference entity multiple link attribute
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | true |
    When the user saves the valid record with a multiple record linked
    Then the user should see a success message on the edit page

  @acceptance-front
  Scenario: User can't update a multiple record linked value without the edit rights
    Given a valid record with a reference entity multiple link attribute
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_edit | false |
    Then the user cannot update the multiple record linked value

#  Todo : Fix random call for the preview image
#  @acceptance-front
#  Scenario: Updating a record with an image value
#    Given a valid record
#    When the user updates the valid record with an image value
#    Then the user should see a success message after the update record
#
#  @acceptance-front
#  Scenario: Updating a record with an invalid image value
#    Given a valid record
#    When the user saves the valid record with an invalid image value
#    Then the user should see the validation error after the update record : "This value is not a valid URL."
