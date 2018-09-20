Feature: Edit an record
  In order to update the information of an record
  As a user
  I want see the details of an record and update them

  @acceptance-front
  Scenario: Updating a record labels
    Given a valid record
    When the user ask for the record
    Given an invalid record
    When the user ask for the record
    # Given the following record:
    #   | code   | enriched entity | labels                                 |
    #   | starck | designer        | {"en_US": "Starck", "fr_FR": "Starck"} |
  #   When the user updates the record "starck" of enriched entity "designer" with:
  #     | labels | {"fr_FR": "Philippe Starck", "en_US": "Philippe Starck"} |
  #   Then the record "starck" should be:
  #     | code   | labels                                                   |
  #     | starck | {"en_US": "Philippe Starck", "fr_FR": "Philippe Starck"} |
