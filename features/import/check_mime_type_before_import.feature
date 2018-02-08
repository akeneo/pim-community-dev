Feature:
  In order to prevent importing invalid files
  As a product manager
  I need to see that mime type has been checked

  Background:
    Given the "footwear" catalog configuration

  @unstable
  Scenario: Import a file that is not a csv file
    Given I am on the "csv_footwear_product_import" import job edit page
    And I fill in the following information:
      | File              | file.csv |
      | Delimiter         | \|       |
      | Enclosure         | '        |
      | Escape            | \\       |
      | Categories column | cat      |
      | Family column     | fam      |
      | Groups column     | grp      |
    And I press the "Save" button
    When I am on the "csv_footwear_product_import" import job page
    And I upload and import an invalid file "akeneo2.jpg"
    Then I should see the flash message "The file extension is not allowed (allowed extensions: csv, zip)."
