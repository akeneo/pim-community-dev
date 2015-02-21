@javascript @info https://akeneo.atlassian.net/browse/PIM-3331
Feature: Create product drafts for new attributes added to the product
  In order to be able to propose changes to product data for new attributes
  As a redactor
  I need to be able to propose changes to a newly added attribute to the product's family

  Scenario: Create product draft for a new attribute of the family
    Given a "clothing" catalog configuration
    And the following product:
      | sku    | family | categories        |
      | tshirt | tees   | summer_collection |
    And I am logged in as "Mary"
    Given I am on the "tees" family page
    And I visit the "Attributes" tab
    And I add available attribute Comment
    When I am on the "tshirt" product page
    And I visit the "Other" group
    And I change the Comment to "tshirt"
    And I save the product
    Then I should see "Send for approval"

  Scenario: Save a product draft with empty custom attribute values
    Given a "clothing" catalog configuration
    And the following family:
      | code           | attributes                                                    |
      | security_vests | sku,name,length,price,side_view,video,size,weather_conditions |
    And I am logged in as "Mary"
    And the following csv file to import:
      """
      sku;family;categories
      bullet_proof_vest;security_vests;jackets
      """
    And the following job "clothing_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    And I am on the "bullet_proof_vest" product page
    And I save the product
    Then I should see "Send for approval"
