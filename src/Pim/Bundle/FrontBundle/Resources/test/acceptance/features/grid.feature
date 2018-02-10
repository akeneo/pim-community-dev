Feature: Grid is working properly
  Scenario: I can switch the locale
    Given a product grid
    Then the locale should be "en_US"
    And the product "label" of "17851719" should be "Avision @V2800 in english"
    And I switch the locale to "fr_FR"
    Then the locale should be "fr_FR"
    And the product "label" of "17851719" should be "Avision @V2800 in french"
