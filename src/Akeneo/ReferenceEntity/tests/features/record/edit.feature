Feature: Edit an record
  In order to update the information of an record
  As a user
  I want see the details of an record and update them

  @acceptance-back
  Scenario: Updating a record label
    Given an referenceEntity and a record with french label "My label"
    When the user updates the french label to "My updated label"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the french label "My updated label"

  # ValuePerChannel / ValuePerLocale
  @acceptance-back
  Scenario: Updating a localizable value of a record
    Given an reference entity with a localizable attribute
    And a record belonging to this reference entity with a value for the french locale
    When the user updates the attribute of the record for the french locale
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the updated value for this attribute and the french locale

  @acceptance-back
  Scenario: Updating a scopable value of a record
    Given an reference entity with a scopable attribute
    And a record belonging to this reference entity with a value for the ecommerce channel
    When the user updates the attribute of the record for the ecommerce channel
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the updated value for this attribute and the ecommerce channel

  @acceptance-back
  Scenario: Updating a scopable value of a record with an invalid channel
    Given an reference entity with a scopable attribute
    And a record belonging to this reference entity with a value for the ecommerce channel
    When the user updates the attribute of the record with an invalid channel
    Then there should be a validation error on the property text attribute with message "This value should be of type string."

#  Todo: Scenario to activate for the import,exports/API
#  @acceptance-back
#  Scenario: Updating a scopable and localizable value of a record
#    Given an reference entity with a scopable and localizable attribute
#    And a record belonging to this reference entity with a value for the ecommerce channel and french locale
#    When the user updates the attribute of the record for the ecommerce channel and french locale
#    Then there is no exception thrown
#    And there is no violations errors
#    And the record should have the updated value for this attribute and the ecommerce channel and the french locale

  # Text
  @acceptance-back
  Scenario: Updating the text value of a record
    Given an reference entity with a text attribute
    And a record belonging to this reference entity with a value of "Philippe stark" for the text attribute
    When the user updates the text attribute of the record to "Stark"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the text value "Stark" for this attribute

  @acceptance-back
  Scenario: Emptying a text value
    Given an reference entity with a text attribute
    And a record belonging to this reference entity with a value of "Philippe stark" for the text attribute
    When the user empties the text attribute of the record
    Then there is no exception thrown
    And there is no violations errors
    And the record should have an empty value for this attribute

  @acceptance-back
  Scenario: Setting a text value to empty string empties this value
    Given an reference entity with a text attribute
    And a record belonging to this reference entity with a value of "Philippe stark" for the text attribute
    When the user updates the text attribute of the record to ""
    Then there is no exception thrown
    And there is no violations errors
    And the record should have an empty value for this attribute

  @acceptance-back
  Scenario: Updating the text value of a record with an invalid value type
    Given an reference entity with a text attribute
    And a record belonging to this reference entity with a value of "Philippe stark" for the text attribute
    When the user updates the text attribute of the record to an invalid value type
    Then there should be a validation error on the property text attribute with message "This value should be of type string."

  @acceptance-back
  Scenario: Updating the text value with more characters than the attribute's max length
    Given an reference entity with a text attribute with max length 10
    And a record belonging to this reference entity with a value of "Philippe" for the text attribute
    When the user updates the text attribute of the record to "Philippe Starck, né le 18 janvier 1949 à Paris"
    Then there should be a validation error on the property text attribute with message "This value is too long. It should have 10 characters or less."

  @acceptance-back
  Scenario: Updating the text value with less characters than the attribute's max length
    Given an reference entity with a text attribute with max length 10
    And a record belonging to this reference entity with a value of "Philippe" for the text attribute
    When the user updates the text attribute of the record to "Didier"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the text value "Didier" for this attribute

  @acceptance-back
  Scenario: Updating an email with an invalid email value
    Given an reference entity with a text attribute with an email validation rule
    And a record belonging to this reference entity with a value of "jean-pierre@dummy.com" for the text attribute
    When the user updates the text attribute of the record to "Hello my name is jean-pierre."
    Then there should be a validation error on the property text attribute with message "This value is not a valid email address."

  @acceptance-back
  Scenario: Updating an email with an valid email value
    Given an reference entity with a text attribute with an email validation rule
    And a record belonging to this reference entity with a value of "jean-pierre@dummy.com" for the text attribute
    When the user updates the text attribute of the record to "didier@dummy.com"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the text value "didier@dummy.com" for this attribute

  @acceptance-back
  Scenario: Updating an url with an invalid url value
    Given an reference entity with a text attribute with an url validation rule
    And a record belonging to this reference entity with a value of "https://www.akeneo.com/" for the text attribute
    When the user updates the text attribute of the record to "htt://akeneo.com/"
    Then there should be a validation error on the property text attribute with message "This value is not a valid URL."

  @acceptance-back
  Scenario: Updating an url with an valid url value
    Given an reference entity with a text attribute with an url validation rule
    And a record belonging to this reference entity with a value of "https://www.akeneo.com/" for the text attribute
    When the user updates the text attribute of the record to "http://akeneo.com/"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the text value "http://akeneo.com/" for this attribute

  @acceptance-back
  Scenario: Updating a text with regular expression with an incompatible value
    Given an reference entity with a text attribute with a regular expression validation rule like "/\d+\|\d+/"
    And a record belonging to this reference entity with a value of "15|25" for the text attribute
    When the user updates the text attribute of the record to "15-25"
    Then there should be a validation error on the property text attribute with message "The text is incompatible with the regular expression "/\d+\|\d+/""

  @acceptance-back
  Scenario: Updating a text with regular expression with an compatible value
    Given an reference entity with a text attribute with a regular expression validation rule like "/\d+\|\d+/"
    And a record belonging to this reference entity with a value of "15|25" for the text attribute
    When the user updates the text attribute of the record to "15|25"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the text value "15|25" for this attribute

  # Image
  @acceptance-back
  Scenario: Updating the image value of a record
    Given an reference entity with an image attribute
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record with a valid file
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the valid image for this attribute

  @acceptance-back
  Scenario: Emptying the file value of a record
    Given an reference entity with an image attribute
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user removes an image from the record for this attribute
    Then there is no exception thrown
    And there is no violations errors
    And the record should not have any image for this attribute

  @acceptance-back
  Scenario: Updating the image value of a record with an invalid file path
    Given an reference entity with an image attribute
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record to an invalid file path
    Then there should be a validation error on the property image attribute with message "This value should be of type string."

  @acceptance-back
  Scenario: Updating the image value of a record with an invalid original filename
    Given an reference entity with an image attribute
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record to an invalid file name
    Then there should be a validation error on the property image attribute with message "This value should be of type string."

  @acceptance-back
  Scenario: Updating the image value of a record with a file having an extension not allowed
    Given an reference entity with an image attribute allowing only files with extension png
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record with a gif file which is a denied extension
    Then there should be a validation error on the property image attribute with message '".gif" files are not allowed for this attribute. Allowed extensions are: png'

  @acceptance-back
  Scenario: Updating the image value of a record with a file having an extension not allowed
    Given an reference entity with an image attribute allowing only files with extension png
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record with a png file
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the valid image for this attribute

  @acceptance-back
  Scenario: Updating the image value of a record with a file bigger than the limit
    Given an reference entity with an image attribute having a max file size of 15ko
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record with a bigger file than the limit
    Then there should be a validation error on the property image attribute with message "The file exceeds the max file size set for the attribute."

  @acceptance-back
  Scenario: Updating the image value of a record with a file smaller than the limit
    Given an reference entity with an image attribute having a max file size of 15ko
    And a record belonging to this reference entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record with a smaller file than the limit
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the valid image for this attribute

  @acceptance-front
  Scenario: Updating a record labels
    Given a valid record
    When the user ask for the record
    Then the record should be:
      | labels                                    |
      | {"en_US": "", "fr_FR": "Philippe Starck"} |
