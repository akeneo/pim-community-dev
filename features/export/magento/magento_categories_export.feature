@javascript
Feature: Magento category export
  In order to view categories in Magento
  As an Administrator
  I need to be able to export categories to Magento

  Scenario: Successfully export attributes to Magento
    Given a "magento" catalog configuration
    And the following category:
      | code         | label-en_US      | label-fr_FR                 | parent    |
      | computers    | Computers        | Ordinateurs                 | default   |
      | desktops     | Desktops         | Ordinateurs de bureau       | computers |
      | laptops      | Laptops          | Ordinateurs portables       | computers |
      | notebooks    | Notebooks        | Ordinateurs ultra-portables | laptops   |
      | apparels     | Apparels & Shoes | Habillements & chaussures   | default   |
      | shirts       | Shirts           | Chemises                    | apparels  |
      | jeans        | Jeans            | Jeans                       | apparels  |
      | shoes        | Shoes            | Chaussures                  | apparels  |
      | shoes_male   | Shoes Male       | Chaussures homme            | shoes     |
      | shoes_female | Shoes Female     | Chaussures femme            | shoes     |
    And I am logged in as "peter"
    When I am on the "magento_category_export" export job edit page
    And I fill in the "storeview" mapping:
      | fr_FR | fr_fr |
    And I fill in the "category" mapping:
      | Master catalog (default) | Default Category |
    And I press the "Save" button and I wait "15"s
    Then I launch the export job
    And I wait for the "magento_category_export" job to finish
    Then I check if "categories" were sent in Magento:
      | store_view         | text                        | parent                    | root             |
      | Default Store View | Computers                   | Default Category          | Default Category |
      | Default Store View | Desktops                    | Computers                 |                  |
      | Default Store View | Laptops                     | Computers                 |                  |
      | Default Store View | Notebooks                   | Laptops                   |                  |
      | Default Store View | Apparels & Shoes            | Default Category          |                  |
      | Default Store View | Shirts                      | Apparels & Shoes          |                  |
      | Default Store View | Jeans                       | Apparels & Shoes          |                  |
      | Default Store View | Shoes                       | Apparels & Shoes          |                  |
      | Default Store View | Shoes Male                  | Shoes                     |                  |
      | Default Store View | Shoes Female                | Shoes                     |                  |
      | fr_fr              | Ordinateurs                 | Default Category          | Default Category |
      | fr_fr              | Ordinateurs de bureau       | Ordinateurs               |                  |
      | fr_fr              | Ordinateurs portables       | Ordinateurs               |                  |
      | fr_fr              | Ordinateurs ultra-portables | Ordinateurs portables     |                  |
      | fr_fr              | Habillements & chaussures   | Default Category          |                  |
      | fr_fr              | Chemises                    | Habillements & chaussures |                  |
      | fr_fr              | Jeans                       | Habillements & chaussures |                  |
      | fr_fr              | Chaussures                  | Habillements & chaussures |                  |
      | fr_fr              | Chaussures homme            | Chaussures                |                  |
      | fr_fr              | Chaussures femme            | Chaussures                |                  |
