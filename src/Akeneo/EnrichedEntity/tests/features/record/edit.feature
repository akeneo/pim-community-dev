Feature: Edit an record
  In order to update the information of an record
  As a user
  I want see the details of an record and update them

  @acceptance-front
  Scenario: Updating a record labels
    Given the following record:
      | code   | enriched entity | labels                                 |
      | starck | designer        | {"en_US": "Starck", "fr_FR": "Starck"} |
    When the user updates the record "starck" of enriched entity "designer" with:
      | labels | {"fr_FR": "Philippe Starck", "en_US": "Philippe Starck"} |
    Then the record "starck" should be:
      | code   | labels                                                   |
      | starck | {"en_US": "Philippe Starck", "fr_FR": "Philippe Starck"} |

  @acceptance-front
  Scenario: Updating an record with unexpected backend answer
    Given the following record:
      | code   | enriched entity | labels                                 |
      | starck | designer        | {"en_US": "Starck", "fr_FR": "Starck"} |
    When the user changes the record "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the saved record "designer" will be:
      | identifier | labels                                      |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
    And the user saves the changes
    And the user shouldn't be notified that modification have been made
    And the user should see the saved notification
    And the record "designer" should be:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |

  @acceptance-front
  Scenario: Updating an record when the backend answer an error
    Given the following record:
      | code   | enriched entity | labels                                 |
      | starck | designer        | {"en_US": "Starck", "fr_FR": "Starck"} |
    When the user changes the record "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the record "designer" save will fail
    And the user saves the changes
    And the user should see the saved notification error

  @acceptance-front
  Scenario: Display updated edit form message
    Given the following record:
      | code   | enriched entity | labels                                 |
      | starck | designer        | {"en_US": "Starck", "fr_FR": "Starck"} |
    When the user changes the record "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the user should be notified that modification have been made
    And the saved record "designer" will be:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
