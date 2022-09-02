@javascript
Feature: Display available field options
  In order to create a read only attribute
  As a product manager
  I need to see and manage the option 'Read only'

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku       | family  |
      | my-jacket | jackets |
    And I am logged in as "Julia"

  @unstable
  Scenario: Successfully update a read only attribute through an import
    Given I am on the "description" attribute page
    And I check the "Read only" switch
    And I save the "attribute"
    And I should see the text "Description"
    And the following CSV file to import:
      """
      sku;family;groups;categories;name-en_US;description-en_US-tablet
      jacket;hoodies;;tees;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      """
    And the following job "csv_clothing_product_import" configuration:
      | storage | {"type": "local", "file_path": "%file to import%"} |
    When I am on the "csv_clothing_product_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_product_import" job to finish
    Then the english tablet description of "jacket" should be "dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est"
