# How to activate/deactivate sorter/filter/column depending on a feature flag


This mini documentation explains how to activate/deactivate column, filters and sorted in the product datagrid.
Here, it's an example to activate/deactivate DQI columns, filters and sorters depending on the activation of `data_quality_insights` feature or not.

Note: there is also a document `product_grid_extensibility.md` in `internal_doc/enrichment/`. It should be read before this document.

## Technical explanation

Here is an example on how to add DQI columns, sorters and filters depending of the activation of the feature, in a file `Resources/config/datagrid/custom_product.yml`:

```
datagrid:
    product-grid:
        columns:
            data_quality_insights_score:
                label:         Quality score
                data_name:     data_quality_insights_score
                type:          field
                frontend_type: quality-score-badge
                feature_flag: data_quality_insights

        sorters:
            columns:
                data_quality_insights_score:
                    data_name: quality_score
                    sorter: data_quality_insights_score
                    feature_flag: data_quality_insights
        filters:
            columns:
                data_quality_insights_score:
                    type: data_quality_insights_score
                    ftype: choice
                    label: 'akeneo_data_quality_insights.product_grid.filter_label.quality_score'
                    data_name: data_quality_insights_score
                    options:
                        field_options:
                            multiple: true
                            choices:
                                A: 1
                                B: 2
                                C: 3
                                D: 4
                                E: 5
                    feature_flag: data_quality_insights
```

Note: they key `feature_flag` is not mandatory. If not provided, the columns is always activated.

## Impacts

The filters, columns and sorters are not available in the product grid when DQI is deactivated.
