Feature: Have meaningful information about the state of the grid
  Scenario: When the grid is loading some new data, I should be aware of it
    Given the channels "ecommerce,mobile"
    And the following product labels:
      | identifier | en_US         | fr_FR             | de_DE           |
      | shirt      | My nice shirt | Une chemise sympa | Ein shon Hemd   |
    And a product grid is displayed
    When I switch the channel to "mobile"
    Then I should see the loading indicator
    Then I should not see the loading indicator

  Scenario: Display the number of product displayed in the current grid
    Given the channels "ecommerce,mobile"
    And the following product labels:
      | identifier | en_US         | fr_FR             | de_DE           |
      | shirt      | My nice shirt | Une chemise sympa | Ein shon Hemd   |
    When a product grid is displayed
    Then I should see that we have 1 results
    And the following product labels:
      | identifier | en_US           | fr_FR            | de_DE            |
      | shirt      | My nice product | Un produit sympa | Ein shon produkt |
      | boot       | My nice product | Un produit sympa | Ein shon produkt |
    When I switch the channel to "mobile"
    Then I should see that we have 2 results
