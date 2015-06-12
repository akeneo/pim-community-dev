Feature: Denormalize product values
  In order to be able to recreate product values from json data
  As a developer
  I need to be able to denormalize json into product values

  Scenario: Successfully normalize and denormalize product values
    Given the "default" catalog configuration
    And the following family:
      | code |
      | all  |
    And the following attributes:
      | code                  | type         | localizable | scopable | metric_family | default_metric_unit | allowed_extensions | families |
      | text                  | text         | no          | no       |               |                     |                    | all      |
      | text_loc              | text         | yes         | no       |               |                     |                    | all      |
      | text_scop             | text         | no          | yes      |               |                     |                    | all      |
      | text_loc_scop         | text         | yes         | yes      |               |                     |                    | all      |
      | number                | number       | no          | no       |               |                     |                    | all      |
      | number_loc            | number       | yes         | no       |               |                     |                    | all      |
      | number_scop           | number       | no          | yes      |               |                     |                    | all      |
      | number_loc_scop       | number       | yes         | yes      |               |                     |                    | all      |
      | textarea              | textarea     | no          | no       |               |                     |                    | all      |
      | textarea_loc          | textarea     | yes         | no       |               |                     |                    | all      |
      | textarea_scop         | textarea     | no          | yes      |               |                     |                    | all      |
      | textarea_loc_scop     | textarea     | yes         | yes      |               |                     |                    | all      |
      | metric                | metric       | no          | no       | Length        | CENTIMETER          |                    | all      |
      | metric_loc            | metric       | yes         | no       | Length        | CENTIMETER          |                    | all      |
      | metric_scop           | metric       | no          | yes      | Length        | CENTIMETER          |                    | all      |
      | metric_loc_scop       | metric       | yes         | yes      | Length        | CENTIMETER          |                    | all      |
      | prices                | prices       | no          | no       |               |                     |                    | all      |
      | prices_loc            | prices       | yes         | no       |               |                     |                    | all      |
      | prices_scop           | prices       | no          | yes      |               |                     |                    | all      |
      | prices_loc_scop       | prices       | yes         | yes      |               |                     |                    | all      |
      | image                 | image        | no          | no       |               |                     | png,jpg,gif        | all      |
      | image_loc             | image        | yes         | no       |               |                     | png,jpg,gif        | all      |
      | image_scop            | image        | no          | yes      |               |                     | png,jpg,gif        | all      |
      | image_loc_scop        | image        | yes         | yes      |               |                     | png,jpg,gif        | all      |
      | file                  | file         | no          | no       |               |                     | txt,png            | all      |
      | file_loc              | file         | yes         | no       |               |                     | txt,png            | all      |
      | file_scop             | file         | no          | yes      |               |                     | txt,png            | all      |
      | file_loc_scop         | file         | yes         | yes      |               |                     | txt,png            | all      |
      | simpleselect          | simpleselect | no          | no       |               |                     |                    | all      |
      | simpleselect_loc      | simpleselect | yes         | no       |               |                     |                    | all      |
      | simpleselect_scop     | simpleselect | no          | yes      |               |                     |                    | all      |
      | simpleselect_loc_scop | simpleselect | yes         | yes      |               |                     |                    | all      |
      | multiselect           | multiselect  | no          | no       |               |                     |                    | all      |
      | multiselect_loc       | multiselect  | yes         | no       |               |                     |                    | all      |
      | multiselect_scop      | multiselect  | no          | yes      |               |                     |                    | all      |
      | multiselect_loc_scop  | multiselect  | yes         | yes      |               |                     |                    | all      |
      | date                  | date         | no          | no       |               |                     |                    | all      |
      | date_loc              | date         | yes         | no       |               |                     |                    | all      |
      | date_scop             | date         | no          | yes      |               |                     |                    | all      |
      | date_loc_scop         | date         | yes         | yes      |               |                     |                    | all      |
      | boolean               | boolean      | no          | no       |               |                     |                    | all      |
      | boolean_loc           | boolean      | yes         | no       |               |                     |                    | all      |
      | boolean_scop          | boolean      | no          | yes      |               |                     |                    | all      |
      | boolean_loc_scop      | boolean      | yes         | yes      |               |                     |                    | all      |
    And the following "simpleselect" attribute option: s1
    And the following "simpleselect_loc" attribute options: s2 and s3
    And the following "simpleselect_scop" attribute options: s4 and s5
    And the following "simpleselect_loc_scop" attribute options: s6, s7 and s8
    And the following "multiselect" attribute options: m1 and m2
    And the following "multiselect_loc" attribute options: m3, m4 and m5
    And the following "multiselect_scop" attribute options: m6, m7 and m8
    And the following "multiselect_loc_scop" attribute options: m9, m10, m11 and m12
    And the following products:
      | sku    | family |
      | first  | all    |
      | second | all    |
    And the following product values:
      | product | attribute             | value                              | locale | scope     |
      | first   | text                  | one                                |        |           |
      | first   | text_loc              | two                                | en_US  |           |
      | first   | text_loc              | three                              | fr_FR  |           |
      | first   | text_scop             | four                               |        | ecommerce |
      | first   | text_scop             | five                               |        | mobile    |
      | first   | text_loc_scop         | six                                | en_US  | ecommerce |
      | first   | text_loc_scop         | seven                              | fr_FR  | ecommerce |
      | first   | text_loc_scop         | eight                              | fr_FR  | mobile    |
      | first   | number                | 1                                  |        |           |
      | first   | number_loc            | 2                                  | en_US  |           |
      | first   | number_loc            | 3                                  | fr_FR  |           |
      | first   | number_scop           | 4                                  |        | ecommerce |
      | first   | number_scop           | 5                                  |        | mobile    |
      | first   | number_loc_scop       | 6                                  | en_US  | ecommerce |
      | first   | number_loc_scop       | 7                                  | fr_FR  | ecommerce |
      | first   | number_loc_scop       | 8                                  | fr_FR  | mobile    |
      | first   | textarea              | nine                               |        |           |
      | first   | textarea_loc          | ten                                | en_US  |           |
      | first   | textarea_loc          | eleven                             | fr_FR  |           |
      | first   | textarea_scop         | twelve                             |        | ecommerce |
      | first   | textarea_scop         | thirteen                           |        | mobile    |
      | first   | textarea_loc_scop     | fourteen                           | en_US  | ecommerce |
      | first   | textarea_loc_scop     | fifteen                            | fr_FR  | ecommerce |
      | first   | textarea_loc_scop     | sixteen                            | fr_FR  | mobile    |
      | first   | metric                | 1 CENTIMETER                       |        |           |
      | first   | metric_loc            | 2 CHAIN                            | en_US  |           |
      | first   | metric_loc            | 3 DECIMETER                        | fr_FR  |           |
      | first   | metric_scop           | 4 DEKAMETER                        |        | ecommerce |
      | first   | metric_scop           | 5 FEET                             |        | mobile    |
      | first   | metric_loc_scop       | 6 FURLONG                          | en_US  | ecommerce |
      | first   | metric_loc_scop       | 7 HECTOMETER                       | fr_FR  | ecommerce |
      | first   | metric_loc_scop       | 8 INCH                             | fr_FR  | mobile    |
      | first   | prices                | 1 EUR, 2 USD                       |        |           |
      | first   | prices_loc            | 3 EUR, 4 USD                       | en_US  |           |
      | first   | prices_loc            | 5 EUR, 6 USD                       | fr_FR  |           |
      | first   | prices_scop           | 7 EUR, 8 USD                       |        | ecommerce |
      | first   | prices_scop           | 9 EUR, 10 USD                      |        | mobile    |
      | first   | prices_loc_scop       | 11 EUR, 12 USD                     | en_US  | ecommerce |
      | first   | prices_loc_scop       | 13 EUR, 14 USD                     | fr_FR  | ecommerce |
      | first   | prices_loc_scop       | 15 EUR, 16 USD                     | fr_FR  | mobile    |
      | first   | image                 | %fixtures%/SNKRS-1C-s.png          |        |           |
      | first   | image_loc             | %fixtures%/SNKRS-1C-t.png          | en_US  |           |
      | first   | image_loc             | %fixtures%/akeneo.jpg              | fr_FR  |           |
      | first   | image_scop            | %fixtures%/akeneo2.jpg             |        | ecommerce |
      | first   | image_scop            | %fixtures%/bic-core-148.gif        |        | mobile    |
      | first   | image_loc_scop        | %fixtures%/fanatic-freewave-76.gif | en_US  | ecommerce |
      | first   | image_loc_scop        | %fixtures%/SNKRS-1C-s.png          | fr_FR  | ecommerce |
      | first   | image_loc_scop        | %fixtures%/SNKRS-1C-t.png          | fr_FR  | mobile    |
      | first   | file                  | %fixtures%/akeneo.txt              |        |           |
      | first   | file_loc              | %fixtures%/akeneo2.txt             | en_US  |           |
      | first   | file_loc              | %fixtures%/bic-core-148.txt        | fr_FR  |           |
      | first   | file_scop             | %fixtures%/fanatic-freewave-76.txt |        | ecommerce |
      | first   | file_scop             | %fixtures%/SNKRS-1C-s.png          |        | mobile    |
      | first   | file_loc_scop         | %fixtures%/SNKRS-1C-t.png          | en_US  | ecommerce |
      | first   | file_loc_scop         | %fixtures%/SNKRS-1C-t.png          | fr_FR  | ecommerce |
      | first   | file_loc_scop         | %fixtures%/SNKRS-1R.png            | fr_FR  | mobile    |
      | first   | simpleselect          | s1                                 |        |           |
      | first   | simpleselect_loc      | s2                                 | en_US  |           |
      | first   | simpleselect_loc      | s3                                 | fr_FR  |           |
      | first   | simpleselect_scop     | s4                                 |        | ecommerce |
      | first   | simpleselect_scop     | s5                                 |        | mobile    |
      | first   | simpleselect_loc_scop | s6                                 | en_US  | ecommerce |
      | first   | simpleselect_loc_scop | s7                                 | fr_FR  | ecommerce |
      | first   | simpleselect_loc_scop | s8                                 | fr_FR  | mobile    |
      | first   | multiselect           | m1, m2                             |        |           |
      | first   | multiselect_loc       | m3, m4                             | en_US  |           |
      | first   | multiselect_loc       | m4, m5                             | fr_FR  |           |
      | first   | multiselect_scop      | m6, m7                             |        | ecommerce |
      | first   | multiselect_scop      | m7, m8                             |        | mobile    |
      | first   | multiselect_loc_scop  | m9, m10, m11                       | en_US  | ecommerce |
      | first   | multiselect_loc_scop  | m10, m11, m12                      | fr_FR  | ecommerce |
      | first   | multiselect_loc_scop  | m9, m12                            | fr_FR  | mobile    |
      | first   | date                  | 2012-01-01                         |        |           |
      | first   | date_loc              | 2012-12-31                         | en_US  |           |
      | first   | date_loc              | 2012-01-01                         | fr_FR  |           |
      | first   | date_scop             | 2012-12-31                         |        | ecommerce |
      | first   | date_scop             | 2012-01-01                         |        | mobile    |
      | first   | date_loc_scop         | 2012-12-31                         | en_US  | ecommerce |
      | first   | date_loc_scop         | 2012-01-01                         | fr_FR  | ecommerce |
      | first   | date_loc_scop         | 2012-12-31                         | fr_FR  | mobile    |
      | first   | boolean               | 1                                  |        |           |
      | first   | boolean_loc           | 0                                  | en_US  |           |
      | first   | boolean_loc           | 1                                  | fr_FR  |           |
      | first   | boolean_scop          | 0                                  |        | ecommerce |
      | first   | boolean_scop          | 1                                  |        | mobile    |
      | first   | boolean_loc_scop      | 0                                  | en_US  | ecommerce |
      | first   | boolean_loc_scop      | 1                                  | fr_FR  | ecommerce |
      | first   | boolean_loc_scop      | 0                                  | fr_FR  | mobile    |
    Then I should be able to normalize and denormalize the products first and second
