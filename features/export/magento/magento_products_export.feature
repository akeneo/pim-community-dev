@javascript
Feature: Magento product export
  In order to view products in Magento
  As an Administrator
  I need to be able to export my products to Magento

  Scenario: Successfully export products to Magento
    Given  a "complete_magento" catalog configuration
    And I launched the completeness calculator
    And I am logged in as "peter"
    When I am on the "magento_full_export" export job edit page
    And I fill in the "storeview" mapping:
      | fr_FR | fr_fr |
    And I fill in the "category" mapping:
      | Master catalog (default) | Default Category |
    And I press the "Save" button and I wait "60"s
    When I launch the export job
    Then I wait for the "magento_full_export" job to finish for "240"s and refresh
    When I am on the "magento_product_export" export job edit page
    And I fill in the "storeview" mapping:
      | fr_FR | fr_fr |
    And I fill in the "category" mapping:
      | Master catalog (default) | Default Category |
    And I press the "Save" button and I wait "60"s
    Then I launch the export job
    And I wait for the "magento_product_export" job to finish for "240"s and refresh
    Then I check if "products" were sent in Magento:
      | sku            | type         | attribute         | associated   | value                                      | store_view         |
      | sku-000        |              | name              |              | Product example                            | Default Store View |
      | sku-000        |              | name              |              | Exemple de produit                         | fr_fr              |
      | sku-000        |              | description       |              | Description                                | Default Store View |
      | sku-000        |              | description       |              | Description                                | fr_fr              |
      | sku-000        |              | short_description |              | Short description                          | Default Store View |
      | sku-000        |              | short_description |              | Courte description                         | fr_fr              |
      | sku-000        |              | price             |              | 50.00                                      | Default Store View |
      | sku-000        |              | price             |              | 50.00                                      | fr_fr              |
      | sku-000        |              | tax_class_id      |              | Shipping                                   | Default Store View |
      | sku-000        |              | tax_class_id      |              | Shipping                                   | fr_fr              |
      | sku-000        |              | weight            |              | 200                                        | Default Store View |
      | sku-000        |              | weight            |              | 200                                        | fr_fr              |
      | sku-000        |              | set_name          |              | Default                                    |                    |
      | sku-000        |              | categories        |              | notebooks                                  |                    |
      | sku-000        | simple       |                   |              |                                            |                    |
      | sku-000        |              |                   | cross_sell   | sku-001                                    |                    |
      | sku-000        |              |                   | cross_sell   | sku-002                                    |                    |
      | sku-001        |              | name              |              | Shirt product example                      | Default Store View |
      | sku-001        |              | name              |              | Exemple de produit chemise                 | fr_fr              |
      | sku-001        |              | description       |              | Shirt description                          | Default Store View |
      | sku-001        |              | description       |              | Description d'une chemise                  | fr_fr              |
      | sku-001        |              | short_description |              | Short shirt description                    | Default Store View |
      | sku-001        |              | short_description |              | Courte description d'une chemise           | fr_fr              |
      | sku-001        |              | color             |              | Blue                                       | Default Store View |
      | sku-001        |              | color             |              | Blue                                       | fr_fr              |
      | sku-001        |              | size              |              | L                                          | Default Store View |
      | sku-001        |              | size              |              | L                                          | fr_fr              |
      | sku-001        |              | price             |              | 27.00                                      | Default Store View |
      | sku-001        |              | price             |              | 27.00                                       | fr_fr              |
      | sku-001        |              | tax_class_id      |              | Shipping                                   | Default Store View |
      | sku-001        |              | tax_class_id      |              | Shipping                                   | fr_fr              |
      | sku-001        |              | weight            |              | 360                                        | Default Store View |
      | sku-001        |              | weight            |              | 360                                        | fr_fr              |
      | sku-001        |              | set_name          |              | Shirt                                      |                    |
      | sku-001        |              | categories        |              | shirts                                     |                    |
      | sku-001        | simple       |                   |              |                                            |                    |
      | sku-001        |              |                   | up_sell      | sku-000                                    |                    |
      | sku-001        |              |                   | up_sell      | sku-003                                    |                    |
      | sku-002        |              | name              |              | Second shirt product example               | Default Store View |
      | sku-002        |              | name              |              | Second exemple de produit chemise          | fr_fr              |
      | sku-002        |              | description       |              | Second shirt description                   | Default Store View |
      | sku-002        |              | description       |              | Seconde description d'une chemise          | fr_fr              |
      | sku-002        |              | short_description |              | Second short shirt description             | Default Store View |
      | sku-002        |              | short_description |              | Seconde courte description d'une chemise   | fr_fr              |
      | sku-002        |              | color             |              | Red                                        | Default Store View |
      | sku-002        |              | color             |              | Red                                        | fr_fr              |
      | sku-002        |              | size              |              | S                                          | Default Store View |
      | sku-002        |              | size              |              | S                                          | fr_fr              |
      | sku-002        |              | price             |              | 48.00                                      | Default Store View |
      | sku-002        |              | price             |              | 48.00                                      | fr_fr              |
      | sku-002        |              | tax_class_id      |              | Shipping                                   | Default Store View |
      | sku-002        |              | tax_class_id      |              | Shipping                                   | fr_fr              |
      | sku-002        |              | weight            |              | 360                                        | Default Store View |
      | sku-002        |              | weight            |              | 360                                        | fr_fr              |
      | sku-002        |              | set_name          |              | Shirt                                      |                    |
      | sku-002        |              | categories        |              | shirts                                     |                    |
      | sku-002        | simple       |                   |              |                                            |                    |
      | sku-003        |              | name              |              | Third shirt product example                | Default Store View |
      | sku-003        |              | name              |              | Troisième exemple de produit chemise       | fr_fr              |
      | sku-003        |              | description       |              | Third shirt description                    | Default Store View |
      | sku-003        |              | description       |              | Troisième description d'une chemise        | fr_fr              |
      | sku-003        |              | short_description |              | Third short shirt description              | Default Store View |
      | sku-003        |              | short_description |              | Troisième courte description d'une chemise | fr_fr              |
      | sku-003        |              | color             |              | Black                                      | Default Store View |
      | sku-003        |              | color             |              | Black                                      | fr_fr              |
      | sku-003        |              | size              |              | XS                                         | Default Store View |
      | sku-003        |              | size              |              | XS                                         | fr_fr              |
      | sku-003        |              | price             |              | 48.00                                      | Default Store View |
      | sku-003        |              | price             |              | 48.00                                      | fr_fr              |
      | sku-003        |              | tax_class_id      |              | Shipping                                   | Default Store View |
      | sku-003        |              | tax_class_id      |              | Shipping                                   | fr_fr              |
      | sku-003        |              | weight            |              | 360                                        | Default Store View |
      | sku-003        |              | weight            |              | 360                                        | fr_fr              |
      | sku-003        |              | set_name          |              | Shirt                                      |                    |
      | sku-003        |              | categories        |              | shirts                                     |                    |
      | sku-003        | simple       |                   |              |                                            |                    |
      | sku-003        |              |                   | related      | sku-002                                    |                    |
      | conf-oro_shirt | configurable |                   |              |                                            |                    |
      | conf-oro_shirt |              | categories        |              | shirts                                     |                    |
      | conf-oro_shirt |              |                   | configurable | sku-001                                    |                    |
      | conf-oro_shirt |              |                   | configurable | sku-002                                    |                    |
      | conf-oro_shirt |              |                   | configurable | sku-003                                    |                    |
