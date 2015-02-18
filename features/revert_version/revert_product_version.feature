@javascript
Feature: Revert a product to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert a product to a previous version

  # what's tested here?
  # -----------------------------|-------------|-------------
  # TYPE                         | VALID VALUE | NULL VALUE
  # -----------------------------|-------------|-------------
  # pim_catalog_boolean          | done        | done
  # pim_catalog_date             | done        | done
  # pim_catalog_file             | -           | -
  # pim_catalog_identifier       | done        | N/A
  # pim_catalog_image            | -           | -
  # pim_catalog_metric           | done        | done
  # pim_catalog_multiselect      | done        | done
  # pim_catalog_number           | -           | done
  # pim_catalog_price_collection | done        | done
  # pim_catalog_simpleselect     | done        | done
  # pim_catalog_text             | done        | done
  # pim_catalog_textarea         | done        | done

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert the status of a product (disabled)
    Given an enabled "shirt" product
    And I am on the "shirt" product page
    And I disable the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: shirt"
    Then product "shirt" should be enabled

  Scenario: Successfully revert the status of a product (enable)
    Given a disabled "shirt" product
    And I am on the "shirt" product page
    And I enable the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: shirt"
    Then product "shirt" should be disabled

  Scenario: Successfully revert the family of a product
    Given the following product:
      | sku  | family |
      | jean | pants  |
    And I am on the products page
    Then I mass-edit products jean
    And I choose the "Change the family of products" operation
    And I change the Family to "Jackets"
    And I move on to the next step
    Then the family of product "jean" should be "jackets"
    And I am on the "jean" product page
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: jean"
    Then the family of product "jean" should be "pants"

  Scenario: Successfully revert the category of a product
    Given the following product:
      | sku     | categories        |
      | sandals | winter_collection |
    And I edit the "sandals" product
    And I visit the "Categories" tab
    And I select the "2014 collection" tree
    And I expand the "2014 collection" category
    And I click on the "Winter collection" category
    And I click on the "Summer collection" category
    And I press the "Save" button
    Then I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: sandals"
    Then the category of "sandals" should be "winter_collection"

  Scenario: Successfully revert simpleselect attribute options of a product
    Given the following product:
      | sku  | family |
      | jean | pants  |
    Given I am on the "jean" product page
    And I change the Manufacturer to "Desigual"
    And I save the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: jean"
    Then I should see a flash message "Successfully revert the product to the previous version"

  Scenario: Successfully revert multiselect attribute options of a product
    Given the following product:
      | sku  | family |
      | jean | pants  |
    Given I am on the "jean" product page
    Given I add a new option to the "Weather conditions" attribute
    When I fill in the following information in the popin:
      | Code  | very_wet      |
      | en_US | Extremely wet |
    And I press the "Save" button in the popin
    And I save the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: jean"
    Then I should see a flash message "Successfully revert the product to the previous version"

  @jira https://akeneo.atlassian.net/browse/PIM-3351
  Scenario: Successfully revert a product with prices and leave them empty
    And the following product:
      | sku   | name-fr_FR | family |
      | jeans | Nice jeans | pants  |
    And I am logged in as "Julia"
    When I edit the "jeans" product
    And I fill in the following information:
      | Name | Really nice jeans |
    And I save the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: jeans"
    And I visit the "Attributes" tab
    And I visit the "Marketing" group
    And the product "jeans" should have the following values:
      | price      |            |
      | name-fr_FR | Nice jeans |

  @jira https://akeneo.atlassian.net/browse/PIM-3301
  Scenario: Successfully revert a product date and leave it empty
    And the following product:
      | sku   | family  |
      | jeans | jackets |
    When I edit the "jeans" product
    And I change the "mobile Release date" to "2014-05-20"
    And I save the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: jeans"
    And I save the product
    And the product "jeans" should have the following values:
      | release_date-mobile |  |

  Scenario: Successfully revert a product number and leave it empty
    And the following product:
      | sku   | family  |
      | jeans | jackets |
    When I edit the "jeans" product
    And I visit the "Marketing" group
    And I change the "tablet Number in stock" to "100"
    And I save the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: jeans"
    And I save the product
    And the product "jeans" should have the following values:
      | number_in_stock-tablet |  |

  Scenario: Successfully revert a pim_catalog_boolean attribute
    Given the following product:
      | sku   | family | handmade |
      | jeans | pants  | yes      |
      | short | pants  |          |
    Given I am on the "jeans" product page
    When I uncheck the "Handmade" switch
    And I save the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: jeans"
    And the product "jeans" should have the following values:
      | handmade | 1 |
    Then the "Handmade" field should contain "1"
    Given I am on the "short" product page
    And I visit the "Attributes" tab
    And I add available attributes Handmade
    When I check the "Handmade" switch
    And I save the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: short"
    And the product "short" should have the following values:
      | handmade |  |

  Scenario: Successfully revert a pim_catalog_date attribute
    Given the following product:
      | sku   | family | release_date-mobile |
      | jeans | pants  | 2011-08-17          |
      | short | pants  |                     |
    Given I am on the "jeans" product page
    When I change the "mobile Release date" to "2001-01-01"
    And I save the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: jeans"
    Then the product "jeans" should have the following values:
      | release_date-mobile | 2011-08-17 |
    Given I am on the "short" product page
    And I visit the "Attributes" tab
    And I add available attributes Release date
    When I change the "mobile Release date" to "2001-01-01"
    And I save the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: short"
    Then the product "short" should have the following values:
      | release_date-mobile |  |

  Scenario: Successfully revert a pim_catalog_identifier attribute
    Given the following product:
      | sku   | family |
      | jeans | pants  |
    Given I am on the "jeans" product page
    When I change the "SKU" to "pantalon"
    And I save the product
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "family: pants"
    Then the product "jeans" should have the following values:
      | sku | jeans |

  Scenario: Successfully revert a pim_catalog_metric attribute
    Given the following product:
      | sku     | family | length        |
      | t-shirt | tees   | 70 CENTIMETER |
      | marcel  | tees   |               |
    Given I am on the "t-shirt" product page
    And I visit the "Sizes" group
    When I change the "Length" to ""
    And I save the product
    When I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: t-shirt"
    Then the product "t-shirt" should have the following values:
      | length | 70.0000 CENTIMETER |
    Given I am on the "marcel" product page
    And I visit the "Attributes" tab
    Then I add available attributes Length
    And I visit the "Sizes" group
    When I change the "Length" to "120"
    And I save the product
    When I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: marcel"
    Then the product "marcel" should have the following values:
      | length |  |

  Scenario: Successfully revert a pim_catalog_multiselect attribute
    Given the following product:
      | sku     | family | weather_conditions |
      | t-shirt | tees   | Dry, Cold          |
      | marcel  | tees   |                    |
    Given I am on the "t-shirt" product page
    And I change the "Weather conditions" to ""
    And I save the product
    When I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: t-shirt"
    Then the product "t-shirt" should have the following values:
      | weather_conditions | Dry, Cold |
    Given I am on the "marcel" product page
    And I visit the "Attributes" tab
    Then I add available attributes Weather conditions
    And I change the "Weather conditions" to "Hot, Wet"
    And I save the product
    When I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: marcel"
    Then the product "marcel" should have the following values:
      | weather_conditions |  |

  Scenario: Successfully revert a pim_catalog_number attribute
    Given the following product:
      | sku     | family |
      | t-shirt | tees   |
    Given I am on the "t-shirt" product page
    And I add available attributes Number in stock
    And I visit the "Marketing" group
    And I change the "tablet Number in stock" to "42"
    And I save the product
    When I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: t-shirt"
    Then the product "t-shirt" should have the following values:
      | number_in_stock-tablet |  |

  Scenario: Successfully revert a pim_catalog_price_collection attribute
    Given the following product:
      | sku     | family | price  |
      | t-shirt | tees   | 49 EUR |
      | marcel  | tees   |        |
    Given I am on the "t-shirt" product page
    And I visit the "Marketing" group
    And I change the "€ Price" to "39"
    And I save the product
    When I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: t-shirt"
    Then the product "t-shirt" should have the following values:
      | price | 49.00 EUR |
    Given I am on the "marcel" product page
    And I visit the "Attributes" tab
    And I visit the "Marketing" group
    And I change the "€ Price" to "19.99"
    And I save the product
    When I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: marcel"
    Then the product "marcel" should have the following values:
      | price |  |

  Scenario: Successfully revert a pim_catalog_simpleselect attribute
    Given the following product:
      | sku     | family | rating |
      | t-shirt | tees   | 4      |
      | marcel  | tees   |        |
    Given I am on the "t-shirt" product page
    And I visit the "Marketing" group
    And I change the "Rating" to "2"
    And I save the product
    When I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: t-shirt"
    Then the product "t-shirt" should have the following values:
      | rating | 4 stars |
    Given I am on the "marcel" product page
    And I visit the "Attributes" tab
    And I visit the "Product information" group
    And I change the "Name" to "test"
    And I visit the "Marketing" group
    And I change the "Rating" to "5"
    And I save the product
    When I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: marcel"
    Then the product "marcel" should have the following values:
      | rating |  |

  Scenario: Successfully revert a pim_catalog_text attribute
    Given the following product:
      | sku     | family | comment            |
      | t-shirt | tees   | This is a comment. |
      | marcel  | tees   |                    |
    Given I am on the "t-shirt" product page
    And I visit the "Other" group
    And I change the "Comment" to "This is not a comment anymore."
    And I save the product
    When I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: t-shirt"
    Then the product "t-shirt" should have the following values:
      | comment | This is a comment. |
    Given I am on the "marcel" product page
    And I visit the "Attributes" tab
    And I add available attributes Comment
    And I visit the "Product information" group
    And I change the "Name" to "test"
    And I visit the "Other" group
    And I change the "Comment" to "New comment."
    And I save the product
    When I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: marcel"
    Then the product "marcel" should have the following values:
      | comment |  |

    Scenario: Successfully revert a pim_catalog_textarea attribute
      Given the following product:
        | sku     | family | description-tablet-en_US |
        | t-shirt | tees   | A nice t-shirt.          |
        | marcel  | tees   |                          |
      Given I am on the "t-shirt" product page
      And I change the "tablet Description" to "A really nice t-shirt !"
      And I save the product
      When I visit the "History" tab
      When I click on the "Revert to this version" action of the row which contains "sku: t-shirt"
      Then the product "t-shirt" should have the following values:
        | description-tablet-en_US | A nice t-shirt. |
      Given I am on the "marcel" product page
      And I visit the "Attributes" tab
      And I visit the "Product information" group
      And I change the "Name" to "test"
      And I change the "tablet Description" to "One does not simply fill a description."
      And I save the product
      When I visit the "History" tab
      When I click on the "Revert to this version" action of the row which contains "sku: marcel"
      Then the product "marcel" should have the following values:
        | comment |  |

  @jira https://akeneo.atlassian.net/browse/PIM-3760
  Scenario: Successfully revert a pim_catalog_image attribute
    Given the following product:
      | sku     | family |
      | t-shirt | tees   |
    Given I am on the "t-shirt" product page
    And I visit the "Media" group
    And I attach file "akeneo.jpg" to "Side view"
    And I visit the "Product information" group
    And I change the Name to "T-shirt with picture"
    And I save the product
    And I visit the "Media" group
    When I remove the "Side view" file
    And I save the product
    When I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "T-shirt with picture"
    Then the product "t-shirt" should have the following values:
      | side_view | akeneo.jpg |

  @jira https://akeneo.atlassian.net/browse/PIM-3760
  Scenario: Successfully revert a pim_catalog_file attribute
    Given the following product:
      | sku     | family |
      | t-shirt | tees   |
    Given I am on the "t-shirt" product page
    And I add available attribute Datasheet
    And I visit the "Media" group
    And I attach file "bic-core-148.txt" to "Datasheet"
    And I visit the "Product information" group
    And I change the Name to "T-shirt with datasheet"
    And I save the product
    And I visit the "Media" group
    When I remove the "Datasheet" file
    And I save the product
    When I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "T-shirt with datasheet"
    Then the product "t-shirt" should have the following values:
      | datasheet | bic-core-148.txt |

  @jira https://akeneo.atlassian.net/browse/PIM-3765
  Scenario: Fail to revert attribute affected by a variant group
    Given the following product:
      | sku          | family  | size |
      | helly-hansen | Jackets | XS   |
    And the following product groups:
      | code       | label                | axis | type    |
      | hh_jackets | Helly Hansen jackets | size | VARIANT |
    And the following variant group values:
      | group      | attribute | value | locale | scope |
      | hh_jackets | name      | a     | en_US  |       |
    Then I am on the "hh_jackets" variant group page
    And I check the row "helly-hansen"
    And I press the "Save" button
    Then I am on the "helly-hansen" product page
    And I visit the "History" tab
    When I click on the "Revert to this version" action of the row which contains "sku: helly-hansen"
    Then I should see a flash message "Product can not be reverted because it belongs to a variant group"
