Feature: Be able to change the display type of the grid
  Scenario: When I change the display style, the item list should be updated accordingly
    Given the channels "ecommerce"
    And the following products with labels:
      | identifier | en_US         | fr_FR             | de_DE           |
      | shirt      | My nice shirt | Une chemise sympa | Ein shon Hemd   |
    And a product grid is displayed
    When I switch the display type to "list"
    Then I should see 1 product row
    When I switch the display type to "gallery"
    Then I should see 1 product tile

