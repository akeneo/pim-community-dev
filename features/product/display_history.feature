Feature: Display the product history
  In order to know who, when and what changes has been made to a product
  As a user
  I need to have access to a product history

  @javascript
  Scenario: Display product updates
    Given a "Camera" product available in english
    And a "Bike" product available in english
    And the following product attributes:
      | product | label        |
      | Camera  | Brand        |
      | Camera  | Manufacturer |
      | Camera  | File upload  |
    And I am logged in as "admin"
    And the following product "Bike" updates:
      | action | loggedAt  | updatedBy | change                 |
      | update | yesterday | admin     | SKU: bike => motorbike |
    And I am on the "Camera" product page
    And I change the Brand to "Syno"
    And I save the product
    When I visit the "History" tab
    Then there should be 1 update
