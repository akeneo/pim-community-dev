@javascript
Feature: Export products according to a date
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to a date

  Scenario: Export only the products updated by the UI since the last export
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv                                                                                                                  |
      | filters  | {"structure":{"locales":["en_US"],"scope":"mobile"},"data":[{"field": "updated", "operator": "SINCE LAST JOB", "value": "csv_footwear_product_export"}]} |
    And the following products:
      | sku      | family   | categories        | price          | size | color | name-en_US |
      | SNKRS-1B | sneakers | summer_collection | 50 EUR, 70 USD | 45   | black | Model 1    |
      | SNKRS-1R | sneakers | summer_collection | 50 EUR, 70 USD | 45   | red   | Model 1    |
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
      SNKRS-1B;summer_collection;black;;1;sneakers;;;;"Model 1";50.00;70.00;;;45;;
      SNKRS-1R;summer_collection;red;;1;sneakers;;;;"Model 1";50.00;70.00;;;45;;
      """
    When I edit the "SNKRS-1B" product
    And I change the "Weather conditions" to "Hot"
    And I save the product
    And I should not see the text "There are unsaved changes"
    And I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      sku;categories;enabled;family;groups;color;description-en_US-mobile;lace_color;manufacturer;name-en_US;PACK-groups;PACK-products;price-EUR;price-USD;rating;side_view;size;SUBSTITUTION-groups;SUBSTITUTION-products;top_view;UPSELL-groups;UPSELL-products;weather_conditions;X_SELL-groups;X_SELL-products
      SNKRS-1B;summer_collection;1;sneakers;;black;;;;"Model 1";;;50.00;70.00;;;45;;;;;;hot;;

      """

  Scenario: Update the updated time condition field
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job edit page
    Then I should see the text "Content"
    When I follow "Content"
    Then I filter by "updated" with operator "No date condition" and value ""
    And I filter by "updated" with operator "Updated products over the last n days (e.g. 6)" and value "12"
    And I filter by "updated" with operator "Updated products since this date" and value ""
    And I filter by "updated" with operator "Updated products since last export" and value ""
    And I should see the text "There are unsaved changes"
    And I press "Save"

  @skip
  Scenario: Error management when the updated time condition field is updated
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job edit page
    And I follow "Content"
    When I filter exported products by operator "Updated products since this date" and value ""
    And I press "Save"
    Then I should be on the "csv_footwear_product_export" export job edit page
    And I should see a validation error "The date should not be blank."
    When I filter exported products by operator "Updated products over the last n days (e.g. 6)" and value ""
    And I press "Save"
    Then I should be on the "csv_footwear_product_export" export job edit page
    And I should see a validation error "The date should not be blank."
    When I filter exported products by operator "Updated products over the last n days (e.g. 6)" and value "ten days"
    And I press "Save"
    Then I should be on the "csv_footwear_product_export" export job edit page
    And I should see a validation error "This value is not valid."
    When I filter exported products by operator "Updated products over the last n days (e.g. 6)" and value "-12"
    And I press "Save"
    Then I should be on the "csv_footwear_product_export" export job edit page
    And I should see a validation error "This value should be 0 or more."

  @jira https://akeneo.atlassian.net/browse/PIM-6038
  Scenario: Export only the products updated by an import since the last export
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv                                                                                                                  |
      | filters  | {"structure":{"locales":["en_US"],"scope":"mobile"},"data":[{"field": "updated", "operator": "SINCE LAST JOB", "value": "csv_footwear_product_export"}]} |
    And the following products:
      | sku      | family   | categories        | price          | size | color | name-en_US |
      | SNKRS-1B | sneakers | summer_collection | 50 EUR, 70 USD | 45   | black | Model 1    |
      | SNKRS-1R | sneakers | summer_collection | 50 EUR, 70 USD | 45   | red   | Model 1    |
    And the following CSV file to import:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
      SNKRS-1B;summer_collection;black;;1;sneakers;;;;"Model 1";50.00;70.00;;;45;;hot
      SNKRS-1R;summer_collection;red;;1;sneakers;;;;"Model 1";50.00;70.00;;;45;;
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
      SNKRS-1B;summer_collection;black;;1;sneakers;;;;"Model 1";50.00;70.00;;;45;;
      SNKRS-1R;summer_collection;red;;1;sneakers;;;;"Model 1";50.00;70.00;;;45;;
      """
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    And I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
      SNKRS-1B;summer_collection;black;;1;sneakers;;;;"Model 1";50.00;70.00;;;45;;hot
      """
