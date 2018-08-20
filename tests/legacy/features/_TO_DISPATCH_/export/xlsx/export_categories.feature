@javascript
Feature: Export categories in XLSX
  In order to be able to access and modify attributes data outside PIM
  As a product manager
  I need to be able to export categories in XLSX

  Background:
    Given an "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully export categories
    Given the following job "xlsx_footwear_category_export" configuration:
      | filePath | %tmp%/category_export/category_export.xlsx |
    And I am on the "xlsx_footwear_category_export" export job page
    When I launch the export job
    And I wait for the "xlsx_footwear_category_export" job to finish
    Then xlsx file "%tmp%/category_export/category_export.xlsx" should contain 6 rows
    And the category order in the xlsx file "%tmp%/category_export/category_export.xlsx" should be following:
      | 2014_collection   |
      | summer_collection |
      | sandals           |
      | winter_collection |
      | winter_boots      |

  Scenario: Successfully export a hundred categories with a correct written number at the end of the export
    Given the following category:
      | code    | label-en_US | parent            |
      | shoe    | Shoe        | summer_collection |
      | shoe2   | Shoe2       | summer_collection |
      | shoe3   | shoe3       | summer_collection |
      | shoe4   | shoe4       | summer_collection |
      | shoe5   | shoe5       | summer_collection |
      | shoe6   | shoe6       | summer_collection |
      | shoe7   | shoe7       | summer_collection |
      | shoe8   | shoe8       | summer_collection |
      | shoe9   | shoe9       | summer_collection |
      | shoe10  | shoe10      | summer_collection |
      | shoe11  | shoe11      | summer_collection |
      | shoe12  | shoe12      | summer_collection |
      | shoe13  | shoe13      | summer_collection |
      | shoe14  | shoe14      | summer_collection |
      | shoe15  | shoe15      | summer_collection |
      | shoe16  | shoe16      | summer_collection |
      | shoe17  | shoe17      | summer_collection |
      | shoe18  | shoe18      | summer_collection |
      | shoe19  | shoe19      | summer_collection |
      | shoe20  | shoe20      | summer_collection |
      | shoe21  | shoe21      | summer_collection |
      | shoe22  | shoe22      | summer_collection |
      | shoe23  | shoe23      | summer_collection |
      | shoe24  | shoe24      | summer_collection |
      | shoe25  | shoe25      | summer_collection |
      | shoe26  | shoe26      | summer_collection |
      | shoe27  | shoe27      | summer_collection |
      | shoe28  | shoe28      | summer_collection |
      | shoe29  | shoe29      | summer_collection |
      | shoe30  | shoe30      | summer_collection |
      | shoe31  | shoe31      | summer_collection |
      | shoe32  | shoe32      | summer_collection |
      | shoe33  | shoe33      | summer_collection |
      | shoe34  | shoe34      | summer_collection |
      | shoe35  | shoe35      | summer_collection |
      | shoe36  | shoe36      | summer_collection |
      | shoe37  | shoe37      | summer_collection |
      | shoe38  | shoe38      | summer_collection |
      | shoe39  | shoe39      | summer_collection |
      | shoe40  | shoe40      | summer_collection |
      | shoe41  | shoe41      | summer_collection |
      | shoe42  | shoe42      | summer_collection |
      | shoe43  | shoe43      | summer_collection |
      | shoe44  | shoe44      | summer_collection |
      | shoe45  | shoe45      | summer_collection |
      | shoe46  | shoe46      | summer_collection |
      | shoe47  | shoe47      | summer_collection |
      | shoe48  | shoe48      | summer_collection |
      | shoe49  | shoe49      | summer_collection |
      | shoe50  | shoe50      | summer_collection |
      | shoe51  | shoe51      | summer_collection |
      | shoe52  | shoe53      | summer_collection |
      | shoe53  | shoe53      | summer_collection |
      | shoe54  | shoe54      | summer_collection |
      | shoe55  | shoe55      | summer_collection |
      | shoe56  | shoe56      | summer_collection |
      | shoe57  | shoe57      | summer_collection |
      | shoe58  | shoe58      | summer_collection |
      | shoe59  | shoe59      | summer_collection |
      | shoe60  | shoe60      | summer_collection |
      | shoe61  | shoe61      | summer_collection |
      | shoe62  | shoe62      | summer_collection |
      | shoe63  | shoe63      | summer_collection |
      | shoe64  | shoe64      | summer_collection |
      | shoe65  | shoe65      | summer_collection |
      | shoe66  | shoe66      | summer_collection |
      | shoe67  | shoe67      | summer_collection |
      | shoe68  | shoe68      | summer_collection |
      | shoe69  | shoe69      | summer_collection |
      | shoe70  | shoe70      | summer_collection |
      | shoe71  | shoe71      | summer_collection |
      | shoe72  | shoe72      | summer_collection |
      | shoe73  | shoe73      | summer_collection |
      | shoe74  | shoe74      | summer_collection |
      | shoe75  | shoe75      | summer_collection |
      | shoe76  | shoe76      | summer_collection |
      | shoe77  | shoe77      | summer_collection |
      | shoe78  | shoe78      | summer_collection |
      | shoe79  | shoe79      | summer_collection |
      | shoe80  | shoe80      | summer_collection |
      | shoe81  | shoe81      | summer_collection |
      | shoe82  | shoe82      | summer_collection |
      | shoe83  | shoe83      | summer_collection |
      | shoe84  | shoe84      | summer_collection |
      | shoe85  | shoe85      | summer_collection |
      | shoe86  | shoe86      | summer_collection |
      | shoe87  | shoe87      | summer_collection |
      | shoe88  | shoe88      | summer_collection |
      | shoe89  | shoe89      | summer_collection |
      | shoe90  | shoe90      | summer_collection |
      | shoe91  | shoe91      | summer_collection |
      | shoe92  | shoe92      | summer_collection |
      | shoe93  | shoe93      | summer_collection |
      | shoe94  | shoe94      | summer_collection |
      | shoe95  | shoe95      | summer_collection |
      | shoe96  | shoe96      | summer_collection |
      | shoe97  | shoe97      | summer_collection |
      | shoe98  | shoe98      | summer_collection |
      | shoe99  | shoe99      | summer_collection |
      | shoe100 | shoe100     | summer_collection |
    And the following job "xlsx_footwear_category_export" configuration:
      | filePath | %tmp%/category_export/category_export.xlsx |
    And I am on the "xlsx_footwear_category_export" export job page
    When I launch the export job
    And I wait for the "xlsx_footwear_category_export" job to finish
    Then I should see the text "read 105"
    Then I should see the text "written 105"

  Scenario: Successfully export categories into several files
    Given the following categories:
      | code          | label-en_US   | parent       |
      | black_rangers | Black Rangers | winter_boots |
      | grey_rangers  | Grey Rangers  | winter_boots |
    And the following job "xlsx_footwear_category_export" configuration:
      | filePath     | %tmp%/xlsx_footwear_category_export/xlsx_footwear_category_export.xlsx |
      | linesPerFile | 3                                                                      |
    When I am on the "xlsx_footwear_category_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_category_export" job to finish
    Then I should see "xlsx_footwear_category_export_1.xlsx" on the "Download generated files" dropdown button
    And I should see "xlsx_footwear_category_export_2.xlsx" on the "Download generated files" dropdown button
    And I should see "xlsx_footwear_category_export_3.xlsx" on the "Download generated files" dropdown button
    And exported xlsx file 1 of "xlsx_footwear_category_export" should contain:
      | code              | label-en_US       | parent            |
      | 2014_collection   | 2014 collection   |                   |
      | summer_collection | Summer collection | 2014_collection   |
      | sandals           | Sandals           | summer_collection |
    And exported xlsx file 2 of "xlsx_footwear_category_export" should contain:
      | code              | label-en_US       | parent            |
      | winter_collection | Winter collection | 2014_collection   |
      | winter_boots      | Winter boots      | winter_collection |
      | black_rangers     | Black Rangers     | winter_boots      |
    And exported xlsx file 3 of "xlsx_footwear_category_export" should contain:
      | code         | label-en_US  | parent       |
      | grey_rangers | Grey Rangers | winter_boots |
