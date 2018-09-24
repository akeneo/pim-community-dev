Feature: Edit an record
  In order to update the information of an record
  As a user
  I want see the details of an record and update them

  @acceptance-back
  Scenario: Updating the text value of a record
    Given an enriched entity with a text attribute
    And a record belonging to this enriched entity with a value of "Philippe stark" for the text attribute
    When the user updates the text attribute of the record to "Stark"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the text value "Stark" for this attribute

  @acceptance-back
  Scenario: Updating the text value with more characters than the attribute's max length
    Given an enriched entity with a text attribute with max length 10
    And a record belonging to this enriched entity with a value of "Philippe stark" for the text attribute
    When the user updates the text attribute of the record to "Philippe Starck, né le 18 janvier 1949 à Paris, est un créateur, designeur et décorateur d'intérieur français."
    Then there should be a validation error on the property text attribute with message "This value is too long. It should have 10 characters or less."

  @acceptance-back
  Scenario: Updating the text value with more characters than the attribute's max length
    Given an enriched entity with a text attribute with max length 10
    And a record belonging to this enriched entity with a value of "Philippe stark" for the text attribute
    When the user updates the text attribute of the record to "Philippe Starck, né le 18 janvier 1949 à Paris, est un créateur, designeur et décorateur d'intérieur français."
    Then there should be a validation error on the property text attribute with message "This value is too long. It should have 10 characters or less."

  @acceptance-back
  Scenario: Updating the image value of a record
    Given an enriched entity with an image attribute
    And a record belonging to this enriched entity with a the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record to "updated_picture.jpeg"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the image "updated_picture.jpeg" for this attribute

  @acceptance-front
  Scenario: Updating a record labels
    Given a valid record
    When the user ask for the record
    Then the record should be:
      | labels                                    |
      | {"en_US": "", "fr_FR": "Philippe Starck"} |
