Feature: Export products according to a date
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products according to a date

  @javascript
  Scenario: Export only the products updated since the last export
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_product_export" configuration:
      | filePath               | %tmp%/product_export/product_export.csv |
      | updated_since_strategy | last_export                             |
    And the following products:
      | sku      | family   | categories        | price          | size | color    | name-en_US |
      | SNKRS-1B | sneakers | summer_collection | 50 EUR, 70 USD | 45   | black    | Model 1    |
      | SNKRS-1R | sneakers | summer_collection | 50 EUR, 70 USD | 45   | red      | Model 1    |
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
    And I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;PACK-groups;PACK-products;price-EUR;price-USD;rating;side_view;size;SUBSTITUTION-groups;SUBSTITUTION-products;top_view;UPSELL-groups;UPSELL-products;weather_conditions;X_SELL-groups;X_SELL-products
      SNKRS-1B;summer_collection;black;;1;sneakers;;;;"Model 1";;;50.00;70.00;;;45;;;;;;hot;;
      """

  @javascript
  Scenario: Update the updated time condition field
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job edit page
    And I follow "Content"
    Then I should not see the "updated since date" element in the filter "Updated time condition"
    Then I should not see the "updated since period" element in the filter "Updated time condition"
    When I filter by "Updated time condition" with operator "Updated products since the defined date" with value "05/25/2016"
    And I press "Save"
    Then I should be on the "csv_footwear_product_export" export job page
    When I follow "Content"
    Then the filter "Updated time condition" should contain operator "Updated products since the defined date" with value "05/25/2016"
    When I am on the "csv_footwear_product_export" export job edit page
    And I follow "Content"
    And I filter by "Updated time condition" with operator "Updated products since the last n days" with value "10"
    And I press "Save"
    Then I should be on the "csv_footwear_product_export" export job page
    When I follow "Content"
    Then the filter "Updated time condition" should contain operator "Updated products since the last n days" with value "10"

  @javascript
  Scenario: Error management when the updated time condition field is updated
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job edit page
    And I follow "Content"
    When I filter by "Updated time condition" with operator "Updated products since the defined date" with value ""
    And I press "Save"
    Then I should be on the "csv_footwear_product_export" export job edit page
    And I should see a validation error "The date must not be empty"
    When I filter by "Updated time condition" with operator "Updated products since the last n days" with value ""
    And I press "Save"
    Then I should be on the "csv_footwear_product_export" export job edit page
    And I should see a validation error "This value should be blank"
    When I filter by "Updated time condition" with operator "Updated products since the last n days" with value "ten days"
    And I press "Save"
    Then I should be on the "csv_footwear_product_export" export job edit page
    And I should see a validation error "This value is not valid."

  @javascript
  Scenario: Export only the products updated since a defined date
    Given a "footwear" catalog configuration
    And the following products:
      | sku      | family   | categories        | price          | size | color    | name-en_US |
      | SNKRS-1B | sneakers | summer_collection | 50 EUR, 70 USD | 45   | black    | Model 1    |
      | SNKRS-1R | sneakers | summer_collection | 50 EUR, 70 USD | 45   | red      | Model 1    |
    And the following job "csv_footwear_product_export" configuration:
      | filePath               | %tmp%/product_export/product_export.csv |
      | updated_since_strategy | since_date                              |
      | updated_since_date     | 2016-04-25                              |
      | locales                | en_US                                   |
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
      """
      sku;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;PACK-groups;PACK-products;price-EUR;price-USD;rating;side_view;size;SUBSTITUTION-groups;SUBSTITUTION-products;top_view;UPSELL-groups;UPSELL-products;weather_conditions;X_SELL-groups;X_SELL-products
      SNKRS-1B;summer_collection;black;;1;sneakers;;;;"Model 1";50.00;70.00;;;45;;
      SNKRS-1R;summer_collection;red;;1;sneakers;;;;"Model 1";50.00;70.00;;;45;;
      """
    When the following job "csv_footwear_product_export" configuration:
      | updated_since_date | NOW +1 day |
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should be empty
