@javascript
Feature: Editing attribute values of a variant group also updates products
  In order to easily edit common attributes of variant group products
  As a product manager
  I need to be able to change attribute values of a variant group

  # what's tested here?
  # --------------------------------|-------------|
  # TYPE                            | VALID VALUE |
  # --------------------------------|-------------|
  # pim_catalog_boolean             | done        |
  # pim_catalog_date                | done        |
  # pim_catalog_file                | done        |
  # pim_catalog_identifier          | N/A         |
  # pim_catalog_image               | done        |
  # pim_catalog_metric              | done        |
  # pim_catalog_multiselect         | done        |
  # pim_catalog_number              | done        |
  # pim_catalog_price_collection    | done        |
  # pim_catalog_simpleselect        | done        |
  # pim_catalog_text                | done        |
  # pim_catalog_textarea            | done        |

  Background:
    Given a "footwear" catalog configuration
    And the following variant group values:
      | group             | attribute          | value         | locale | scope  |
      | caterpillar_boots | destocking_date    | 2012-02-22    |        |        |
      | caterpillar_boots | length             | 10 CENTIMETER |        |        |
      | caterpillar_boots | weather_conditions | Dry           |        |        |
      | caterpillar_boots | number_in_stock    | 1900          |        |        |
      | caterpillar_boots | price              | 39.99 EUR     |        |        |
      | caterpillar_boots | rating             | 1             |        |        |
      | caterpillar_boots | name               | Old name      | en_US  |        |
      | caterpillar_boots | description        | A product.    | en_US  | tablet |
    And the following products:
      | sku  | groups            | color | size |
      | boot | caterpillar_boots | black | 40   |
    And the following attributes:
      | code                  | label-en_US           | type | group | allowedExtensions    |
      | technical_description | Technical description | file | media | gif,png,jpeg,jpg,txt |
    And I am logged in as "Julia"
    And I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab

  Scenario: Change a pim_catalog_boolean attribute of a variant group
    When I add available attributes Handmade
    And I visit the "Other" group
    And I check the "Handmade" switch
    And I save the variant group
    Then the product "boot" should have the following values:
      | handmade | 1 |

  Scenario: Change a pim_catalog_date attribute of a variant group
    When I change the "Destocking date" to "2001-01-01"
    And I save the variant group
    Then the product "boot" should have the following values:
      | destocking_date | 2001-01-01 |

  Scenario: Change a pim_catalog_metric attribute of a variant group
    When I change the "Length" to "5"
    And I save the variant group
    Then the product "boot" should have the following values:
      | length | 5.0000 CENTIMETER |

  Scenario: Change a pim_catalog_multiselect attribute of a variant group
    When I change the "Weather conditions" to "Wet, Cold"
    And I save the variant group
    Then the product "boot" should have the following values:
      | weather_conditions | [wet], [cold] |

  Scenario: Change a pim_catalog_number attribute of a variant group
    When I visit the "Other" group
    And I change the "Number in stock" to "8000"
    And I save the variant group
    Then the product "boot" should have the following values:
      | number_in_stock | 8000.0000 |

  Scenario: Change a pim_catalog_price_collection attribute of a variant group
    When I visit the "Marketing" group
    And I change the "€ Price" to "89"
    And I save the variant group
    Then the product "boot" should have the following values:
      | price | 89.00 EUR |

  Scenario: Change a pim_catalog_simpleselect attribute of a variant group
    When I visit the "Marketing" group
    And I change the "Rating" to "5"
    And I save the variant group
    Then the product "boot" should have the following values:
      | rating | [5] |

  Scenario: Change a pim_catalog_text attribute of a variant group
    When I change the "Name" to "In a galaxy far far away"
    And I save the variant group
    Then the product "boot" should have the following values:
      | name-en_US | In a galaxy far far away |

  Scenario: Change a pim_catalog_textarea attribute of a variant group
    When I change the "tablet Description" to "The best boots!"
    And I save the variant group
    Then the product "boot" should have the following values:
      | description-en_US-tablet | The best boots! |

  Scenario: Change a pim_catalog_image attribute of a variant group
    When I add available attributes Side view
    And I visit the "Media" group
    And I attach file "SNKRS-1R.png" to "Side view"
    And I save the variant group
    Then the product "boot" should have the following values:
      | side_view | SNKRS-1R.png |

  Scenario: Change a pim_catalog_file attribute of a variant group
    When I add available attributes Technical description
    And I visit the "Media" group
    And I attach file "SNKRS-1R.png" to "Technical description"
    And I save the variant group
    Then the product "boot" should have the following values:
      | technical_description | SNKRS-1R.png |
