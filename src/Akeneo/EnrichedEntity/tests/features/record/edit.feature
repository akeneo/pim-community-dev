Feature: Edit an record
  In order to update the information of an record
  As a user
  I want see the details of an record and update them

  @acceptance-front
  Scenario: Updating a record labels
    Given a valid record
    When the user ask for the record
    Then the record should be:
      | labels                                    |
      | {"en_US": "", "fr_FR": "Philippe Starck"} |
