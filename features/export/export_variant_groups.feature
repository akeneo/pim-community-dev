Feature: Export variant groups
  In order to be able to access and modify groups data outside PIM
  As a product manager
  I need to be able to export variant groups

  @javascript
  Scenario: Successfully export variant groups
    Given a "footwear" catalog configuration
    And the following job "footwear_variant_group_export" configuration:
      | filePath | %tmp%/variant_group_export/variant_group_export.csv |
    And I am logged in as "Julia"
    And I am on the "footwear_variant_group_export" export job page
    When I launch the export job
    And I wait for the "footwear_variant_group_export" job to finish
    Then I should see "Read 1"
    And I should see "Written 1"
    And exported file of "footwear_variant_group_export" should contain:
    """
    code;axis;label-en_US;type
    caterpillar_boots;color,size;"Caterpillar boots";VARIANT
    """

  @javascript
  Scenario: Successfully export variant groups with attributes
    Given a "footwear" catalog configuration
    And the following attribute:
      | code       | type | allowed_extensions |
      | attachment | file | txt                |
    And the following variant group values:
      | group             | attribute          | value                 | locale | scope  |
      | caterpillar_boots | manufacturer       | Caterpillar           |        |        |
      | caterpillar_boots | weather_conditions | dry, wet              |        |        |
      | caterpillar_boots | description        | Nice boots            | en_US  | mobile |
      | caterpillar_boots | comment            | Best worn in winter   |        |        |
      | caterpillar_boots | price              | 100 EUR, 150 USD      |        |        |
      | caterpillar_boots | side_view          | %fixtures%/akeneo.jpg |        |        |
      | caterpillar_boots | length             | 30 CENTIMETER         |        |        |
      | caterpillar_boots | number_in_stock    | 50                    |        |        |
      | caterpillar_boots | destocking_date    | 2015-03-05            |        |        |
      | caterpillar_boots | handmade           | 1                     |        |        |
      | caterpillar_boots | attachment         | %fixtures%/akeneo.txt |        |        |
    And the following job "footwear_variant_group_export" configuration:
      | filePath | %tmp%/variant_group_export/variant_group_export.csv |
    And I am logged in as "Julia"
    And I am on the "footwear_variant_group_export" export job page
    When I launch the export job
    And I wait for the "footwear_variant_group_export" job to finish
    Then I should see "Read 1"
    And I should see "Written 1"
    And exported file of "footwear_variant_group_export" should contain:
    """
    code;attachment;axis;comment;description-en_US-mobile;destocking_date;handmade;label-en_US;length;length-unit;manufacturer;number_in_stock;price-EUR;price-USD;side_view;type;weather_conditions
    caterpillar_boots;files/caterpillar_boots/attachment/akeneo.txt;color,size;"Best worn in winter";"Nice boots";2015-03-05;1;"Caterpillar boots";30;CENTIMETER;Caterpillar;50;100.00;150.00;files/caterpillar_boots/side_view/akeneo.jpg;VARIANT;dry,wet
    """
    And export directory of "footwear_variant_group_export" should contain the following media:
      | files/caterpillar_boots/side_view/akeneo.jpg  |
      | files/caterpillar_boots/attachment/akeneo.txt |
