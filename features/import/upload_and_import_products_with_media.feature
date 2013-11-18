@javascript
Feature: Upload and import products with media
  In order to easily import existing product media
  As Julia
  I need to be able to upload and import products along with media

  Scenario: Successfully upload and import an archive
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the "footwear_product_import" import job page
    When I upload and import the file "caterpillar_import.zip"
    And I wait for the job to finish
    Then there should be 3 products
    And product "CAT-001" should be enabled
    And product "CAT-002" should be enabled
    And product "CAT-003" should be enabled
    And the product "CAT-001" should have the following values:
      | categories               | winter_collection |
      | family                   | boots             |
      | name-EN_US               | Caterepillar 1    |
      | description-en_US-mobile | Model 1 boots     |
      | side_view                | cat_001.png       |
    And the product "CAT-002" should have the following values:
      | categories               | winter_collection |
      | family                   | boots             |
      | name-EN_US               | Caterepillar 2    |
      | description-en_US-mobile | Model 2 boots     |
      | side_view                | cat_002.png       |
    And the product "CAT-003" should have the following values:
      | categories               | winter_collection |
      | family                   | boots             |
      | name-EN_US               | Caterepillar 3    |
      | description-en_US-mobile | Model 3 boots     |
      | side_view                | cat_003.png       |
