Feature: Apply transformations on images
  In order to manipulate the images
  As a developer
  I need to be able to apply transformations on images

  Scenario: Apply a colorpsace transformation
    Given I apply the following transformations on the input file "%fixtures%/file_transformer/akene_angel.png"
      | type        | options               |
      | color_space | {"colorspace":"gray"} |
    Then the result file should be the same than "%fixtures%/file_transformer/akene_angel_color_gray.png"

  Scenario: Apply a resize transformation
    Given I apply the following transformations on the input file "%fixtures%/file_transformer/akene_angel.png"
      | type   | options                   |
      | resize | {"width":50, "height":75} |
    Then the result file should be the same than "%fixtures%/file_transformer/akene_angel_resize_50x75.png"

  Scenario: Apply a ppc resolution transformation
    Given I apply the following transformations on the input file "%fixtures%/file_transformer/akene_angel.png"
      | type       | options                                   |
      | resolution | {"resolution":5, "resolution-unit":"ppc"} |
    Then the result file should be the same than "%fixtures%/file_transformer/akene_angel_resolution_5ppc.png"

  Scenario: Apply a dpi resolution transformation
    Given I apply the following transformations on the input file "%fixtures%/file_transformer/akene_angel.png"
      | type       | options                                    |
      | resolution | {"resolution":72, "resolution-unit":"ppi"} |
    Then the result file should be the same than "%fixtures%/file_transformer/akene_angel_resolution_72dpi.png"

  Scenario: Apply a scale ratio transformation
    Given I apply the following transformations on the input file "%fixtures%/file_transformer/akene_angel.png"
      | type  | options      |
      | scale | {"ratio":10} |
    Then the result file should be the same than "%fixtures%/file_transformer/akene_angel_scale_10%.png"

  Scenario: Apply a scale width transformation
    Given I apply the following transformations on the input file "%fixtures%/file_transformer/akene_angel.png"
      | type  | options      |
      | scale | {"width":30} |
    Then the result file should be the same than "%fixtures%/file_transformer/akene_angel_scale_w30.png"

  Scenario: Apply a scale height transformation
    Given I apply the following transformations on the input file "%fixtures%/file_transformer/akene_angel.png"
      | type  | options       |
      | scale | {"height":30} |
    Then the result file should be the same than "%fixtures%/file_transformer/akene_angel_scale_h30.png"

  Scenario: Apply a thumbnail transformation
    Given I apply the following transformations on the input file "%fixtures%/file_transformer/akene_angel.png"
      | type      | options                    |
      | thumbnail | {"width":80, "height":120} |
    Then the result file should be the same than "%fixtures%/file_transformer/akene_angel_thumbnail.png"
