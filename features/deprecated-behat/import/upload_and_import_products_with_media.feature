@javascript
Feature: Upload and import products with media
  In order to easily import existing product media
  As a product manager
  I need to be able to upload and import products along with media

  Scenario: Successfully upload and import an archive
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the "footwear_product_import" import job page
    When I upload and import the file "caterpillar_import.zip"
    And I wait for the "footwear_product_import" job to finish
    Then there should be 3 products
    And product "CAT-001" should be enabled
    And product "CAT-002" should be enabled
    And product "CAT-003" should be enabled
    And the family of "CAT-001" should be "boots"
    And the family of "CAT-002" should be "boots"
    And the family of "CAT-003" should be "boots"
    And the category of "CAT-001" should be "winter_collection"
    And the category of "CAT-002" should be "winter_collection"
    And the category of "CAT-003" should be "winter_collection"
    And the english name of "CAT-001" should be "Caterpillar 1"
    And the english name of "CAT-002" should be "Caterpillar 2"
    And the english name of "CAT-003" should be "Caterpillar 3"
    And the english mobile description of "CAT-001" should be "Model 1 boots"
    And the english mobile description of "CAT-002" should be "Model 2 boots"
    And the english mobile description of "CAT-003" should be "Model 3 boots"
    And the product "CAT-001" should have the following values:
      | side_view | cat_001.png |
    And the product "CAT-002" should have the following values:
      | side_view | cat_002.png |
    And the product "CAT-003" should have the following values:
      | side_view | cat_003.png |

  @info https://akeneo.atlassian.net/browse/PIM-2090
  Scenario: Fail to launch an import through file upload when no file was selected
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the "footwear_product_import" import job page
    When I follow "Upload and import"
    And I execute javascript:
      """
      $('#pim_importexport_import_upload').submit()
      """
    Then I should see "You must select a file to upload"
