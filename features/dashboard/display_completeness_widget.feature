Feature: Display completeness widget
  In order to have a quick overview of products almost ready to be exported but incomplete
  As a regular user
  I need to be able to see on the dashboard a completeness widget that present channels and locales completenesses

  Scenario: Display completeness widget
    Given a "apparel" catalog configuration
    And the following products:
      | sku  | family  | name-fr_FR      | name-en_US      | name-en_GB      | description-en_US-ecommerce | description-fr_FR-ecommerce | price                 | size    | color | manufacturer     | material | categories                       |
      | sku1 | tshirts | T shirt Batman  |                 |                 | Batman                      | Batman                      | 10 USD, 6 GBP, 15 EUR | size_XS | black | american_apparel | cotton   | 2014_collection                  |
      | sku2 | tshirts |                 |                 |                 | Superman                    |                             | 10 USD, 6 GBP, 15 EUR | size_S  | blue  | american_apparel | cotton   | 2014_collection                  |
      | sku3 | tshirts | Tshirt Iron Man | Tshirt Iron Man | Tshirt Iron Man | Iron Man                    | Iron Man                    | 10 USD, 6 GBP, 15 EUR | size_S  | blue  | american_apparel | cotton   | 2013_collection, 2015_collection |
    And I launched the completeness calculator
    And I am logged in as "Mary"
    When I am on the dashboard page
    Then I should see "Completeness Over Channels and Locales"
    And completeness of "Ecommerce" should be "13%"
    And "German (Germany)" completeness of "Ecommerce" should be "0%"
    And "English (United Kingdom)" completeness of "Ecommerce" should be "0%"
    And "English (United States)" completeness of "Ecommerce" should be "0%"
    And "French (France)" completeness of "Ecommerce" should be "50%"
    And completeness of "Tablet" should be "100%"
    And "English (United Kingdom)" completeness of "Tablet" should be "100%"
    And "English (United States)" completeness of "Tablet" should be "100%"
    And completeness of "Print" should be "0%"
    And "German (Germany)" completeness of "Print" should be "0%"
    And "English (United States)" completeness of "Print" should be "0%"
