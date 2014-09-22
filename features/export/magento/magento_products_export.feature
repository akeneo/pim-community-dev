@javascript
Feature: Magento product export
  In order to view products in Magento
  As an Administrator
  I need to be able to export my products to Magento

  Scenario: Successfully export products to Magento
    Given  a "complete_magento" catalog configuration
    And the following products:
      | sku     | family  | categories | groups    | price                 | size   | weight   | tax_class_id |
      | sku-000 | Default | notebooks  |           | 10.00 EUR,50.00 USD   |        | 200 GRAM | 4            |
      | sku-001 | Shirt   | shirts     | oro_shirt | 5.00 EUR,27.00 USD    | XS     | 360 GRAM | 4            |
      | sku-002 | Shirt   | shirts     | oro_shirt | 25.00 EUR,48.00 USD   | S      | 360 GRAM | 4            |
      | sku-003 | Shirt   | shirts     | oro_shirt | 20.00 EUR,48.00 USD   | L      | 360 GRAM | 4            |
    And the following product values:
      | product | attribute         | value                                      | locale | scope   |
      | sku-000 | name              | Product example                            | en_US  | magento |
      | sku-000 | name              | Exemple de produit                         | fr_FR  | magento |
      | sku-000 | description       | Description                                | en_US  | magento |
      | sku-000 | description       | Description                                | fr_FR  | magento |
      | sku-000 | short_description | Short description                          | en_US  | magento |
      | sku-000 | short_description | Courte description                         | fr_FR  | magento |
      | sku-001 | name              | Shirt product example                      | en_US  | magento |
      | sku-001 | name              | Exemple de produit chemise                 | fr_FR  | magento |
      | sku-001 | description       | Shirt description                          | en_US  | magento |
      | sku-001 | description       | Description d'une chemise                  | fr_FR  | magento |
      | sku-001 | short_description | Short shirt description                    | en_US  | magento |
      | sku-001 | short_description | Courte description d'une chemise           | fr_FR  | magento |
      | sku-001 | color             | Blue                                       | en_US  | magento |
      | sku-001 | color             | Bleu                                       | fr_FR  | magento |
      | sku-002 | name              | Second shirt product example               | en_US  | magento |
      | sku-002 | name              | Second exemple de produit chemise          | fr_FR  | magento |
      | sku-002 | description       | Second shirt description                   | en_US  | magento |
      | sku-002 | description       | Seconde description d'une chemise          | fr_FR  | magento |
      | sku-002 | short_description | Second short shirt description             | en_US  | magento |
      | sku-002 | short_description | Seconde courte description d'une chemise   | fr_FR  | magento |
      | sku-002 | color             | Red                                        | en_US  | magento |
      | sku-002 | color             | Rouge                                      | fr_FR  | magento |
      | sku-003 | name              | Third shirt product example                | en_US  | magento |
      | sku-003 | name              | Troisième exemple de produit chemise       | fr_FR  | magento |
      | sku-003 | description       | Third shirt description                    | en_US  | magento |
      | sku-003 | description       | Troisième description d'une chemise        | fr_FR  | magento |
      | sku-003 | short_description | Third short shirt description              | en_US  | magento |
      | sku-003 | short_description | Troisième courte description d'une chemise | fr_FR  | magento |
      | sku-003 | color             | Black                                      | en_US  | magento |
      | sku-003 | color             | Noir                                       | fr_FR  | magento |
    And I launched the completeness calculator
    And I am logged in as "peter"
    When I am on the "magento_full_export" export job edit page
    And I fill in the "storeview" mapping:
      | fr_FR | fr_fr |
    And I fill in the "category" mapping:
      | Master catalog (default) | Default Category |
    And I press the "Save" button and I wait "15"s
    When I launch the export job
    Then I wait for the "magento_full_export" job to finish
    When I am on the "magento_product_export" export job edit page
    And I fill in the "storeview" mapping:
      | fr_FR | fr_fr |
    And I fill in the "category" mapping:
      | Master catalog (default) | Default Category |
    And I press the "Save" button and I wait "15"s
    Then I launch the export job
    And I wait for the "magento_product_export" job to finish
    Then I check if "products" were sent in Magento:
      | product | attribute         | value                                      | store_view         |
      | sku-000 | name              | Product example                            | Default Store View |
      | sku-000 | name              | Exemple de produit                         | fr_fr              |
      | sku-000 | description       | Description                                | Default Store View |
      | sku-000 | description       | Description                                | fr_fr              |
      | sku-000 | short_description | Short description                          | Default Store View |
      | sku-000 | short_description | Courte description                         | fr_fr              |
      | sku-001 | name              | Shirt product example                      | Default Store View |
      | sku-001 | name              | Exemple de produit chemise                 | fr_fr              |
      | sku-001 | description       | Shirt description                          | Default Store View |
      | sku-001 | description       | Description d'une chemise                  | fr_fr              |
      | sku-001 | short_description | Short shirt description                    | Default Store View |
      | sku-001 | short_description | Courte description d'une chemise           | fr_fr              |
      | sku-001 | color             | Blue                                       | Default Store View |
      | sku-001 | color             | Bleu                                       | fr_fr              |
      | sku-002 | name              | Second shirt product example               | Default Store View |
      | sku-002 | name              | Second exemple de produit chemise          | fr_fr              |
      | sku-002 | description       | Second shirt description                   | Default Store View |
      | sku-002 | description       | Seconde description d'une chemise          | fr_fr              |
      | sku-002 | short_description | Second short shirt description             | Default Store View |
      | sku-002 | short_description | Seconde courte description d'une chemise   | fr_fr              |
      | sku-002 | color             | Red                                        | Default Store View |
      | sku-002 | color             | Rouge                                      | fr_fr              |
      | sku-003 | name              | Third shirt product example                | Default Store View |
      | sku-003 | name              | Troisième exemple de produit chemise       | fr_fr              |
      | sku-003 | description       | Third shirt description                    | Default Store View |
      | sku-003 | description       | Troisième description d'une chemise        | fr_fr              |
      | sku-003 | short_description | Third short shirt description              | Default Store View |
      | sku-003 | short_description | Troisième courte description d'une chemise | fr_fr              |
      | sku-003 | color             | Black                                      | Default Store View |
      | sku-003 | color             | Noir                                       | fr_fr              |
