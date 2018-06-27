Feature: Edit an enriched entity
  In order to update the information of an enriched entity
  As a user
  I want see the details of an enriched entity and update them

  Background:
    Given the following enriched entity:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |

  @acceptance-back @acceptance-front
  Scenario: Updating an enriched entity labels
    When the user updates the enriched entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the enriched entity "designer" should be:
      | identifier | labels                                    |
      | designer   | {"en_US": "Stylist", "fr_FR": "Styliste"} |

  @acceptance-front
  Scenario: Updating an enriched entity with unexpected backend answer
    When the user changes the enriched entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the saved enriched entity "designer" will be:
      | identifier | labels                                      |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
    And the user saves the changes
    And the user shouldn't be notified that modification have been made
    And the enriched entity "designer" should be:
      | identifier | labels                                      |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |

  @acceptance-front
  Scenario: Display updated edit form message
    When the user changes the enriched entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the user should be notified that modification have been made
    And the saved enriched entity "designer" will be:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |

  @acceptance-front
  Scenario: Display confirmation dialog when the user click on a breadcrumb item and cancel it
    When the user changes the enriched entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    And the user click on a breadcrumb item
    Then the user should see the confirmation dialog and dismiss

  @acceptance-front
  Scenario: Display confirmation dialog when the user click on a breadcrumb item and confirm it
    When the user changes the enriched entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    And the user click on a breadcrumb item
    Then the user should see the confirmation dialog and accept

  @acceptance-front
  Scenario: Display confirmation dialog when the user goes on another page and cancel it
    When the user changes the enriched entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    And the user goes to "http://www.pim-test.com"
    Then the user should see the confirmation dialog and dismiss

  @acceptance-front
  Scenario: Display confirmation dialog when the user goes on another page and confirm it
    When the user changes the enriched entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    And the user goes to "http://www.pim-test.com"
    Then the user should see the confirmation dialog and accept
