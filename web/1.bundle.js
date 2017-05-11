webpackJsonp([1,2],Array(448).concat([
/* 448 */
/* unknown exports provided */
/* all exports used */
/*!************************!*\
  !*** ./src/Pim/Bundle ***!
  \************************/
/***/ (function(module, exports, __webpack_require__) {

var map = {
	"pimanalytics/js/data-collector": 215,
	"pimanalytics/js/patch-fetcher": 97,
	"pimdashboard/js/abstract-widget": 62,
	"pimdashboard/js/completeness-widget": 95,
	"pimdashboard/js/last-operations-widget": 96,
	"pimdashboard/js/widget-container": 99,
	"pimdashboard/templates/completeness-widget.html": 100,
	"pimdashboard/templates/last-operations-widget.html": 101,
	"pimdashboard/templates/view-all-btn.html": 102,
	"pimdatagrid/js/datafilter-builder": 505,
	"pimdatagrid/js/datafilter/collection-filters-manager": 484,
	"pimdatagrid/js/datafilter/filter/abstract-filter": 86,
	"pimdatagrid/js/datafilter/filter/ajax-choice-filter": 506,
	"pimdatagrid/js/datafilter/filter/choice-filter": 89,
	"pimdatagrid/js/datafilter/filter/date-filter": 485,
	"pimdatagrid/js/datafilter/filter/datetime-filter": 507,
	"pimdatagrid/js/datafilter/filter/metric-filter": 508,
	"pimdatagrid/js/datafilter/filter/multiselect-filter": 486,
	"pimdatagrid/js/datafilter/filter/none-filter": 509,
	"pimdatagrid/js/datafilter/filter/number-filter": 87,
	"pimdatagrid/js/datafilter/filter/price-filter": 510,
	"pimdatagrid/js/datafilter/filter/product_category-filter": 216,
	"pimdatagrid/js/datafilter/filter/product_completeness-filter": 511,
	"pimdatagrid/js/datafilter/filter/product_scope-filter": 512,
	"pimdatagrid/js/datafilter/filter/select-filter": 450,
	"pimdatagrid/js/datafilter/filter/select-row-filter": 513,
	"pimdatagrid/js/datafilter/filter/select2-choice-filter": 514,
	"pimdatagrid/js/datafilter/filter/select2-rest-choice-filter": 515,
	"pimdatagrid/js/datafilter/filter/text-filter": 57,
	"pimdatagrid/js/datafilter/filters-manager": 487,
	"pimdatagrid/js/datafilter/formatter/abstract-formatter": 516,
	"pimdatagrid/js/datagrid-builder": 47,
	"pimdatagrid/js/datagrid/action-launcher": 217,
	"pimdatagrid/js/datagrid/action/abstract-action": 39,
	"pimdatagrid/js/datagrid/action/ajax-action": 218,
	"pimdatagrid/js/datagrid/action/configure-columns-action": 517,
	"pimdatagrid/js/datagrid/action/delete-action": 219,
	"pimdatagrid/js/datagrid/action/mass-action": 220,
	"pimdatagrid/js/datagrid/action/model-action": 48,
	"pimdatagrid/js/datagrid/action/navigate-action": 63,
	"pimdatagrid/js/datagrid/action/refresh-collection-action": 221,
	"pimdatagrid/js/datagrid/action/reset-collection-action": 222,
	"pimdatagrid/js/datagrid/action/tab-redirect-action": 223,
	"pimdatagrid/js/datagrid/actions-panel": 224,
	"pimdatagrid/js/datagrid/body": 225,
	"pimdatagrid/js/datagrid/cell/action-cell": 226,
	"pimdatagrid/js/datagrid/cell/boolean-cell": 518,
	"pimdatagrid/js/datagrid/cell/date-cell": 227,
	"pimdatagrid/js/datagrid/cell/datetime-cell": 228,
	"pimdatagrid/js/datagrid/cell/html-cell": 229,
	"pimdatagrid/js/datagrid/cell/integer-cell": 519,
	"pimdatagrid/js/datagrid/cell/number-cell": 520,
	"pimdatagrid/js/datagrid/cell/select-cell": 521,
	"pimdatagrid/js/datagrid/cell/select-row-cell": 64,
	"pimdatagrid/js/datagrid/cell/string-cell": 49,
	"pimdatagrid/js/datagrid/column/action-column": 230,
	"pimdatagrid/js/datagrid/formatter/cell-formatter": 231,
	"pimdatagrid/js/datagrid/grid": 235,
	"pimdatagrid/js/datagrid/grid-views/collection": 232,
	"pimdatagrid/js/datagrid/grid-views/model": 233,
	"pimdatagrid/js/datagrid/grid-views/view": 234,
	"pimdatagrid/js/datagrid/header": 238,
	"pimdatagrid/js/datagrid/header-cell/header-cell": 236,
	"pimdatagrid/js/datagrid/header-cell/select-all-header-cell": 237,
	"pimdatagrid/js/datagrid/listener/abstract-listener": 451,
	"pimdatagrid/js/datagrid/listener/callback-listener": 522,
	"pimdatagrid/js/datagrid/listener/column-form-listener": 523,
	"pimdatagrid/js/datagrid/listener/oro-column-form-listener": 488,
	"pimdatagrid/js/datagrid/page-size": 239,
	"pimdatagrid/js/datagrid/pagination": 241,
	"pimdatagrid/js/datagrid/pagination-input": 240,
	"pimdatagrid/js/datagrid/row": 242,
	"pimdatagrid/js/datagrid/state": 34,
	"pimdatagrid/js/datagrid/state-listener": 524,
	"pimdatagrid/js/datagrid/toolbar": 243,
	"pimdatagrid/js/datagrid/widget/export-widget": 525,
	"pimdatagrid/js/fetcher/datagrid-view-fetcher": 526,
	"pimdatagrid/js/loading-mask": 15,
	"pimdatagrid/js/multiselect-decorator": 455,
	"pimdatagrid/js/pageable-collection": 37,
	"pimdatagrid/js/remover/datagrid-view-remover": 244,
	"pimdatagrid/js/saver/datagrid-view-saver": 65,
	"pimdatagrid/lib/backbone-pageable": 245,
	"pimdatagrid/lib/backgrid/backgrid": 20,
	"pimdatagrid/lib/multiselect/jquery.multiselect": 457,
	"pimdatagrid/lib/multiselect/jquery.multiselect.filter": 456,
	"pimdatagrid/templates/configure-columns-action.html": 463,
	"pimdatagrid/templates/datagrid/action-launcher-button.html": 103,
	"pimdatagrid/templates/datagrid/action-launcher-list-item.html": 104,
	"pimdatagrid/templates/datagrid/actions-group.html": 105,
	"pimdatagrid/templates/filter/date-filter.html": 464,
	"pimdatagrid/templates/filter/metric-filter.html": 465,
	"pimdatagrid/templates/filter/select2-choice-filter.html": 453,
	"pimenrich/js/app": 98,
	"pimenrich/js/association-type/form/delete": 246,
	"pimenrich/js/attribute-option/create": 458,
	"pimenrich/js/attribute-option/form": 247,
	"pimenrich/js/channel/form/delete": 248,
	"pimenrich/js/channel/form/properties/conversion-unit": 249,
	"pimenrich/js/channel/form/properties/general": 250,
	"pimenrich/js/channel/form/properties/general/currencies": 251,
	"pimenrich/js/channel/form/properties/general/locales": 252,
	"pimenrich/js/channel/form/save": 253,
	"pimenrich/js/common/column-list-view": 489,
	"pimenrich/js/common/property": 18,
	"pimenrich/js/controller/association-type": 254,
	"pimenrich/js/controller/base": 19,
	"pimenrich/js/controller/channel/edit": 255,
	"pimenrich/js/controller/common/index": 256,
	"pimenrich/js/controller/family": 257,
	"pimenrich/js/controller/form": 50,
	"pimenrich/js/controller/group": 66,
	"pimenrich/js/controller/group-type": 258,
	"pimenrich/js/controller/job-execution": 259,
	"pimenrich/js/controller/job-instance": 260,
	"pimenrich/js/controller/product": 261,
	"pimenrich/js/controller/redirect": 262,
	"pimenrich/js/controller/registry": 527,
	"pimenrich/js/controller/role": 263,
	"pimenrich/js/controller/system": 264,
	"pimenrich/js/controller/template": 51,
	"pimenrich/js/controller/user": 265,
	"pimenrich/js/controller/variant-group": 266,
	"pimenrich/js/date-context": 42,
	"pimenrich/js/error/error": 22,
	"pimenrich/js/family/form/attributes": 267,
	"pimenrich/js/family/form/attributes/attributes": 67,
	"pimenrich/js/family/form/attributes/toolbar": 268,
	"pimenrich/js/family/form/attributes/toolbar/add-select/attribute-group/select": 269,
	"pimenrich/js/family/form/attributes/toolbar/add-select/attribute/select": 68,
	"pimenrich/js/family/form/delete": 270,
	"pimenrich/js/family/form/properties/general": 271,
	"pimenrich/js/family/form/properties/general/attribute-as-label": 272,
	"pimenrich/js/family/form/properties/general/code": 273,
	"pimenrich/js/family/form/properties/general/translation": 274,
	"pimenrich/js/family/form/save": 275,
	"pimenrich/js/family/mass-edit/attributes": 276,
	"pimenrich/js/family/mass-edit/hidden-field-updater": 277,
	"pimenrich/js/family/mass-edit/toolbar/add-select/attribute/select": 278,
	"pimenrich/js/fetcher/attribute-fetcher": 528,
	"pimenrich/js/fetcher/attribute-group-fetcher": 529,
	"pimenrich/js/fetcher/base-fetcher": 56,
	"pimenrich/js/fetcher/completeness-fetcher": 530,
	"pimenrich/js/fetcher/fetcher-registry": 4,
	"pimenrich/js/fetcher/locale-fetcher": 531,
	"pimenrich/js/fetcher/product-fetcher": 532,
	"pimenrich/js/fetcher/variant-group-fetcher": 533,
	"pimenrich/js/filter/attribute/attribute": 28,
	"pimenrich/js/filter/attribute/boolean": 279,
	"pimenrich/js/filter/attribute/date": 280,
	"pimenrich/js/filter/attribute/identifier": 281,
	"pimenrich/js/filter/attribute/media": 282,
	"pimenrich/js/filter/attribute/metric": 283,
	"pimenrich/js/filter/attribute/number": 284,
	"pimenrich/js/filter/attribute/price-collection": 285,
	"pimenrich/js/filter/attribute/select": 286,
	"pimenrich/js/filter/attribute/string": 287,
	"pimenrich/js/filter/filter": 31,
	"pimenrich/js/filter/product/category": 288,
	"pimenrich/js/filter/product/category/selector": 289,
	"pimenrich/js/filter/product/completeness": 290,
	"pimenrich/js/filter/product/enabled": 291,
	"pimenrich/js/filter/product/family": 292,
	"pimenrich/js/filter/product/updated": 293,
	"pimenrich/js/form/builder": 16,
	"pimenrich/js/form/cache-invalidator": 88,
	"pimenrich/js/form/common/add-select/footer": 294,
	"pimenrich/js/form/common/add-select/line": 69,
	"pimenrich/js/form/common/add-select/select": 70,
	"pimenrich/js/form/common/attributes": 52,
	"pimenrich/js/form/common/attributes/attribute-group-selector": 295,
	"pimenrich/js/form/common/attributes/copy": 297,
	"pimenrich/js/form/common/attributes/copy-field": 296,
	"pimenrich/js/form/common/back-to-grid": 298,
	"pimenrich/js/form/common/delete": 25,
	"pimenrich/js/form/common/download-file": 299,
	"pimenrich/js/form/common/edit-form": 300,
	"pimenrich/js/form/common/form-tabs": 301,
	"pimenrich/js/form/common/grid": 53,
	"pimenrich/js/form/common/group-selector": 302,
	"pimenrich/js/form/common/index/confirm-button": 534,
	"pimenrich/js/form/common/index/create-button": 303,
	"pimenrich/js/form/common/index/grid": 304,
	"pimenrich/js/form/common/index/index": 305,
	"pimenrich/js/form/common/label": 40,
	"pimenrich/js/form/common/meta/created": 306,
	"pimenrich/js/form/common/meta/status": 307,
	"pimenrich/js/form/common/meta/updated": 308,
	"pimenrich/js/form/common/properties/general": 309,
	"pimenrich/js/form/common/properties/translation": 71,
	"pimenrich/js/form/common/redirect": 72,
	"pimenrich/js/form/common/save": 32,
	"pimenrich/js/form/common/save-buttons": 310,
	"pimenrich/js/form/common/save-form": 311,
	"pimenrich/js/form/common/state": 312,
	"pimenrich/js/form/common/tab/history": 313,
	"pimenrich/js/form/common/tab/properties": 314,
	"pimenrich/js/form/config-provider": 54,
	"pimenrich/js/form/form-modal": 535,
	"pimenrich/js/form/registry": 315,
	"pimenrich/js/formatter/choices/base": 45,
	"pimenrich/js/formatter/date-formatter": 60,
	"pimenrich/js/generator/media-url-generator": 490,
	"pimenrich/js/grid/view-selector": 321,
	"pimenrich/js/grid/view-selector-create-view": 316,
	"pimenrich/js/grid/view-selector-current": 317,
	"pimenrich/js/grid/view-selector-line": 318,
	"pimenrich/js/grid/view-selector-remove-view": 319,
	"pimenrich/js/grid/view-selector-save-view": 320,
	"pimenrich/js/group-type/form/delete": 322,
	"pimenrich/js/group/form/delete": 323,
	"pimenrich/js/group/form/meta/product-count": 324,
	"pimenrich/js/group/form/products": 325,
	"pimenrich/js/group/form/properties/general": 326,
	"pimenrich/js/group/form/save": 327,
	"pimenrich/js/i18n": 9,
	"pimenrich/js/job/common/edit/content/data/help": 328,
	"pimenrich/js/job/common/edit/field/decimal-separator": 329,
	"pimenrich/js/job/common/edit/field/field": 55,
	"pimenrich/js/job/common/edit/field/select": 73,
	"pimenrich/js/job/common/edit/field/switch": 330,
	"pimenrich/js/job/common/edit/field/text": 331,
	"pimenrich/js/job/common/edit/label": 332,
	"pimenrich/js/job/common/edit/launch": 74,
	"pimenrich/js/job/common/edit/meta": 333,
	"pimenrich/js/job/common/edit/properties": 334,
	"pimenrich/js/job/common/edit/save": 75,
	"pimenrich/js/job/common/edit/upload": 336,
	"pimenrich/js/job/common/edit/upload-launch": 335,
	"pimenrich/js/job/common/edit/validation": 337,
	"pimenrich/js/job/common/label": 338,
	"pimenrich/js/job/execution/auto-refresh": 339,
	"pimenrich/js/job/execution/download-archives-buttons": 340,
	"pimenrich/js/job/execution/download-log": 341,
	"pimenrich/js/job/execution/show-profile": 342,
	"pimenrich/js/job/execution/summary-table": 343,
	"pimenrich/js/job/export/edit/delete": 344,
	"pimenrich/js/job/export/edit/save": 345,
	"pimenrich/js/job/import/edit/delete": 346,
	"pimenrich/js/job/import/edit/save": 347,
	"pimenrich/js/job/product/edit/content": 348,
	"pimenrich/js/job/product/edit/content/data": 349,
	"pimenrich/js/job/product/edit/content/data/add-select/attribute/select": 350,
	"pimenrich/js/job/product/edit/content/data/default-attribute-filters": 351,
	"pimenrich/js/job/product/edit/content/readonly": 352,
	"pimenrich/js/job/product/edit/content/structure": 353,
	"pimenrich/js/job/product/edit/content/structure/attributes": 355,
	"pimenrich/js/job/product/edit/content/structure/attributes-selector": 354,
	"pimenrich/js/job/product/edit/content/structure/locales": 356,
	"pimenrich/js/job/product/edit/content/structure/scope": 357,
	"pimenrich/js/job/product/edit/field/date-format": 358,
	"pimenrich/js/jquery.wizard": 536,
	"pimenrich/js/jstree/jquery.jstree.nested_switch": 359,
	"pimenrich/js/jstree/jquery.jstree.tree_selector": 90,
	"pimenrich/js/manager/attribute-group-manager": 76,
	"pimenrich/js/manager/attribute-manager": 24,
	"pimenrich/js/manager/group-manager": 360,
	"pimenrich/js/manager/history-item-manager": 537,
	"pimenrich/js/manager/product-manager": 58,
	"pimenrich/js/manager/variant-group-manager": 361,
	"pimenrich/js/page-title": 23,
	"pimenrich/js/pim-async-tab": 362,
	"pimenrich/js/pim-attributeoptionview": 538,
	"pimenrich/js/pim-currencyfield": 539,
	"pimenrich/js/pim-init": 363,
	"pimenrich/js/pim-item-tableview": 540,
	"pimenrich/js/pim-item-view": 541,
	"pimenrich/js/pim-optionform": 491,
	"pimenrich/js/pim-popinform": 542,
	"pimenrich/js/pim-scopable": 543,
	"pimenrich/js/product/create/create": 364,
	"pimenrich/js/product/create/form": 365,
	"pimenrich/js/product/field-manager": 17,
	"pimenrich/js/product/field/boolean-field": 544,
	"pimenrich/js/product/field/date-field": 545,
	"pimenrich/js/product/field/field": 84,
	"pimenrich/js/product/field/media-field": 546,
	"pimenrich/js/product/field/metric-field": 547,
	"pimenrich/js/product/field/multi-select-field": 492,
	"pimenrich/js/product/field/number-field": 548,
	"pimenrich/js/product/field/price-collection-field": 549,
	"pimenrich/js/product/field/simple-select-field": 493,
	"pimenrich/js/product/field/text-field": 550,
	"pimenrich/js/product/field/textarea-field": 551,
	"pimenrich/js/product/field/wysiwyg-field": 552,
	"pimenrich/js/product/form": 3,
	"pimenrich/js/product/form/associations": 366,
	"pimenrich/js/product/form/attributes": 367,
	"pimenrich/js/product/form/attributes/add-select/attribute/line": 368,
	"pimenrich/js/product/form/attributes/add-select/attribute/select": 41,
	"pimenrich/js/product/form/attributes/completeness": 369,
	"pimenrich/js/product/form/attributes/locale-specific": 370,
	"pimenrich/js/product/form/attributes/localizable": 371,
	"pimenrich/js/product/form/attributes/validation": 373,
	"pimenrich/js/product/form/attributes/validation-error": 372,
	"pimenrich/js/product/form/attributes/variant-group": 374,
	"pimenrich/js/product/form/categories": 375,
	"pimenrich/js/product/form/delete": 376,
	"pimenrich/js/product/form/download-pdf": 377,
	"pimenrich/js/product/form/locale-switcher": 77,
	"pimenrich/js/product/form/mass-edit/attributes": 378,
	"pimenrich/js/product/form/mass-edit/hidden-field-updater": 379,
	"pimenrich/js/product/form/meta/change-family": 380,
	"pimenrich/js/product/form/meta/family": 381,
	"pimenrich/js/product/form/meta/groups": 382,
	"pimenrich/js/product/form/panel/comments": 383,
	"pimenrich/js/product/form/panel/completeness": 384,
	"pimenrich/js/product/form/panel/history": 385,
	"pimenrich/js/product/form/panel/panels": 386,
	"pimenrich/js/product/form/panel/selector": 387,
	"pimenrich/js/product/form/product-label": 388,
	"pimenrich/js/product/form/save": 390,
	"pimenrich/js/product/form/save-and-back": 389,
	"pimenrich/js/product/form/scope-switcher": 78,
	"pimenrich/js/product/form/sequential-edit": 391,
	"pimenrich/js/product/form/status-switcher": 392,
	"pimenrich/js/provider/to-fill-field-provider": 79,
	"pimenrich/js/remover/association-type-remover": 393,
	"pimenrich/js/remover/base-remover": 26,
	"pimenrich/js/remover/channel": 394,
	"pimenrich/js/remover/family": 395,
	"pimenrich/js/remover/group-remover": 396,
	"pimenrich/js/remover/group-type-remover": 397,
	"pimenrich/js/remover/job-instance-export-remover": 398,
	"pimenrich/js/remover/job-instance-import-remover": 399,
	"pimenrich/js/remover/product-remover": 400,
	"pimenrich/js/remover/variant-group-remover": 401,
	"pimenrich/js/route-matcher": 402,
	"pimenrich/js/router": 13,
	"pimenrich/js/saver/base-saver": 29,
	"pimenrich/js/saver/channel": 403,
	"pimenrich/js/saver/entity-saver": 404,
	"pimenrich/js/saver/family": 405,
	"pimenrich/js/saver/group-saver": 406,
	"pimenrich/js/saver/job-instance-export-saver": 407,
	"pimenrich/js/saver/job-instance-import-saver": 408,
	"pimenrich/js/saver/product-saver": 409,
	"pimenrich/js/saver/variant-group-saver": 410,
	"pimenrich/js/security-context": 21,
	"pimenrich/js/translator": 2,
	"pimenrich/js/tree-associate.jstree": 411,
	"pimenrich/js/tree-manage.jstree": 553,
	"pimenrich/js/tree-view.jstree": 412,
	"pimenrich/js/user-context": 5,
	"pimenrich/js/variant-group/form/attributes/add-select/attribute/select": 413,
	"pimenrich/js/variant-group/form/delete": 414,
	"pimenrich/js/variant-group/form/no-attribute": 415,
	"pimenrich/js/variant-group/form/properties/general": 416,
	"pimenrich/js/variant-group/form/save": 417,
	"pimenrich/lib/translator": 80,
	"pimenrich/templates/attribute-option/edit.html": 466,
	"pimenrich/templates/attribute-option/form.html": 106,
	"pimenrich/templates/attribute-option/index.html": 467,
	"pimenrich/templates/attribute-option/show.html": 468,
	"pimenrich/templates/attribute-option/validation-error.html": 469,
	"pimenrich/templates/channel/tab/properties/conversion-unit.html": 107,
	"pimenrich/templates/channel/tab/properties/general.html": 108,
	"pimenrich/templates/channel/tab/properties/general/category-tree.html": 109,
	"pimenrich/templates/channel/tab/properties/general/currencies.html": 110,
	"pimenrich/templates/channel/tab/properties/general/locales.html": 111,
	"pimenrich/templates/error/error.html": 112,
	"pimenrich/templates/export/common/edit/field/field.html": 113,
	"pimenrich/templates/export/common/edit/field/select.html": 114,
	"pimenrich/templates/export/common/edit/field/switch.html": 115,
	"pimenrich/templates/export/common/edit/field/text.html": 116,
	"pimenrich/templates/export/common/edit/launch.html": 117,
	"pimenrich/templates/export/common/edit/meta.html": 118,
	"pimenrich/templates/export/common/edit/properties.html": 119,
	"pimenrich/templates/export/common/edit/upload.html": 120,
	"pimenrich/templates/export/common/edit/validation.html": 121,
	"pimenrich/templates/export/product/edit/content.html": 122,
	"pimenrich/templates/export/product/edit/content/data.html": 123,
	"pimenrich/templates/export/product/edit/content/data/help.html": 124,
	"pimenrich/templates/export/product/edit/content/structure.html": 125,
	"pimenrich/templates/export/product/edit/content/structure/attribute-list.html": 126,
	"pimenrich/templates/export/product/edit/content/structure/attributes-selector.html": 127,
	"pimenrich/templates/export/product/edit/content/structure/attributes.html": 128,
	"pimenrich/templates/export/product/edit/content/structure/locales.html": 129,
	"pimenrich/templates/export/product/edit/content/structure/scope.html": 130,
	"pimenrich/templates/family/tab/attributes.html": 131,
	"pimenrich/templates/family/tab/attributes/attributes.html": 132,
	"pimenrich/templates/family/tab/attributes/toolbar.html": 133,
	"pimenrich/templates/family/tab/general/attribute-as-label.html": 134,
	"pimenrich/templates/filter/attribute/boolean.html": 135,
	"pimenrich/templates/filter/attribute/date.html": 136,
	"pimenrich/templates/filter/attribute/media.html": 137,
	"pimenrich/templates/filter/attribute/metric.html": 138,
	"pimenrich/templates/filter/attribute/number.html": 139,
	"pimenrich/templates/filter/attribute/price-collection.html": 140,
	"pimenrich/templates/filter/attribute/select.html": 141,
	"pimenrich/templates/filter/attribute/string.html": 142,
	"pimenrich/templates/filter/filter.html": 143,
	"pimenrich/templates/filter/product/category.html": 144,
	"pimenrich/templates/filter/product/category/selector.html": 145,
	"pimenrich/templates/filter/product/completeness.html": 146,
	"pimenrich/templates/filter/product/enabled.html": 147,
	"pimenrich/templates/filter/product/family.html": 148,
	"pimenrich/templates/filter/product/identifier.html": 149,
	"pimenrich/templates/filter/product/updated.html": 150,
	"pimenrich/templates/filter/simpleselect.html": 501,
	"pimenrich/templates/form/add-select/footer.html": 151,
	"pimenrich/templates/form/add-select/line.html": 152,
	"pimenrich/templates/form/add-select/select.html": 153,
	"pimenrich/templates/form/back-to-grid.html": 154,
	"pimenrich/templates/form/delete.html": 155,
	"pimenrich/templates/form/download-file.html": 156,
	"pimenrich/templates/form/edit-form.html": 157,
	"pimenrich/templates/form/form-tabs.html": 158,
	"pimenrich/templates/form/grid.html": 159,
	"pimenrich/templates/form/group-selector.html": 160,
	"pimenrich/templates/form/index/confirm-button.html": 470,
	"pimenrich/templates/form/index/create-button.html": 161,
	"pimenrich/templates/form/index/index.html": 162,
	"pimenrich/templates/form/meta/created.html": 163,
	"pimenrich/templates/form/meta/status.html": 164,
	"pimenrich/templates/form/meta/updated.html": 165,
	"pimenrich/templates/form/properties/general.html": 166,
	"pimenrich/templates/form/properties/input.html": 167,
	"pimenrich/templates/form/properties/translation.html": 61,
	"pimenrich/templates/form/redirect.html": 168,
	"pimenrich/templates/form/save-buttons.html": 169,
	"pimenrich/templates/form/save.html": 502,
	"pimenrich/templates/form/state.html": 170,
	"pimenrich/templates/form/tab/attributes.html": 171,
	"pimenrich/templates/form/tab/attributes/attribute-group-selector.html": 172,
	"pimenrich/templates/form/tab/attributes/copy-field.html": 173,
	"pimenrich/templates/form/tab/attributes/copy.html": 174,
	"pimenrich/templates/form/tab/history.html": 503,
	"pimenrich/templates/form/tab/properties.html": 175,
	"pimenrich/templates/form/tab/section.html": 176,
	"pimenrich/templates/grid/view-selector-create-view-label-input.html": 177,
	"pimenrich/templates/grid/view-selector-create-view.html": 178,
	"pimenrich/templates/grid/view-selector-current.html": 179,
	"pimenrich/templates/grid/view-selector-line.html": 180,
	"pimenrich/templates/grid/view-selector-remove-view.html": 181,
	"pimenrich/templates/grid/view-selector-save-view.html": 182,
	"pimenrich/templates/grid/view-selector.html": 183,
	"pimenrich/templates/group/meta/product-count.html": 184,
	"pimenrich/templates/group/tab/properties/general.html": 185,
	"pimenrich/templates/i18n/flag.html": 186,
	"pimenrich/templates/job-execution/auto-refresh.html": 187,
	"pimenrich/templates/job-execution/download-archives-buttons.html": 188,
	"pimenrich/templates/job-execution/summary-table.html": 189,
	"pimenrich/templates/product/create-error.html": 190,
	"pimenrich/templates/product/create-popin.html": 191,
	"pimenrich/templates/product/download-pdf.html": 192,
	"pimenrich/templates/product/field/boolean.html": 471,
	"pimenrich/templates/product/field/date.html": 472,
	"pimenrich/templates/product/field/field.html": 193,
	"pimenrich/templates/product/field/media.html": 473,
	"pimenrich/templates/product/field/metric.html": 474,
	"pimenrich/templates/product/field/multi-select.html": 475,
	"pimenrich/templates/product/field/number.html": 476,
	"pimenrich/templates/product/field/price-collection.html": 477,
	"pimenrich/templates/product/field/simple-select.html": 478,
	"pimenrich/templates/product/field/text.html": 479,
	"pimenrich/templates/product/field/textarea.html": 454,
	"pimenrich/templates/product/form/add-select/attribute/line.html": 194,
	"pimenrich/templates/product/locale-switcher.html": 195,
	"pimenrich/templates/product/meta/change-family-modal.html": 196,
	"pimenrich/templates/product/meta/family.html": 197,
	"pimenrich/templates/product/meta/group-modal.html": 198,
	"pimenrich/templates/product/meta/groups.html": 199,
	"pimenrich/templates/product/panel/comments.html": 200,
	"pimenrich/templates/product/panel/completeness.html": 201,
	"pimenrich/templates/product/panel/container.html": 202,
	"pimenrich/templates/product/panel/history.html": 203,
	"pimenrich/templates/product/panel/selector.html": 204,
	"pimenrich/templates/product/scope-switcher.html": 205,
	"pimenrich/templates/product/sequential-edit.html": 206,
	"pimenrich/templates/product/status-switcher.html": 207,
	"pimenrich/templates/product/tab/association-panes.html": 208,
	"pimenrich/templates/product/tab/associations.html": 209,
	"pimenrich/templates/product/tab/attributes/validation-error.html": 210,
	"pimenrich/templates/product/tab/attributes/variant-group.html": 211,
	"pimenrich/templates/product/tab/categories.html": 212,
	"pimenrich/templates/variant-group/form/no-attribute.html": 213,
	"pimenrich/templates/variant-group/tab/properties/general.html": 214,
	"pimimportexport/js/job-execution-view": 554,
	"pimnavigation/js/navigation/abstract-view": 459,
	"pimnavigation/js/navigation/collection": 460,
	"pimnavigation/js/navigation/dotmenu/item-view": 494,
	"pimnavigation/js/navigation/dotmenu/view": 495,
	"pimnavigation/js/navigation/favorites/view": 555,
	"pimnavigation/js/navigation/model": 452,
	"pimnavigation/js/navigation/pinbar/collection": 496,
	"pimnavigation/js/navigation/pinbar/item-view": 497,
	"pimnavigation/js/navigation/pinbar/model": 461,
	"pimnavigation/js/navigation/pinbar/view": 556,
	"pimnavigation/lib/jquery-form/jquery.form": 418,
	"pimnavigation/lib/url/url.min": 557,
	"pimnotification/js/indicator": 498,
	"pimnotification/js/notification-list": 499,
	"pimnotification/js/notifications": 558,
	"pimnotification/templates/notification/notification-footer.html": 480,
	"pimnotification/templates/notification/notification-list.html": 481,
	"pimnotification/templates/notification/notification.html": 482,
	"pimreferencedata/js/product/field/reference-multi-select-field": 559,
	"pimreferencedata/js/product/field/reference-simple-select-field": 560,
	"pimui/js/app": 33,
	"pimui/js/delete-confirmation": 81,
	"pimui/js/error": 85,
	"pimui/js/form/state.js": 561,
	"pimui/js/form/system/group/loading-message": 562,
	"pimui/js/init-layout": 419,
	"pimui/js/jquery-setup": 563,
	"pimui/js/jquery.sidebarize": 420,
	"pimui/js/layout": 421,
	"pimui/js/mediator": 8,
	"pimui/js/messenger": 12,
	"pimui/js/modal": 36,
	"pimui/js/pim-datepicker": 44,
	"pimui/js/pim-dialog": 14,
	"pimui/js/pim-dialogform": 91,
	"pimui/js/pim-fileinput": 500,
	"pimui/js/pim-formupdatelistener": 564,
	"pimui/js/pim-initselect2": 30,
	"pimui/js/pim-saveformstate": 422,
	"pimui/js/pim-ui": 92,
	"pimui/js/pim-wysiwyg": 93,
	"pimui/js/templates/system/group/loading-message.html": 483,
	"pimui/js/tools": 94,
	"pimui/lib/backbone.bootstrap-modal": 565,
	"pimui/lib/backbone/backbone": 6,
	"pimui/lib/base64/base64": 566,
	"pimui/lib/bootstrap-datetimepicker/js/bootstrap-datetimepicker": 423,
	"pimui/lib/bootstrap-switch/bootstrap.switch": 43,
	"pimui/lib/bootstrap/js/bootstrap": 35,
	"pimui/lib/dropzonejs/dist/dropzone-amd-module.js": 567,
	"pimui/lib/jquery-numeric/jquery.numeric": 424,
	"pimui/lib/jquery-ui/jquery-ui-1.11.4.custom.min": 59,
	"pimui/lib/jquery/jquery-1.10.2": 1,
	"pimui/lib/json2/json2": 425,
	"pimui/lib/jstree/jquery.hotkeys": 568,
	"pimui/lib/jstree/jquery.jstree": 46,
	"pimui/lib/select2/select2": 11,
	"pimui/lib/slimbox2/slimbox2": 462,
	"pimui/lib/text/text": 504,
	"pimui/lib/underscore/underscore": 0,
	"pimuser/js/init-user": 426,
	"undefined": 427
};
function webpackContext(req) {
	return __webpack_require__(webpackContextResolve(req));
};
function webpackContextResolve(req) {
	var id = map[req];
	if(!(id + 1)) // check for number or string
		throw new Error("Cannot find module '" + req + "'.");
	return id;
};
webpackContext.keys = function webpackContextKeys() {
	return Object.keys(map);
};
webpackContext.resolve = webpackContextResolve;
module.exports = webpackContext;
webpackContext.id = 448;

/***/ }),
/* 449 */,
/* 450 */
/* unknown exports provided */
/* all exports used */
/*!**********************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter/filter/select-filter.js ***!
  \**********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/translator */ 2), __webpack_require__(/*! oro/datafilter/abstract-filter */ 86), __webpack_require__(/*! oro/multiselect-decorator */ 455)], __WEBPACK_AMD_DEFINE_RESULT__ = function(_, __, AbstractFilter, MultiselectDecorator) {
    'use strict';

    /**
     * Select filter: filter value as select option
     *
     * @export  oro/datafilter/select-filter
     * @class   oro.datafilter.SelectFilter
     * @extends oro.datafilter.AbstractFilter
     */
    return AbstractFilter.extend({
        /**
         * Filter template
         *
         * @property
         */
        template: _.template(
            '<div class="AknActionButton filter-select filter-criteria-selector">' +
                '<% if (showLabel) { %><%= label %>: <% } %>' +
                '<select>' +
                    '<% _.each(options, function (option) { %>' +
                        '<option value="<%= option.value %>"<% if (option.value == emptyValue.type) { %> selected="selected"<% } %>><%= option.label %></option>' +
                    '<% }); %>' +
                '</select>' +
            '</div>' +
            '<% if (canDisable) { %><a href="<%= nullLink %>" class="disable-filter"><i class="icon-remove hide-text"><%- _.__("Close") %></i></a><% } %>'
        ),

        /**
         * Should default value be added to options list
         *
         * @property
         */
        populateDefault: true,

        /**
         * Selector for filter area
         *
         * @property
         */
        containerSelector: '.filter-select',

        /**
         * Selector for close button
         *
         * @property
         */
        disableSelector: '.disable-filter',

        /**
         * Selector for widget button
         *
         * @property
         */
        buttonSelector: '.select-filter-widget.ui-multiselect:first',

        /**
         * Selector for select input element
         *
         * @property
         */
        inputSelector: 'select',

        /**
         * Select widget object
         *
         * @property
         */
        selectWidget: null,

        /**
         * Minimum widget menu width, calculated depends on filter options
         *
         * @property
         */
        minimumWidth: null,

        /**
         * Select widget options
         *
         * @property
         */
        widgetOptions: {
            multiple: false,
            classes: 'AknActionButton-selectButton select-filter-widget'
        },

        /**
         * Select widget menu opened flag
         *
         * @property
         */
        selectDropdownOpened: false,

        /**
         * @property {Boolean}
         */
        contextSearch: true,

        /**
         * Filter events
         *
         * @property
         */
        events: {
            'keydown select': '_preventEnterProcessing',
            'click .filter-select': '_onClickFilterArea',
            'click .disable-filter': '_onClickDisableFilter',
            'change select': '_onSelectChange'
        },

        /**
         * Initialize.
         *
         * @param {Object} options
         */
        initialize: function() {
            // init filter content options if it was not initialized so far
            if (_.isUndefined(this.choices)) {
                this.choices = [];
            }
            // temp code to keep backward compatible
            this.choices = _.map(this.choices, function(option, i) {
                return _.isString(option) ? {value: i, label: option} : option;
            });

            // init empty value object if it was not initialized so far
            if (_.isUndefined(this.emptyValue)) {
                this.emptyValue = {
                    value: ''
                };
            }

            AbstractFilter.prototype.initialize.apply(this, arguments);
        },

        /**
         * Render filter template
         *
         * @return {*}
         */
        render: function () {
            var options =  this.choices.slice(0);
            this.$el.empty();

            if (this.populateDefault) {
                options.unshift({value: '', label: this.placeholder});
            }

            this.$el.append(
                this.template({
                    label: this.label,
                    showLabel: this.showLabel,
                    options: options,
                    placeholder: this.placeholder,
                    nullLink: this.nullLink,
                    canDisable: this.canDisable,
                    emptyValue: this.emptyValue
                })
            );

            this._updateDOMValue();
            this._initializeSelectWidget();

            return this;
        },

        /**
         * Initialize multiselect widget
         *
         * @protected
         */
        _initializeSelectWidget: function() {
            this.selectWidget = new MultiselectDecorator({
                element: this.$(this.inputSelector),
                parameters: _.extend({
                    noneSelectedText: this.placeholder,
                    selectedText: _.bind(function(numChecked, numTotal, checkedItems) {
                        return this._getSelectedText(checkedItems);
                    }, this),
                    position: {
                        my: 'left top+2',
                        at: 'left bottom',
                        of: this.$(this.containerSelector)
                    },
                    open: _.bind(function() {
                        this.selectWidget.onOpenDropdown();
                        this._setDropdownWidth();
                        this._setButtonPressed(this.$(this.containerSelector), true);
                        this.selectDropdownOpened = true;
                    }, this),
                    close: _.bind(function() {
                        this._setButtonPressed(this.$(this.containerSelector), false);
                        setTimeout(_.bind(function() {
                            this.selectDropdownOpened = false;
                        }, this), 100);
                    }, this)
                }, this.widgetOptions),
                contextSearch: this.contextSearch
            });

            this.selectWidget.setViewDesign(this);
            this.$(this.buttonSelector)
                .append('<span class="AknActionButton-caret AknCaret"></span>')
                .find('span:first-child').addClass('filter-criteria-hint');
        },

        /**
         * Get text for filter hint
         *
         * @param {Array} checkedItems
         * @protected
         */
        _getSelectedText: function(checkedItems) {
            if (_.isEmpty(checkedItems)) {
                return this.placeholder;
            }

            var elements = [];
            _.each(checkedItems, function(element) {
                var title = element.getAttribute('title');
                if (title) {
                    elements.push(title);
                }
            });
            return elements.join(', ');
        },

        /**
         * Get criteria hint value
         *
         * @return {String}
         */
        _getCriteriaHint: function() {
            var value = (arguments.length > 0) ? this._getDisplayValue(arguments[0]) : this._getDisplayValue();
            var choice = _.find(this.choices, function (c) {
                return (c.value == value.value);
            });
            return !_.isUndefined(choice) ? choice.label : this.placeholder;
        },

        /**
         * Set design for select dropdown
         *
         * @protected
         */
        _setDropdownWidth: function() {
            if (!this.minimumWidth) {
                this.minimumWidth = this.selectWidget.getMinimumDropdownWidth() + 22;
            }
            var widget = this.selectWidget.getWidget(),
                filterWidth = this.$(this.containerSelector).width(),
                requiredWidth = Math.max(filterWidth + 10, this.minimumWidth);
            widget.width(requiredWidth).css('min-width', requiredWidth + 'px');
            widget.find('input[type="search"]').width(requiredWidth - 22);
        },

        /**
         * Open/close select dropdown
         *
         * @param {Event} e
         * @protected
         */
        _onClickFilterArea: function(e) {
            if (!this.selectDropdownOpened) {
                setTimeout(_.bind(function() {
                    this.selectWidget.multiselect('open');
                }, this), 50);
            } else {
                setTimeout(_.bind(function() {
                    this.selectWidget.multiselect('close');
                }, this), 50);
            }

            e.stopPropagation();
        },

        /**
         * Triggers change data event
         *
         * @protected
         */
        _onSelectChange: function() {
            // set value
            this.setValue(this._formatRawValue(this._readDOMValue()));

            // update dropdown
            var widget = this.$(this.containerSelector);
            this.selectWidget.updateDropdownPosition(widget);
        },

        /**
         * Handle click on filter disabler
         *
         * @param {Event} e
         */
        _onClickDisableFilter: function(e) {
            e.preventDefault();
            this.disable();
        },

        /**
         * @inheritDoc
         */
        _isNewValueUpdated: function(newValue) {
            return !_.isEqual(this.getValue().value || '', newValue.value);
        },

        /**
         * @inheritDoc
         */
        _onValueUpdated: function(newValue, oldValue) {
            AbstractFilter.prototype._onValueUpdated.apply(this, arguments);

            if (this.selectWidget) {
                this.selectWidget.multiselect('refresh');
            }
        },

        /**
         * @inheritDoc
         */
        _writeDOMValue: function(value) {
            this._setInputValue(this.inputSelector, value.value);
            return this;
        },

        /**
         * @inheritDoc
         */
        _readDOMValue: function() {
            return {
                value: this._getInputValue(this.inputSelector)
            };
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 451 */
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datagrid/listener/abstract-listener.js ***!
  \**************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/*jslint browser: true, nomen: true*/
/*global define*/
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! underscore */ 0), __webpack_require__(/*! jquery */ 1), __webpack_require__(/*! backbone */ 6)], __WEBPACK_AMD_DEFINE_RESULT__ = function (_, $, Backbone) {
    'use strict';

    /**
     * Abstarct listener for datagrid
     *
     * @export  oro/datagrid/abstract-listener
     * @class   oro.datagrid.AbstractListener
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
        /** @param {String} Column name of cells that will be listened for changing their values */
        columnName: 'id',

        /** @param {String} Model field that contains data */
        dataField: 'id',

        /**
         * Initialize listener object
         *
         * @param {Object} options
         */
        initialize: function (options) {
            if (!_.has(options, 'columnName')) {
                throw new Error('Data column name is not specified');
            }
            this.columnName = options.columnName;

            if (options.dataField) {
                this.dataField = options.dataField;
            }

            Backbone.Model.prototype.initialize.apply(this, arguments);

            if (!options.$gridContainer) {
                throw new Error('gridSelector is not specified');
            }
            this.$gridContainer = options.$gridContainer;
            this.gridName = options.gridName;

            this.setDatagridAndSubscribe();
        },

        /**
         * Set datagrid instance
         */
        setDatagridAndSubscribe: function () {
            this.$gridContainer.on('datagrid:change:' + this.gridName, this._onModelEdited.bind(this));
        },

        /**
         * Process cell editing
         *
         * @param {Backbone.Model} model
         * @protected
         */
        _onModelEdited: function (e, model) {
            if (!model.hasChanged(this.columnName)) {
                return;
            }

            var value = model.get(this.dataField);

            if (!_.isUndefined(value)) {
                this._processValue(value, model);
            }
        },

        /**
         * Process value
         *
         * @param {*} value Value of model property with name of this.dataField
         * @param {Backbone.Model} model
         * @protected
         * @abstract
         */
        _processValue: function (value, model) {
            throw new Error('_processValue method is abstract and must be implemented');
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 452 */
/* unknown exports provided */
/* all exports used */
/*!*********************************************************************************!*\
  !*** ./src/Pim/Bundle/NavigationBundle/Resources/public/js/navigation/model.js ***!
  \*********************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! underscore */ 0), __webpack_require__(/*! routing */ 7), __webpack_require__(/*! backbone */ 6)], __WEBPACK_AMD_DEFINE_RESULT__ = function(_, routing, Backbone) {
    'use strict';

    /**
     * @export  oro/navigation/model
     * @class   oro.navigation.Model
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
        defaults: {
            title: '',
            url: null,
            position: null,
            type: null
        },

        url: function() {
            var base = _.result(this, 'urlRoot') || _.result(this.collection, 'url');
            if (base && base.indexOf(this.get('type')) === -1) {
                base += (base.charAt(base.length - 1) === '/' ? '' : '/') + this.get('type');
            } else if (!base) {
                base = routing.generate('oro_api_get_navigationitems', { type: this.get('type') });
            }
            if (this.isNew()) {
                return base;
            }
            return base + (base.charAt(base.length - 1) === '/' ? '' : '/') + 'ids/' + encodeURIComponent(this.id);
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 453 */
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/templates/filter/select2-choice-filter.html ***!
  \****************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"AknFilterChoice choicefilter\">\n    <div class=\"AknFilterChoice-operator AknDropdown\">\n        <% if (emptyChoice) { %>\n            <button type=\"button\" class=\"AknActionButton AknActionButton--big AknActionButton--noRightBorder dropdown-toggle\" data-toggle=\"dropdown\">\n                <%= selectedOperatorLabel %>\n                <span class=\"AknCaret\"></span>\n            </button>\n            <ul class=\"dropdown-menu\">\n                <% _.each(operatorChoices, function (label, operator) { %>\n                    <li<% if (selectedOperator == operator) { %> class=\"active\"<% } %>>\n                        <a class=\"operator_choice\" href=\"#\" data-value=\"<%= operator %>\"><%= label %></a>\n                    </li>\n                <% }); %>\n            </ul>\n        <% } %>\n    </div>\n    <input type=\"text\" name=\"value\" class=\"AknTextField AknTextField--select2 AknTextField--noRadius AknFilterChoice-field select-field\">\n    <button type=\"button\" class=\"AknFilterChoice-button AknButton AknButton--apply AknButton--noLeftRadius filter-update\"><%- _.__('Update') %></button>\n</div>\n"

/***/ }),
/* 454 */
/* unknown exports provided */
/* all exports used */
/*!********************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/product/field/textarea.html ***!
  \********************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<textarea id=\"<%- fieldId %>\" class=\"AknTextareaField\" data-locale=\"<%- value.locale %>\" data-scope=\"<%- value.scope %>\" <%- editMode === 'view' ? 'disabled' : '' %>><%- value.data %></textarea>\n"

/***/ }),
/* 455 */
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/multiselect-decorator.js ***!
  \************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/mediator */ 8), __webpack_require__(/*! jquery.multiselect */ 457), __webpack_require__(/*! jquery.multiselect.filter */ 456)], __WEBPACK_AMD_DEFINE_RESULT__ = function($, _, mediator) {
    'use strict';

    /**
     * Multiselect decorator class.
     * Wraps multiselect widget and provides design modifications
     *
     * @export oro/multiselect-decorator
     * @class  oro.MultiselectDecorator
     */
    var MultiselectDecorator = function(options) {
        this.initialize(options);
    };

    MultiselectDecorator.prototype = {
        /**
         * Multiselect widget element container
         *
         * @property {Object}
         */
        element: null,

        /**
         * Default multiselect widget parameters
         *
         * @property {Object}
         */
        parameters: {
            height: 'auto'
        },

        /**
         * @property {Boolean}
         */
        contextSearch: true,

        /**
         * Minimum width of this multiselect
         *
         * @property {int}
         */
        minimumWidth: null,

        /**
         * Initialize all required properties
         */
        initialize: function(options) {
            if (!options.element) {
                throw new Error('Select element must be defined');
            }
            this.element = options.element;

            if (options.parameters) {
                _.extend(this.parameters, options.parameters);
            }

            if (_.has(options, 'contextSearch')) {
                this.contextSearch = options.contextSearch;
            }

            // initialize multiselect widget
            this.multiselect(this.parameters);

            // initialize multiselect filter
            if (this.contextSearch) {
                this.multiselectfilter({
                    label: '',
                    placeholder: '',
                    autoReset: true
                });
            }

            // destroy DOM garbage after change page via hash-navigation
            mediator.once('hash_navigation_request:start', function() {
                if (this.element.closest('body').length) {
                    this.multiselect('destroy');
                    this.element.hide();
                }
            }, this);
        },

        /**
         * Set design for view
         *
         * @param {Backbone.View} view
         */
        setViewDesign: function(view) {
            view.$('.ui-multiselect').removeClass('ui-widget').removeClass('ui-state-default');
            view.$('.ui-multiselect span.ui-icon').remove();
        },

        /**
         * Fix dropdown design
         *
         * @protected
         */
        _setDropdownDesign: function() {
            var widget = this.getWidget();
            widget.addClass('dropdown-menu');
            widget.addClass('AknDropdown-menu');
            widget.removeClass('ui-widget-content');
            widget.removeClass('ui-widget');
            widget.find('.ui-widget-header').removeClass('ui-widget-header');
            widget.find('.ui-multiselect-filter').removeClass('ui-multiselect-filter');
            widget.find('ul li label').removeClass('ui-corner-all');
        },

        /**
         * Action performed on dropdown open
         */
        onOpenDropdown: function() {
            this._setDropdownDesign();
            this.getWidget().find('input[type="search"]').focus();
            $('body').trigger('click');
        },

        /**
         * Get minimum width of dropdown menu
         *
         * @return {Number}
         */
        getMinimumDropdownWidth: function() {
            if (_.isNull(this.minimumWidth)) {
                var elements = this.getWidget().find('.ui-multiselect-checkboxes li');
                var margin = 26;

                var longestElement = _.max(elements, function (element) {
                    var htmlContent = $(element).find('span:first').html();
                    var length = htmlContent ? htmlContent.length : 0;

                    return length;
                });

                this.minimumWidth = $(longestElement).find('span:first').width() + margin;
            }

            return this.minimumWidth;
        },

        /**
         * Get multiselect widget
         *
         * @return {Object}
         */
        getWidget: function() {
            try {
                return this.multiselect('widget');
            } catch (error) {
                return $('.ui-multiselect-menu.pimmultiselect');
            }
        },

        /**
         * Proxy for multiselect method
         *
         * @param functionName
         * @return {Object}
         */
        multiselect: function(functionName) {
            return this.element.multiselect(functionName);
        },

        /**
         * Proxy for multiselectfilter method
         *
         * @param functionName
         * @return {Object}
         */
        multiselectfilter: function(functionName) {
            return this.element.multiselectfilter(functionName);
        },

        /**
         *  Set dropdown position according to button element
         *
         * @param {Object} button
         */
        updateDropdownPosition: function(button) {
            var position = button.offset();

            this.getWidget().css({
                top: position.top + button.outerHeight(),
                right: position.right
            });
        }
    };

    return MultiselectDecorator;
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 456 */
/* unknown exports provided */
/* all exports used */
/*!*****************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/lib/multiselect/jquery.multiselect.filter.js ***!
  \*****************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/*
 * jQuery MultiSelect UI ImportExport Filtering Plugin 1.5pre
 * Copyright (c) 2012 Eric Hynds
 *
 * http://www.erichynds.com/jquery/jquery-ui-multiselect-widget/
 *
 * Depends:
 *   - jQuery UI MultiSelect widget
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($) {
  var rEscape = /[\-\[\]{}()*+?.,\\\^$|#\s]/g;

  $.widget('ech.multiselectfilter', {

    options: {
      label: 'Filter:',
      width: null, /* override default width set in css file (px). null will inherit */
      placeholder: 'Enter keywords',
      autoReset: false
    },

    _create: function() {
      var opts = this.options;
      var elem = $(this.element);

      // get the multiselect instance
      var instance = (this.instance = (elem.data('echMultiselect') || elem.data("multiselect")));

      // store header; add filter class so the close/check all/uncheck all links can be positioned correctly
      var header = (this.header = instance.menu.find('.ui-multiselect-header').addClass('ui-multiselect-hasfilter'));

      // wrapper elem
      var wrapper = (this.wrapper = $('<div class="ui-multiselect-filter">' + (opts.label.length ? opts.label : '') + '<input placeholder="'+opts.placeholder+'" type="search"' + (/\d/.test(opts.width) ? 'style="width:'+opts.width+'px"' : '') + ' /></div>').prependTo(this.header));

      // reference to the actual inputs
      this.inputs = instance.menu.find('input[type="checkbox"], input[type="radio"]');

      // build the input box
      this.input = wrapper.find('input').bind({
        keydown: function(e) {
          // prevent the enter key from submitting the form / closing the widget
          if(e.which === 13) {
            e.preventDefault();
          }
        },
        keyup: $.proxy(this._handler, this),
        click: $.proxy(this._handler, this)
      });

      // cache input values for searching
      this.updateCache();

      // rewrite internal _toggleChecked fn so that when checkAll/uncheckAll is fired,
      // only the currently filtered elements are checked
      instance._toggleChecked = function(flag, group) {
        var $inputs = (group && group.length) ?  group : this.labels.find('input');
        var _self = this;

        // do not include hidden elems if the menu isn't open.
        var selector = instance._isOpen ?  ':disabled, :hidden' : ':disabled';

        $inputs = $inputs
          .not(selector)
          .each(this._toggleState('checked', flag));

        // update text
        this.update();

        // gather an array of the values that actually changed
        var values = $inputs.map(function() {
          return this.value;
        }).get();

        // select option tags
        this.element.find('option').filter(function() {
          if(!this.disabled && $.inArray(this.value, values) > -1) {
            _self._toggleState('selected', flag).call(this);
          }
        });

        // trigger the change event on the select
        if($inputs.length) {
          this.element.trigger('change');
        }
      };

      // rebuild cache when multiselect is updated
      var doc = $(document).bind('multiselectrefresh', $.proxy(function() {
        this.updateCache();
        this._handler();
      }, this));

      // automatically reset the widget on close?
      if(this.options.autoReset) {
        doc.bind('multiselectclose', $.proxy(this._reset, this));
      }
    },

    // thx for the logic here ben alman
    _handler: function(e) {
      var term = $.trim(this.input[0].value.toLowerCase()),

      // speed up lookups
      rows = this.rows, inputs = this.inputs, cache = this.cache;

      if(!term) {
        rows.show();
      } else {
        rows.hide();

        var regex = new RegExp(term.replace(rEscape, "\\$&"), 'gi');

        this._trigger("filter", e, $.map(cache, function(v, i) {
          var found = false;
          if(v.search(regex) !== -1) {
            found = true;
          } else {
            // look for 'value' attibute if innerHTML doesn't match
            var val = rows.eq(i).find('input').attr('value');
            if(val.search(regex) !== -1) {
              found = true;
            }
          }

          if(found) {
            rows.eq(i).show();
            return inputs.get(i);
          }

          return null;
        }));
      }

      // show/hide optgroups
      this.instance.menu.find(".ui-multiselect-optgroup-label").each(function() {
        var $this = $(this);
        var isVisible = $this.nextUntil('.ui-multiselect-optgroup-label').filter(function() {
          return $.css(this, "display") !== 'none';
        }).length;

        $this[isVisible ? 'show' : 'hide']();
      });
    },

    _reset: function() {
      this.input.val('').trigger('keyup');
    },

    updateCache: function() {
      // each list item
      this.rows = this.instance.menu.find(".ui-multiselect-checkboxes li:not(.ui-multiselect-optgroup-label)");

      // cache
      this.cache = this.element.children().map(function() {
        var elem = $(this);

        // account for optgroups
        if(this.tagName.toLowerCase() === "optgroup") {
          elem = elem.children();
        }

        return elem.map(function() {
          return this.innerHTML.toLowerCase();
        }).get();
      }).get();
    },

    widget: function() {
      return this.wrapper;
    },

    destroy: function() {
      $.Widget.prototype.destroy.call(this);
      this.input.val('').trigger("keyup");
      this.wrapper.remove();
    }
  });

}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 457 */
/* unknown exports provided */
/* all exports used */
/*!**********************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/lib/multiselect/jquery.multiselect.js ***!
  \**********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/*
 * jQuery MultiSelect UI ImportExport 1.14pre
 * Copyright (c) 2012 Eric Hynds
 *
 * http://www.erichynds.com/jquery/jquery-ui-multiselect-widget/
 *
 * Depends:
 *   - jQuery 1.4.2+
 *   - jQuery UI 1.8 widget factory
 *
 * Optional:
 *   - jQuery UI effects
 *   - jQuery UI position utility
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($) {

  var multiselectID = 0;
  var $doc = $(document);

  $.widget("ech.multiselect", {

    // default options
    options: {
      header: true,
      height: 175,
      minWidth: 225,
      classes: '',
      checkAllText: 'Check all',
      uncheckAllText: 'Uncheck all',
      noneSelectedText: 'Select options',
      selectedText: '# selected',
      selectedList: 0,
      show: null,
      hide: null,
      autoOpen: false,
      multiple: true,
      position: {}
    },

    _create: function() {
      var el = this.element.hide();
      var o = this.options;

      this.speed = $.fx.speeds._default; // default speed for effects
      this._isOpen = false; // assume no

      // create a unique namespace for events that the widget
      // factory cannot unbind automatically. Use eventNamespace if on
      // jQuery UI 1.9+, and otherwise fallback to a custom string.
      this._namespaceID = this.eventNamespace || ('multiselect' + multiselectID);

      var button = (this.button = $('<button type="button"><span class="ui-icon ui-icon-triangle-1-s"></span></button>'))
        .addClass('ui-multiselect ui-widget ui-state-default ui-corner-all')
        .addClass(o.classes)
        .attr({ 'title':el.attr('title'), 'aria-haspopup':true, 'tabIndex':el.attr('tabIndex') })
        .insertAfter(el),

        buttonlabel = (this.buttonlabel = $('<span />'))
          .html(o.noneSelectedText)
          .appendTo(button),

        menu = (this.menu = $('<div />'))
          .addClass('ui-multiselect-menu ui-widget ui-widget-content ui-corner-all')
          .addClass(o.classes)
          .appendTo(document.body),

        header = (this.header = $('<div />'))
          .addClass('ui-widget-header ui-corner-all ui-multiselect-header ui-helper-clearfix')
          .appendTo(menu),

        headerLinkContainer = (this.headerLinkContainer = $('<ul />'))
          .addClass('ui-helper-reset')
          .html(function() {
            if(o.header === true) {
              return '<li><a class="ui-multiselect-all" href="#"><span class="ui-icon ui-icon-check"></span><span>' + o.checkAllText + '</span></a></li><li><a class="ui-multiselect-none" href="#"><span class="ui-icon ui-icon-closethick"></span><span>' + o.uncheckAllText + '</span></a></li>';
            } else if(typeof o.header === "string") {
              return '<li>' + o.header + '</li>';
            } else {
              return '';
            }
          })
          .append('<li class="ui-multiselect-close"><a href="#" class="ui-multiselect-close"><span class="ui-icon ui-icon-circle-close"></span></a></li>')
          .appendTo(header),

        checkboxContainer = (this.checkboxContainer = $('<ul />'))
          .addClass('ui-multiselect-checkboxes ui-helper-reset')
          .appendTo(menu);

        // perform event bindings
        this._bindEvents();

        // build menu
        this.refresh(true);

        // some addl. logic for single selects
        if(!o.multiple) {
          menu.addClass('ui-multiselect-single');
        }

        // bump unique ID
        multiselectID++;
    },

    _init: function() {
      if(this.options.header === false) {
        this.header.hide();
      }
      if(!this.options.multiple) {
        this.headerLinkContainer.find('.ui-multiselect-all, .ui-multiselect-none').hide();
      }
      if(this.options.autoOpen) {
        this.open();
      }
      if(this.element.is(':disabled')) {
        this.disable();
      }
    },

    refresh: function(init) {
      var el = this.element;
      var o = this.options;
      var menu = this.menu;
      var checkboxContainer = this.checkboxContainer;
      var optgroups = [];
      var html = "";
      var id = el.attr('id') || multiselectID++; // unique ID for the label & option tags

      // build items
      el.find('option').each(function(i) {
        var $this = $(this);
        var parent = this.parentNode;
        var title = this.innerHTML;
        var description = this.title;
        var value = this.value;
        var inputID = 'ui-multiselect-' + (this.id || id + '-option-' + i);
        var isDisabled = this.disabled;
        var isSelected = this.selected;
        var labelClasses = [ 'ui-corner-all' ];
        var liClasses = (isDisabled ? 'ui-multiselect-disabled ' : ' ') + this.className;
        var optLabel;

        // is this an optgroup?
        if(parent.tagName === 'OPTGROUP') {
          optLabel = parent.getAttribute('label');

          // has this optgroup been added already?
          if($.inArray(optLabel, optgroups) === -1) {
            html += '<li class="ui-multiselect-optgroup-label ' + parent.className + '"><a href="#">' + optLabel + '</a></li>';
            optgroups.push(optLabel);
          }
        }

        if(isDisabled) {
          labelClasses.push('ui-state-disabled');
        }

        // browsers automatically select the first option
        // by default with single selects
        if(isSelected && !o.multiple) {
          labelClasses.push('ui-state-active');
        }

        html += '<li class="' + liClasses + '">';

        // create the label
        html += '<label for="' + inputID + '" title="' + description + '" class="' + labelClasses.join(' ') + '">';
        html += '<input id="' + inputID + '" name="multiselect_' + id + '" type="' + (o.multiple ? "checkbox" : "radio") + '" value="' + value + '" title="' + title + '"';

        // pre-selected?
        if(isSelected) {
          html += ' checked="checked"';
          html += ' aria-selected="true"';
        }

        // disabled?
        if(isDisabled) {
          html += ' disabled="disabled"';
          html += ' aria-disabled="true"';
        }

        // add the title and close everything off
        html += ' /><span>' + title + '</span></label></li>';
      });

      // insert into the DOM
      checkboxContainer.html(html);

      // cache some moar useful elements
      this.labels = menu.find('label');
      this.inputs = this.labels.children('input');

      // set widths
      this._setButtonWidth();
      this._setMenuWidth();

      // remember default value
      this.button[0].defaultValue = this.update();

      // broadcast refresh event; useful for widgets
      if(!init) {
        this._trigger('refresh');
      }
    },

    // updates the button text. call refresh() to rebuild
    update: function() {
      var o = this.options;
      var $inputs = this.inputs;
      var $checked = $inputs.filter(':checked');
      var numChecked = $checked.length;
      var value;

      if(numChecked === 0) {
        value = o.noneSelectedText;
      } else {
        if($.isFunction(o.selectedText)) {
          value = o.selectedText.call(this, numChecked, $inputs.length, $checked.get());
        } else if(/\d/.test(o.selectedList) && o.selectedList > 0 && numChecked <= o.selectedList) {
          value = $checked.map(function() { return $(this).next().html(); }).get().join(', ');
        } else {
          value = o.selectedText.replace('#', numChecked).replace('#', $inputs.length);
        }
      }

      this._setButtonValue(value);

      return value;
    },

    // this exists as a separate method so that the developer
    // can easily override it.
    _setButtonValue: function(value) {
      this.buttonlabel.text(value);
    },

    // binds events
    _bindEvents: function() {
      var self = this;
      var button = this.button;

      function clickHandler() {
        self[ self._isOpen ? 'close' : 'open' ]();
        return false;
      }

      // webkit doesn't like it when you click on the span :(
      button
        .find('span')
        .bind('click.multiselect', clickHandler);

      // button events
      button.bind({
        click: clickHandler,
        keypress: function(e) {
          switch(e.which) {
            case 27: // esc
              case 38: // up
              case 37: // left
              self.close();
            break;
            case 39: // right
              case 40: // down
              self.open();
            break;
          }
        },
        mouseenter: function() {
          if(!button.hasClass('ui-state-disabled')) {
            $(this).addClass('ui-state-hover');
          }
        },
        mouseleave: function() {
          $(this).removeClass('ui-state-hover');
        },
        focus: function() {
          if(!button.hasClass('ui-state-disabled')) {
            $(this).addClass('ui-state-focus');
          }
        },
        blur: function() {
          $(this).removeClass('ui-state-focus');
        }
      });

      // header links
      this.header.delegate('a', 'click.multiselect', function(e) {
        // close link
        if($(this).hasClass('ui-multiselect-close')) {
          self.close();

          // check all / uncheck all
        } else {
          self[$(this).hasClass('ui-multiselect-all') ? 'checkAll' : 'uncheckAll']();
        }

        e.preventDefault();
      });

      // optgroup label toggle support
      this.menu.delegate('li.ui-multiselect-optgroup-label a', 'click.multiselect', function(e) {
        e.preventDefault();

        var $this = $(this);
        var $inputs = $this.parent().nextUntil('li.ui-multiselect-optgroup-label').find('input:visible:not(:disabled)');
        var nodes = $inputs.get();
        var label = $this.parent().text();

        // trigger event and bail if the return is false
        if(self._trigger('beforeoptgrouptoggle', e, { inputs:nodes, label:label }) === false) {
          return;
        }

        // toggle inputs
        self._toggleChecked(
          $inputs.filter(':checked').length !== $inputs.length,
          $inputs
        );

        self._trigger('optgrouptoggle', e, {
          inputs: nodes,
          label: label,
          checked: nodes[0].checked
        });
      })
      .delegate('label', 'mouseenter.multiselect', function() {
        if(!$(this).hasClass('ui-state-disabled')) {
          self.labels.removeClass('ui-state-hover');
          $(this).addClass('ui-state-hover').find('input').focus();
        }
      })
      .delegate('label', 'keydown.multiselect', function(e) {
        e.preventDefault();

        switch(e.which) {
          case 9: // tab
            case 27: // esc
            self.close();
          break;
          case 38: // up
            case 40: // down
            case 37: // left
            case 39: // right
            self._traverse(e.which, this);
          break;
          case 13: // enter
            $(this).find('input')[0].click();
          break;
        }
      })
      .delegate('input[type="checkbox"], input[type="radio"]', 'click.multiselect', function(e) {
        var $this = $(this);
        var val = this.value;
        var checked = this.checked;
        var tags = self.element.find('option');

        // bail if this input is disabled or the event is cancelled
        if(this.disabled || self._trigger('click', e, { value: val, text: this.title, checked: checked }) === false) {
          e.preventDefault();
          return;
        }

        // make sure the input has focus. otherwise, the esc key
        // won't close the menu after clicking an item.
        $this.focus();

        // toggle aria state
        $this.attr('aria-selected', checked);

        // change state on the original option tags
        tags.each(function() {
          if(this.value === val) {
            this.selected = checked;
          } else if(!self.options.multiple) {
            this.selected = false;
          }
        });

        // some additional single select-specific logic
        if(!self.options.multiple) {
          self.labels.removeClass('ui-state-active');
          $this.closest('label').toggleClass('ui-state-active', checked);

          // close menu
          self.close();
        }

        // fire change on the select box
        self.element.trigger("change");

        // setTimeout is to fix multiselect issue #14 and #47. caused by jQuery issue #3827
        // http://bugs.jquery.com/ticket/3827
        setTimeout($.proxy(self.update, self), 10);
      });

      // close each widget when clicking on any other element/anywhere else on the page
      $doc.bind('mousedown.' + this._namespaceID, function(event) {
        var target = event.target;

        if(self._isOpen
            && !$.contains(self.menu[0], target)
            && !$.contains(self.button[0], target)
            && target !== self.button[0]
            && target !== self.menu[0])
        {
          self.close();
        }
      });

      // deal with form resets.  the problem here is that buttons aren't
      // restored to their defaultValue prop on form reset, and the reset
      // handler fires before the form is actually reset.  delaying it a bit
      // gives the form inputs time to clear.
      $(this.element[0].form).bind('reset.multiselect', function() {
        setTimeout($.proxy(self.refresh, self), 10);
      });
    },

    // set button width
    _setButtonWidth: function() {
      var width = this.element.outerWidth();
      var o = this.options;

      if(/\d/.test(o.minWidth) && width < o.minWidth) {
        width = o.minWidth;
      }

      // set widths
      this.button.outerWidth(width);
    },

    // set menu width
    _setMenuWidth: function() {
      var m = this.menu;
      m.outerWidth(this.button.outerWidth());
    },

    // move up or down within the menu
    _traverse: function(which, start) {
      var $start = $(start);
      var moveToLast = which === 38 || which === 37;

      // select the first li that isn't an optgroup label / disabled
      $next = $start.parent()[moveToLast ? 'prevAll' : 'nextAll']('li:not(.ui-multiselect-disabled, .ui-multiselect-optgroup-label)')[ moveToLast ? 'last' : 'first']();

      // if at the first/last element
      if(!$next.length) {
        var $container = this.menu.find('ul').last();

        // move to the first/last
        this.menu.find('label')[ moveToLast ? 'last' : 'first' ]().trigger('mouseover');

        // set scroll position
        $container.scrollTop(moveToLast ? $container.height() : 0);

      } else {
        $next.find('label').trigger('mouseover');
      }
    },

    // This is an internal function to toggle the checked property and
    // other related attributes of a checkbox.
    //
    // The context of this function should be a checkbox; do not proxy it.
    _toggleState: function(prop, flag) {
      return function() {
        if(!this.disabled) {
          this[ prop ] = flag;
        }

        if(flag) {
          this.setAttribute('aria-selected', true);
        } else {
          this.removeAttribute('aria-selected');
        }
      };
    },

    _toggleChecked: function(flag, group) {
      var $inputs = (group && group.length) ?  group : this.inputs;
      var self = this;

      // toggle state on inputs
      $inputs.each(this._toggleState('checked', flag));

      // give the first input focus
      $inputs.eq(0).focus();

      // update button text
      this.update();

      // gather an array of the values that actually changed
      var values = $inputs.map(function() {
        return this.value;
      }).get();

      // toggle state on original option tags
      this.element
        .find('option')
        .each(function() {
          if(!this.disabled && $.inArray(this.value, values) > -1) {
            self._toggleState('selected', flag).call(this);
          }
        });

      // trigger the change event on the select
      if($inputs.length) {
        this.element.trigger("change");
      }
    },

    _toggleDisabled: function(flag) {
      this.button.attr({ 'disabled':flag, 'aria-disabled':flag })[ flag ? 'addClass' : 'removeClass' ]('ui-state-disabled');

      var inputs = this.menu.find('input');
      var key = "ech-multiselect-disabled";

      if(flag) {
        // remember which elements this widget disabled (not pre-disabled)
        // elements, so that they can be restored if the widget is re-enabled.
        inputs = inputs.filter(':enabled').data(key, true)
      } else {
        inputs = inputs.filter(function() {
          return $.data(this, key) === true;
        }).removeData(key);
      }

      inputs
        .attr({ 'disabled':flag, 'arial-disabled':flag })
        .parent()[ flag ? 'addClass' : 'removeClass' ]('ui-state-disabled');

      this.element.attr({
        'disabled':flag,
        'aria-disabled':flag
      });
    },

    // open the menu
    open: function(e) {
      var self = this;
      var button = this.button;
      var menu = this.menu;
      var speed = this.speed;
      var o = this.options;
      var args = [];

      // bail if the multiselectopen event returns false, this widget is disabled, or is already open
      if(this._trigger('beforeopen') === false || button.hasClass('ui-state-disabled') || this._isOpen) {
        return;
      }

      var $container = menu.find('ul').last();
      var effect = o.show;

      // figure out opening effects/speeds
      if($.isArray(o.show)) {
        effect = o.show[0];
        speed = o.show[1] || self.speed;
      }

      // if there's an effect, assume jQuery UI is in use
      // build the arguments to pass to show()
      if(effect) {
        args = [ effect, speed ];
      }

      // set the scroll of the checkbox container
      $container.scrollTop(0).height(o.height);

      // positon
      this.position();

      // show the menu, maybe with a speed/effect combo
      $.fn.show.apply(menu, args);

      // select the first not disabled option
      // triggering both mouseover and mouseover because 1.4.2+ has a bug where triggering mouseover
      // will actually trigger mouseenter.  the mouseenter trigger is there for when it's eventually fixed
      this.labels.filter(':not(.ui-state-disabled)').eq(0).trigger('mouseover').trigger('mouseenter').find('input').trigger('focus');

      button.addClass('ui-state-active');
      this._isOpen = true;
      this._trigger('open');
    },

    // close the menu
    close: function() {
      if(this._trigger('beforeclose') === false) {
        return;
      }

      var o = this.options;
      var effect = o.hide;
      var speed = this.speed;
      var args = [];

      // figure out opening effects/speeds
      if($.isArray(o.hide)) {
        effect = o.hide[0];
        speed = o.hide[1] || this.speed;
      }

      if(effect) {
        args = [ effect, speed ];
      }

      $.fn.hide.apply(this.menu, args);
      this.button.removeClass('ui-state-active').trigger('blur').trigger('mouseleave');
      this._isOpen = false;
      this._trigger('close');
    },

    enable: function() {
      this._toggleDisabled(false);
    },

    disable: function() {
      this._toggleDisabled(true);
    },

    checkAll: function(e) {
      this._toggleChecked(true);
      this._trigger('checkAll');
    },

    uncheckAll: function() {
      this._toggleChecked(false);
      this._trigger('uncheckAll');
    },

    getChecked: function() {
      return this.menu.find('input').filter(':checked');
    },

    destroy: function() {
      // remove classes + data
      $.Widget.prototype.destroy.call(this);

      // unbind events
      $doc.unbind(this._namespaceID);

      this.button.remove();
      this.menu.remove();
      this.element.show();

      return this;
    },

    isOpen: function() {
      return this._isOpen;
    },

    widget: function() {
      return this.menu;
    },

    getButton: function() {
      return this.button;
    },

    position: function() {
      var o = this.options;

      // use the position utility if it exists and options are specifified
      if($.ui.position && !$.isEmptyObject(o.position)) {
        o.position.of = o.position.of || this.button;

        this.menu
          .show()
          .position(o.position)
          .hide();

        // otherwise fallback to custom positioning
      } else {
        var pos = this.button.offset();

        this.menu.css({
          top: pos.top + this.button.outerHeight(),
          left: pos.left
        });
      }
    },

    // react to option changes after initialization
    _setOption: function(key, value) {
      var menu = this.menu;

      switch(key) {
        case 'header':
          menu.find('div.ui-multiselect-header')[value ? 'show' : 'hide']();
          break;
        case 'checkAllText':
          menu.find('a.ui-multiselect-all span').eq(-1).text(value);
          break;
        case 'uncheckAllText':
          menu.find('a.ui-multiselect-none span').eq(-1).text(value);
          break;
        case 'height':
          menu.find('ul').last().height(parseInt(value, 10));
          break;
        case 'minWidth':
          this.options[key] = parseInt(value, 10);
          this._setButtonWidth();
          this._setMenuWidth();
          break;
        case 'selectedText':
        case 'selectedList':
        case 'noneSelectedText':
          this.options[key] = value; // these all needs to update immediately for the update() call
          this.update();
          break;
        case 'classes':
          menu.add(this.button).removeClass(this.options.classes).addClass(value);
          break;
        case 'multiple':
          menu.toggleClass('ui-multiselect-single', !value);
          this.options.multiple = value;
          this.element[0].multiple = value;
          this.refresh();
          break;
        case 'position':
          this.position();
      }

      $.Widget.prototype._setOption.apply(this, arguments);
    }
  });

}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 458 */
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/attribute-option/create.js ***!
  \************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! backbone */ 6),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! pim/form-builder */ 16),
        __webpack_require__(/*! oro/messenger */ 12),
        __webpack_require__(/*! pim/template/attribute-option/validation-error */ 469)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        _,
        Backbone,
        Routing,
        FormBuilder,
        messenger,
        errorTemplate
    ) {
        var CreateOptionView = Backbone.View.extend({
            errorTemplate: _.template(errorTemplate),
            attribute: null,
            initialize: function (options) {
                this.attribute = options.attribute;
            },
            createOption: function () {
                var deferred = $.Deferred();

                FormBuilder.build('pim-attribute-option-form').done(function (form) {
                    var modal = new Backbone.BootstrapModal({
                        modalOptions: {
                            backdrop: 'static',
                            keyboard: false
                        },
                        allowCancel: true,
                        okCloses: false,
                        title: _.__('pim_enrich.form.attribute_option.add_option_modal.title'),
                        content: '',
                        cancelText: _.__('pim_enrich.form.attribute_option.add_option_modal.cancel'),
                        okText: _.__('pim_enrich.form.attribute_option.add_option_modal.confirm')
                    });
                    modal.open();

                    form.setElement(modal.$('.modal-body')).render();

                    modal.on('cancel', deferred.reject);
                    modal.on('ok', function () {
                        form.$('.validation-errors').remove();
                        $.ajax({
                            method: 'POST',
                            url: Routing.generate(
                                'pim_enrich_attributeoption_create',
                                { attributeId: this.attribute.id }
                            ),
                            data: JSON.stringify(form.getFormData())
                        }).done(function (option) {
                            modal.close();
                            messenger.notificationFlashMessage(
                                'success',
                                _.__('pim_enrich.form.attribute_option.flash.option_created')
                            );
                            deferred.resolve(option);
                        }).fail(function (xhr) {
                            var response = xhr.responseJSON;

                            if (response.code) {
                                form.$('input[name="code"]').after(
                                    this.errorTemplate({
                                        errors: [response.code]
                                    })
                                );
                            } else {
                                messenger.notificationFlashMessage(
                                    'error',
                                    _.__('pim_enrich.form.attribute_option.flash.error_creating_option')
                                );
                            }
                        }.bind(this));
                    }.bind(this));
                }.bind(this));

                return deferred.promise();
            }
        });

        return function (attribute) {
            if (!attribute) {
                throw new Error('Attribute must be provided to create a new option');
            }

            var view = new CreateOptionView({ attribute: attribute });

            return view.createOption().always(function () {
                view.remove();
            });
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 459 */
/* unknown exports provided */
/* all exports used */
/*!*****************************************************************************************!*\
  !*** ./src/Pim/Bundle/NavigationBundle/Resources/public/js/navigation/abstract-view.js ***!
  \*****************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backbone */ 6), __webpack_require__(/*! oro/navigation/dotmenu/view */ 495), __webpack_require__(/*! pim/router */ 13)], __WEBPACK_AMD_DEFINE_RESULT__ = function(_, Backbone, DotmenuView, router) {
    'use strict';

    /**
     * @export  oro/navigation/abstract-view
     * @class   oro.navigation.AbstractView
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        options: {
            tabTitle: 'Tabs',
            tabIcon: 'icon-folder-close',
            tabId: 'tabs',
            hideTabOnEmpty: false,
            collection: null
        },

        initialize: function() {
            this.dotMenu = new DotmenuView();
        },

        getCollection: function() {
            return this.options.collection;
        },

        registerTab: function() {
            this.dotMenu.addTab({
                key: this.options.tabId,
                title: this.options.tabTitle,
                icon: this.options.tabIcon,
                hideOnEmpty: this.options.hideTabOnEmpty
            });
        },

        /**
         * Search for pinbar items for current page.
         * @param  {Boolean} excludeGridParams
         * @param  {String}  url
         * @return {*}
         */
        getItemForCurrentPage: function(excludeGridParams) {
            return this.getItemForPage(this.getCurrentPageItemData().url, excludeGridParams);
        },

        /**
         * Search for pinbar items for url.
         * @param  {String}  url
         * @param  {Boolean} excludeGridParams
         * @return {*}
         */
        getItemForPage: function(url, excludeGridParams) {
            return this.options.collection.filter(_.bind(function (item) {
                var itemUrl = item.get('url');
                if (!_.isUndefined(excludeGridParams) && excludeGridParams) {
                    itemUrl = itemUrl.split('#g')[0];
                    url = url.split('#g')[0];
                }
                return itemUrl == url;
            }, this));
        },

        /**
         * Get object with info about current page
         * @return {Object}
         */
        getCurrentPageItemData: function() {
            return { url: Backbone.history.getFragment() };
        },

        /**
         * Get data for new navigation item based on element options
         *
         * @param el
         * @returns {Object}
         */
        getNewItemData: function(el) {
            itemData['title'] = document.title;
            return itemData;
        },

        cleanupTab: function() {
            this.dotMenu.cleanup(this.options.tabId);
            this.dotMenu.hideTab(this.options.tabId);
        },

        addItemToTab: function(item, prepend) {
            this.dotMenu.addTabItem(this.options.tabId, item, prepend);
        },

        checkTabContent: function() {
            this.dotMenu.checkTabContent(this.options.tabId);
        },

        render: function() {
            this.checkTabContent();
            return this;
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 460 */
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************************!*\
  !*** ./src/Pim/Bundle/NavigationBundle/Resources/public/js/navigation/collection.js ***!
  \**************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! backbone */ 6), __webpack_require__(/*! oro/navigation/model */ 452)], __WEBPACK_AMD_DEFINE_RESULT__ = function(Backbone, NavigationModel) {
    'use strict';

    /**
     * @export  oro/navigation/collection
     * @class   oro.navigation.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        model: NavigationModel
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 461 */
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************************!*\
  !*** ./src/Pim/Bundle/NavigationBundle/Resources/public/js/navigation/pinbar/model.js ***!
  \****************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! oro/navigation/model */ 452)], __WEBPACK_AMD_DEFINE_RESULT__ = function(NavigationModel) {
    'use strict';

    /**
     * @export  oro/navigation/pinbar/model
     * @class   oro.navigation.pinbar.Model
     * @extends oro.navigation.Model
     */
    return NavigationModel.extend({
        defaults: {
            title: '',
            url: null,
            position: null,
            type: 'pinbar',
            display_type: null,
            maximized: false,
            remove: false
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 462 */
/* unknown exports provided */
/* all exports used */
/*!***************************************************************************!*\
  !*** ./src/Pim/Bundle/UIBundle/Resources/public/lib/slimbox2/slimbox2.js ***!
  \***************************************************************************/
/***/ (function(module, exports) {

/*
	Slimbox v2.04 - The ultimate lightweight Lightbox clone for jQuery
	(c) 2007-2010 Christophe Beyls <http://www.digitalia.be>
	MIT-style license.
*/
(function (w) {
var E = w(window), u, f, F = -1, n, x, D, v, y, L, r, m = !window.XMLHttpRequest, s = [], l = document.documentElement, k = {}, t = new Image(), J = new Image(), H, a, g, p, I, d, G, c, A, K;w(function () {
    w("body").append(w([H = w('<div id="lbOverlay" />')[0], a = w('<div id="lbCenter" />')[0], G = w('<div id="lbBottomContainer" />')[0]]).css("display", "none"));g = w('<div id="lbImage" />').appendTo(a).append(p = w('<div style="position: relative;" />').append([I = w('<a id="lbPrevLink" href="#" />').click(B)[0], d = w('<a id="lbNextLink" href="#" />').click(e)[0]])[0])[0];c = w('<div id="lbBottom" />').appendTo(G).append([w('<a id="lbCloseLink" href="#" />').add(H).click(C)[0], A = w('<div id="lbCaption" />')[0], K = w('<div id="lbNumber" />')[0], w('<div style="clear: both;" />')[0]])[0]
});w.slimbox = function (O, N, M) {
    u = w.extend({loop: false, overlayOpacity: 0.8, overlayFadeDuration: 400, resizeDuration: 400, resizeEasing: "swing", initialWidth: 250, initialHeight: 250, imageFadeDuration: 400, captionAnimationDuration: 400, counterText: "Image {x} of {y}", closeKeys: [27, 88, 67], previousKeys: [37, 80], nextKeys: [39, 78]}, M);if (typeof O == "string") {
        O = [[O, N]];N = 0
    }y = E.scrollTop() + (E.height() / 2);L = u.initialWidth;r = u.initialHeight;w(a).css({top: Math.max(0, y - (r / 2)), width: L, height: r, marginLeft: -L / 2}).show();v = m || (H.currentStyle && (H.currentStyle.position != "fixed"));if (v) {
        H.style.position = "absolute"
    }w(H).css("opacity", u.overlayOpacity).fadeIn(u.overlayFadeDuration);z();j(1);f = O;u.loop = u.loop && (f.length > 1);return b(N)
};w.fn.slimbox = function (M, P, O) {
    P = P || function (Q) {
        return [Q.href, Q.title]
    };O = O || function () {
        return true
    };var N = this;return N.unbind("click").click(function () {
        var S = this, U = 0, T, Q = 0, R;T = w.grep(N, function (W, V) {
            return O.call(S, W, V)
        });for (R = T.length; Q < R; ++Q) {
            if (T[Q] == S) {
                U = Q
            }T[Q] = P(T[Q], Q)
        }return w.slimbox(T, U, M)
    })
};function z() {
    var N = E.scrollLeft(), M = E.width();w([a, G]).css("left", N + (M / 2));if (v) {
        w(H).css({left: N, top: E.scrollTop(), width: M, height: E.height()})
    }
}function j(M) {
    if (M) {
        w("object").add(m ? "select" : "embed").each(function (O, P) {
            s[O] = [P, P.style.visibility];P.style.visibility = "hidden"
        })
    }else {
        w.each(s, function (O, P) {
            P[0].style.visibility = P[1]
        });s = []
    }var N = M ? "bind" : "unbind";E[N]("scroll resize", z);w(document)[N]("keydown", o)
}function o(O) {
    var N = O.keyCode, M = w.inArray;return (M(N, u.closeKeys) >= 0) ? C() : (M(N, u.nextKeys) >= 0) ? e() : (M(N, u.previousKeys) >= 0) ? B() : false
}function B() {
    return b(x)
}function e() {
    return b(D)
}function b(M) {
    if (M >= 0) {
        F = M;n = f[F][0];x = (F || (u.loop ? f.length : 0)) - 1;D = ((F + 1) % f.length) || (u.loop ? 0 : -1);q();a.className = "lbLoading";k = new Image();k.onload = i;k.src = n
    }return false
}function i() {
    a.className = "";w(g).css({backgroundImage: "url(" + n + ")", visibility: "hidden", display: ""});w(p).width(k.width);w([p, I, d]).height(k.height);w(A).html(f[F][1] || "");w(K).html((((f.length > 1) && u.counterText) || "").replace(/{x}/, F + 1).replace(/{y}/, f.length));if (x >= 0) {
        t.src = f[x][0]
    }if (D >= 0) {
        J.src = f[D][0]
    }L = g.offsetWidth;r = g.offsetHeight;var M = Math.max(0, y - (r / 2));if (a.offsetHeight != r) {
        w(a).animate({height: r, top: M}, u.resizeDuration, u.resizeEasing)
    }if (a.offsetWidth != L) {
        w(a).animate({width: L, marginLeft: -L / 2}, u.resizeDuration, u.resizeEasing)
    }w(a).queue(function () {
        w(G).css({width: L, top: M + r, marginLeft: -L / 2, visibility: "hidden", display: ""});w(g).css({display: "none", visibility: "", opacity: ""}).fadeIn(u.imageFadeDuration, h)
    })
}function h() {
    if (x >= 0) {
        w(I).show()
    }if (D >= 0) {
        w(d).show()
    }w(c).css("marginTop", -c.offsetHeight).animate({marginTop: 0}, u.captionAnimationDuration);G.style.visibility = ""
}function q() {
    k.onload = null;k.src = t.src = J.src = n;w([a, g, c]).stop(true);w([I, d, g, G]).hide()
}function C() {
    if (F >= 0) {
        q();F = x = D = -1;w(a).hide();w(H).stop().fadeOut(u.overlayFadeDuration, j)
    }return false
}
})(jQuery);


/***/ }),
/* 463 */
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/templates/configure-columns-action.html ***!
  \************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"AknColumnConfigurator-column\">\n    <div class=\"AknColumnConfigurator-columnHeader\"></div>\n    <div class=\"AknColumnConfigurator-listContainer\">\n        <ul class=\"AknVerticalList nav-list\">\n            <li class=\"AknVerticalList-item AknVerticalList-item--selectable tab active\">\n                <%- _.__(\"pim_datagrid.column_configurator.all_groups\") %>\n                <span class=\"badge badge-transparent pull-right\"><%- columns.length %></span>\n            </li>\n            <% _.each(groups, function(group) { %>\n            <li class=\"AknVerticalList-item AknVerticalList-item--selectable tab\" data-value=\"<%- group.name %>\">\n                <%- group.name %>\n                <span class=\"AknBadge\"><%- group.itemCount %></span>\n            </li>\n            <% }); %>\n        </ul>\n    </div>\n</div>\n\n<div class=\"AknColumnConfigurator-column\">\n    <div class=\"AknColumnConfigurator-columnHeader\">\n        <i class=\"AknColumnConfigurator-searchIcon icon-search\"></i>\n        <input class=\"AknTextField AknColumnConfigurator-searchInput\" type=\"search\" placeholder=\"<%- _.__('pim_datagrid.column_configurator.search') %>\"/>\n    </div>\n    <div class=\"AknColumnConfigurator-listContainer\">\n        <ul id=\"column-list\" class=\"AknVerticalList connected-sortable\">\n            <% _.each(_.where(columns, {displayed: false}), function(column) { %>\n            <li class=\"AknVerticalList-item AknVerticalList-item--movable\" data-value=\"<%- column.code %>\" data-group=\"<%- column.group %>\">\n                <div>\n                    <i class=\"icon-th\"></i>\n                    <%- column.label %>\n                </div>\n                <a href=\"javascript:void(0);\" class=\"AknIconButton AknIconButton--small AknIconButton--grey action\" title=\"<%- _.__('pim_datagrid.column_configurator.remove_column') %>\">\n                    <i class=\"icon-trash\"></i>\n                </a>\n            </li>\n            <% }); %>\n        </ul>\n    </div>\n</div>\n\n<div class=\"AknColumnConfigurator-column\">\n    <div class=\"AknColumnConfigurator-columnHeader\">\n        <%- _.__(\"pim_datagrid.column_configurator.displayed_columns\") %>\n        <button class=\"AknButton AknButton--grey reset\">\n            <%- _.__(\"pim_datagrid.column_configurator.clear\") %>\n        </button>\n    </div>\n    <div class=\"AknColumnConfigurator-listContainer\">\n        <ul id=\"column-selection\" class=\"AknVerticalList connected-sortable\">\n            <% _.each(_.sortBy(_.where(columns, {displayed: true}), 'position'), function(column) { %>\n            <li class=\"AknVerticalList-item AknVerticalList-item--movable\" data-value=\"<%- column.code %>\" data-group=\"<%- column.group %>\">\n                <div>\n                    <i class=\"icon-th\"></i>\n                    <%- column.label %>\n                </div>\n                <a href=\"javascript:void(0);\" class=\"AknIconButton AknIconButton--small AknIconButton--grey action\" title=\"<%- _.__('pim_datagrid.column_configurator.remove_column') %>\">\n                    <% if (column.removable) { %><i class=\"icon-trash\"></i><% } %>\n                </a>\n            </li>\n            <% }); %>\n            <div class=\"AknMessageBox AknMessageBox--error AknMessageBox--hide alert alert-error\"><%- _.__(\"datagrid_view.columns.min_message\") %></div>\n        </ul>\n    </div>\n</div>\n"

/***/ }),
/* 464 */
/* unknown exports provided */
/* all exports used */
/*!******************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/templates/filter/date-filter.html ***!
  \******************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"AknFilterDate\">\n    <select class=\"AknFilterDate-select type\" name=\"<%= name %>\">\n        <% _.each(choices, function (option) { %>\n        <option value=\"<%= option.value %>\"<% if (option.value == selectedChoice) { %> selected=\"selected\"<% } %>><%= option.label %></option>\n        <% }); %>\n    </select>\n    <div class=\"AknFilterDate-dates\">\n        <span class=\"AknFilterDate-start\">\n            <input type=\"text\" value=\"\" class=\"<%= inputClass %> add-on\" name=\"start\" placeholder=\"<%- _.__('from') %>\" size=\"1\">\n        </span>\n        <span class=\"AknFilterDate-separator\">-</span>\n        <span class=\"AknFilterDate-end\">\n            <input type=\"text\" value=\"\" class=\"<%= inputClass %> add-on\" name=\"end\" placeholder=\"<%- _.__('to') %>\" size=\"1\">\n        </span>\n    </div>\n    <div class=\"AknButtonList AknButtonList--right\">\n        <button class=\"AknButtonList-item AknButton AknButton--apply filter-update\" type=\"button\"><%- _.__('Update') %></button>\n    </div>\n</div>\n"

/***/ }),
/* 465 */
/* unknown exports provided */
/* all exports used */
/*!********************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/templates/filter/metric-filter.html ***!
  \********************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"AknFilterChoice metricfilter choicefilter\">\n    <div class=\"AknFilterChoice-operator AknDropdown btn-group\">\n        <button class=\"AknActionButton AknActionButton--big AknActionButton--noRightBorder dropdown-toggle\" data-toggle=\"dropdown\">\n            <%= _.__('Action') %>\n            <span class=\"AknCaret caret\"></span>\n        </button>\n        <ul class=\"dropdown-menu\">\n            <% _.each(choices, function (choice) { %>\n            <li><a class=\"choice_value\" href=\"#\" data-value=\"<%= choice.value %>\"><%= choice.label %></a></li>\n            <% }); %>\n            </ul>\n        <input class=\"name_input\" type=\"hidden\" name=\"metric_type\" value=\"\"/>\n        </div>\n\n    <input class=\"AknTextField AknTextField--noRadius AknFilterChoice-field\" type=\"text\" name=\"value\" value=\"\">\n\n    <div class=\"AknFilterChoice-operator AknDropdown\">\n        <button class=\"AknActionButton AknActionButton--big AknActionButton--noRightBorder AknActionButton--noLeftBorder dropdown-toggle\" data-toggle=\"dropdown\">\n            <%= _.__('Unit') %>\n            <span class=\"AknCaret caret\"></span>\n        </button>\n        <ul class=\"dropdown-menu\">\n            <% _.each(units, function (symbol, code) { %>\n                <li><a class=\"choice_value\" href=\"#\" data-value=\"<%= code %>\"><%= _.__(code) %></a></li>\n            <% }); %>\n            </ul>\n        <input class=\"name_input\" type=\"hidden\" name=\"metric_unit\" value=\"\"/>\n        </div>\n    <button class=\"AknButton AknButton--apply AknFilterChoice-button AknButton--noLeftRadius filter-update\" type=\"button\"><%= _.__(\"Update\") %></button>\n</div>\n"

/***/ }),
/* 466 */
/* unknown exports provided */
/* all exports used */
/*!*******************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/attribute-option/edit.html ***!
  \*******************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<td class=\"AknGrid-bodyCell field-cell\">\n    <div class=\"AknFieldContainer AknFieldContainer--withoutMargin\">\n        <div class=\"AknFieldContainer-inputContainer\">\n            <% if (item.id) { %>\n                <input type=\"hidden\" class=\"attribute_option_code\" value=\"<%- item.code %>\"/>\n                <span class=\"option-code\"><%- item.code %></span>\n            <% } else { %>\n                <input type=\"text\" class=\"AknTextField attribute_option_code exclude\" value=\"<%- item.code %>\"/>\n                <div class=\"AknFieldContainer-iconsContainer\">\n                    <i class=\"AknIconButton AknIconButton--important AknIconButton--hide icon-warning-sign validation-tooltip\" data-placement=\"top\" data-toggle=\"tooltip\"></i>\n                </div>\n            <% } %>\n        </div>\n    </div>\n</td>\n<% _.each(locales, function (locale) { %>\n    <td class=\"AknGrid-bodyCell field-cell\">\n        <% if (item.optionValues[locale]) { %>\n            <input type=\"text\" class=\"AknTextField attribute-option-value exclude\" data-locale=\"<%- locale %>\"\n                value=\"<%- item.optionValues[locale].value %>\"/>\n        <% } else { %>\n            <input type=\"text\" class=\"AknTextField attribute-option-value exclude\" data-locale=\"<%- locale %>\"\n        value=\"\"/>\n        <% } %>\n    </td>\n<% }); %>\n<td class=\"AknGrid-bodyCell\">\n    <div class=\"AknButtonList AknButtonList--right\">\n        <span class=\"AknButtonList-item AknIconButton AknIconButton--small AknIconButton--apply update-row\"><i class=\"icon-ok\"></i></span>\n        <span class=\"AknButtonList-item AknIconButton AknIconButton--small AknIconButton--important show-row\"><i class=\"icon-remove\"></i></span>\n    </div>\n</td>\n"

/***/ }),
/* 467 */
/* unknown exports provided */
/* all exports used */
/*!********************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/attribute-option/index.html ***!
  \********************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<colgroup>\n    <col class=\"code\" span=\"1\">\n    <col class=\"fields\" span=\"<%- locales.length %>\"/>\n    <col class=\"action\" span=\"1\"/>\n</colgroup>\n<thead>\n    <tr>\n        <th class=\"AknGrid-headerCell\"><%- code_label %></th>\n        <% _.each(locales, function (locale) { %>\n            <th class=\"AknGrid-headerCell\">\n                <%- locale %>\n            </th>\n        <% }); %>\n        <th class=\"AknGrid-headerCell AknGrid-headerCell--right\"><%- _.__('pim_enrich.entity.attribute_option.actions') %></th>\n    </tr>\n</thead>\n<tbody></tbody>\n<tfoot>\n    <tr class=\"AknGrid-bodyRow\">\n        <td class=\"AknGrid-bodyCell\" colspan=\"<%- 2 + locales.length %>\">\n            <span class=\"AknButton AknButton--grey AknButton--small option-add\"><%- add_option_label %></span>\n        </td>\n    </tr>\n</tfoot>\n"

/***/ }),
/* 468 */
/* unknown exports provided */
/* all exports used */
/*!*******************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/attribute-option/show.html ***!
  \*******************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<td class=\"AknGrid-bodyCell\">\n    <span class=\"handle\"><i class=\"icon-reorder\"></i></span>\n    <span class=\"option-code\"><%- item.code %></span>\n</td>\n<% _.each(locales, function (locale) { %>\n    <td class=\"AknGrid-bodyCell\">\n        <% if (item.optionValues[locale]) { %>\n            <span title=\"<%- item.optionValues[locale].value %>\">\n                <%- item.optionValues[locale].value %>\n            </span>\n        <% } %>\n    </td>\n<% }); %>\n<td class=\"AknGrid-bodyCell\">\n    <div class=\"AknButtonList AknButtonList--right\">\n        <span class=\"AknButtonList-item AknIconButton AknIconButton--small AknIconButton--apply edit-row\"><i class=\"icon-pencil\"></i></span>\n        <span class=\"AknButtonList-item AknIconButton AknIconButton--small AknIconButton--important delete-row\"><i class=\"icon-trash\"></i></span>\n    </div>\n</td>\n"

/***/ }),
/* 469 */
/* unknown exports provided */
/* all exports used */
/*!*******************************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/attribute-option/validation-error.html ***!
  \*******************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"AknFieldContainer-validationErrors validation-errors\">\n    <% _.each(errors, function(error) { %>\n        <span class=\"AknFieldContainer-validationError\">\n            <i class=\"icon-warning-sign\"></i>\n            <span class=\"error-message\"><%- error %></span>\n        </span>\n    <% }) %>\n</div>\n"

/***/ }),
/* 470 */
/* unknown exports provided */
/* all exports used */
/*!***********************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/index/confirm-button.html ***!
  \***********************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<a class=\"AknButton AknButton--withIcon <%- buttonClass %>\" title=\"<%- buttonLabel %>\" data-title=\" <%- title %>\"\n   data-dialog=\"confirm\" data-method=\"POST\"\n   data-message=\"<%- message %>\" data-url=\"<%- url %>\" data-redirect-url=\"<%- redirectUrl %>\"\n   data-error-message=\"<%- errorMessage %>\" data-success-message=\"<%- successMessage %>\">\n    <i class=\"AknButton-icon icon-<%- iconName %>\"></i>\n    <%- buttonLabel %>\n</a>\n"

/***/ }),
/* 471 */
/* unknown exports provided */
/* all exports used */
/*!*******************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/product/field/boolean.html ***!
  \*******************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"switch switch-small\" data-on-label=\"<%- _.__('switch_on') %>\" data-off-label=\"<%- _.__('switch_off') %>\">\n    <input id=\"<%- fieldId %>\" type=\"checkbox\" data-locale=\"<%- value.locale %>\" data-scope=\"<%- value.scope %>\" <%- value.data ? 'checked' : '' %> <%- editMode === 'view' ? 'disabled' : '' %>>\n</div>\n"

/***/ }),
/* 472 */
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/product/field/date.html ***!
  \****************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"datetimepicker\">\n    <input id=\"<%- fieldId %>\" class=\"AknTextField datepicker-field add-on\" type=\"text\" data-locale=\"<%- value.locale %>\" data-scope=\"<%- value.scope %>\" value=\"<%- value.data %>\" <%- editMode === 'view' ? 'disabled' : '' %>/>\n</div>\n"

/***/ }),
/* 473 */
/* unknown exports provided */
/* all exports used */
/*!*****************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/product/field/media.html ***!
  \*****************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"AknMediaField <%- value.data && value.data.filePath ? 'has-file' : '' %>\" >\n    <% if (!value.data || value.data.filePath === null) { %>\n        <input class=\"AknMediaField-fileUploaderInput\" id=\"<%- fieldId %>\" type=\"file\" data-locale=\"<%- value.locale %>\" data-scope=\"<%- value.scope %>\" <%- editMode === 'view' ? 'disabled' : '' %>/>\n        <div class=\"AknMediaField-emptyContainer\">\n            <img src=\"/bundles/pimui/images/upload.png\" alt=\"upload icon\" class=\"AknMediaField-uploadIcon\"/>\n            <span><%- _.__('pim_enrich.entity.product.media.upload')%></span>\n        </div>\n    <% } else { %>\n        <div class=\"AknMediaField-preview preview\">\n            <% mediaThumbnailUrl = mediaUrlGenerator.getMediaShowUrl(value.data.filePath, 'thumbnail_small') %>\n            <% mediaPreviewUrl   = mediaUrlGenerator.getMediaShowUrl(value.data.filePath, 'preview') %>\n            <% mediaDownloadUrl  = mediaUrlGenerator.getMediaDownloadUrl(value.data.filePath) %>\n            <% if ('pim_catalog_image' === attribute.type && null != mediaThumbnailUrl) { %>\n                <div class=\"AknMediaField-thumb file\"><img src=\"<%- mediaThumbnailUrl %>\" class=\"AknMediaField-image\"/></div>\n            <% } else { %>\n                <div class=\"AknMediaField-thumb file\"><i class=\"AknMediaField-icon icon icon-file\"></i></div>\n            <% } %>\n            <div class=\"AknMediaField-info info\">\n                <div class=\"filename\" title=\"<%- value.data.originalFilename %>\"><%- value.data.originalFilename %></div>\n                <div class=\"AknButtonList AknButtonList--centered actions\">\n                    <% if ('pim_catalog_image' === attribute.type && null != mediaPreviewUrl) { %>\n                        <span class=\"AknButtonList-item AknIconButton AknIconButton--grey open-media\"><i class=\"icon icon-eye-open\"></i></span>\n                    <% } %>\n                    <a href=\"<%- mediaDownloadUrl %>\" class=\"AknButtonList-item AknIconButton AknIconButton--grey download-file\" download><i class=\"icon icon-cloud-download\"></i></a>\n                    <span class=\"AknButtonList-item AknIconButton AknIconButton--grey clear-field <%- editMode === 'view' ? 'AknIconButton--hide' : '' %>\"><i class=\"icon icon-trash\"></i></span>\n                </div>\n            </div>\n        </div>\n    <% } %>\n    <div class=\"AknMediaField-progress AknProgress AknProgress--micro progress\">\n        <div class=\"AknProgress-bar bar\"></div>\n    </div>\n</div>\n"

/***/ }),
/* 474 */
/* unknown exports provided */
/* all exports used */
/*!******************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/product/field/metric.html ***!
  \******************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"AknMetricField metric-container\">\n    <input class=\"AknTextField AknTextField--noRightRadius data\" id=\"<%- fieldId %>\" type=\"text\" data-locale=\"<%- value.locale %>\" data-scope=\"<%- value.scope %>\" value=\"<%- value.data.amount %>\" <%- editMode === 'view' ? 'disabled' : '' %>/>\n    <select class=\"AknMetricField-unit unit select-field\" data-locale=\"<%- value.locale %>\" data-scope=\"<%- value.scope %>\" <%- editMode === 'view' ? 'disabled' : '' %>>\n        <% _.each(_.keys(measures[attribute.metric_family].units), function(unit) { %>\n            <option value=\"<%- unit %>\"<% if (value.data.unit === unit) { %> selected<% } %>><%- _.__(unit) %></option>\n        <% }); %>\n    </select>\n</div>\n"

/***/ }),
/* 475 */
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/product/field/multi-select.html ***!
  \************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<input id=\"<%- fieldId %>\" type=\"hidden\" class=\"select-field\" value=\"<%- value.data.join(',') %>\" data-locale=\"<%- value.locale %>\" data-scope=\"<%- value.scope %>\" <%- editMode === 'view' ? 'disabled' : '' %>/>\n<% if (userCanAddOption) { %>\n    <div class=\"AknFieldContainer-iconsContainer\">\n        <span class=\"AknIconButton AknIconButton--dark add-attribute-option\" data-toggle=\"tooltip\" data-placement=\"top\" data-original-title=\"<%- _.__('label.attribute_option.add_option') %>\">\n            <i class=\"icon-plus\"></i>\n        </span>\n    </div>\n<% } %>\n"

/***/ }),
/* 476 */
/* unknown exports provided */
/* all exports used */
/*!******************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/product/field/number.html ***!
  \******************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<input id=\"<%- fieldId %>\" class=\"AknTextField\" type=\"text\" data-locale=\"<%- value.locale %>\" data-scope=\"<%- value.scope %>\" value=\"<%- value.data %>\" <%- editMode === 'view' ? 'disabled' : '' %>/>\n"

/***/ }),
/* 477 */
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/product/field/price-collection.html ***!
  \****************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"AknPriceList\">\n    <% if (!value.data) { %>\n        <% _.each(currencies, function (currency) { %>\n            <div class=\"AknPriceList-item price-input\">\n                <input class=\"AknTextField AknTextField--noRightRadius\" type=\"text\" data-locale=\"<%- value.locale %>\" data-scope=\"<%- value.scope %>\" data-currency=\"<%- currency.code %>\" value=\"\" <%- editMode === 'view' ? 'disabled' : '' %> size=\"1\">\n                <span class=\"AknPriceList-currency\"><%- currency.code %></span>\n            </div>\n        <% }) %>\n    <% } else { %>\n        <% _.each(value.data, function (price) { %>\n            <div class=\"AknPriceList-item price-input\">\n                <input class=\"AknTextField AknTextField--noRightRadius\" type=\"text\" data-locale=\"<%- value.locale %>\" data-scope=\"<%- value.scope %>\" data-currency=\"<%- price.currency %>\" value=\"<%- price.amount %>\" <%- editMode === 'view' ? 'disabled' : '' %> size=\"1\">\n                <span class=\"AknPriceList-currency\"><%- price.currency %></span>\n            </div>\n        <% }) %>\n    <% } %>\n</div>\n"

/***/ }),
/* 478 */
/* unknown exports provided */
/* all exports used */
/*!*************************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/product/field/simple-select.html ***!
  \*************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<input id=\"<%- fieldId %>\" type=\"hidden\" class=\"select-field\" value=\"<%- value.data %>\" data-min-input-length=\"0\" data-locale=\"<%- value.locale %>\" data-scope=\"<%- value.scope %>\" <%- editMode === 'view' ? 'disabled' : '' %>/>\n<% if (userCanAddOption) { %>\n    <div class=\"AknFieldContainer-iconsContainer\">\n        <span class=\"AknIconButton AknIconButton--dark add-attribute-option\" data-toggle=\"tooltip\" data-placement=\"top\" data-original-title=\"<%- _.__('label.attribute_option.add_option') %>\">\n            <i class=\"icon-plus\"></i>\n        </span>\n    </div>\n<% } %>\n"

/***/ }),
/* 479 */
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/product/field/text.html ***!
  \****************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<input id=\"<%- fieldId %>\" class=\"AknTextField <%- context.isRequired ? 'AknTextField--required' : '' %>\" type=\"text\" data-locale=\"<%- value.locale %>\" data-scope=\"<%- value.scope %>\" value=\"<%- value.data %>\" <%- editMode === 'view' ? 'disabled' : '' %>/>\n"

/***/ }),
/* 480 */
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************************************!*\
  !*** ./src/Pim/Bundle/NotificationBundle/Resources/public/templates/notification/notification-footer.html ***!
  \************************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<p class=\"AknNotificationList-footer\">\n    <% if (loading) { %>\n        <img src=\"<%= options.imgUrl %>\" alt=\"<%= options.loadingText %>\"/>\n    <% } %>\n\n    <% if (!loading && !hasNotifications && !hasMore) { %>\n        <span><%= options.noNotificationsMessage %></span>\n    <% } %>\n\n    <% if (hasNotifications && hasUnread) { %>\n        <button class=\"AknButton AknButton--grey mark-as-read\"><%= options.markAsReadMessage %></button>\n    <% } %>\n</p>\n"

/***/ }),
/* 481 */
/* unknown exports provided */
/* all exports used */
/*!**********************************************************************************************************!*\
  !*** ./src/Pim/Bundle/NotificationBundle/Resources/public/templates/notification/notification-list.html ***!
  \**********************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<a href=\"<%= url ? '#' + url : 'javascript: void(0);' %>\" class=\"AknNotification-link <%= viewed ? '' : 'AknNotification-link--new new' %>\">\n    <div class=\"AknNotification-header\">\n        <div class=\"AknNotification-icon AknNotification-icon--<%= actionType %>\">\n            <i class=\"AknNotification-status AknNotification-status--<%= type %> icon-<%= icon %>\"></i>\n        </div>\n        <div class=\"AknNotification-metas\">\n            <time class=\"AknNotification-time\"><%= createdAt %></time>\n            <span class=\"AknNotification-title\"><%= actionTypeMessage %></span>\n        </div>\n        <i class=\"AknIconButton AknIconButton--light icon-<%= viewed ? 'trash' : 'eye-close' %> action\"></i>\n    </div>\n    <div class=\"AknNotification-message\"><%= message %></div>\n    <% if (comment) { %> <div class=\"AknNotification-comment\"><%= comment %></div> <% } %>\n    <% if (showReportButton) { %>\n    <button class=\"AknNotification-button AknButton AknButton--micro AknButton--grey AknButton--withIcon\"><i class=\"AknButton-icon icon-file-text-alt\"></i><%- _.__(buttonLabel) %></button>\n    <% } %>\n</a>\n"

/***/ }),
/* 482 */
/* unknown exports provided */
/* all exports used */
/*!*****************************************************************************************************!*\
  !*** ./src/Pim/Bundle/NotificationBundle/Resources/public/templates/notification/notification.html ***!
  \*****************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<a href=\"javascript:void(0);\" class=\"AknBell-link dropdown-toggle\" data-toggle=\"dropdown\">\n    <i class=\"AknBell-icon icon-bell\"></i>\n    <span class=\"AknBell-countContainer\"></span>\n</a>\n\n<ul class=\"AknNotificationList AknDropdown-menu AknDropdown-menu--right\"></ul>\n"

/***/ }),
/* 483 */
/* unknown exports provided */
/* all exports used */
/*!*************************************************************************************************!*\
  !*** ./src/Pim/Bundle/UIBundle/Resources/public/js/templates/system/group/loading-message.html ***!
  \*************************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"AknFormContainer AknFormContainer--withPadding system-loading-message-field\">\n    <div class=\"AknFieldContainer\">\n        <div class=\"AknFieldContainer-header\">\n            <label class=\"AknFieldContainer-label\" for=\"loading_message_enabled\"><%- _.__('oro_config.form.config.group.loading_message.label') %></label>\n        </div>\n        <div class=\"AknFieldContainer-inputContainer system-loading-message-enabled-field\">\n            <div class=\"switch switch-small\" data-on-label=\"<%- _.__('pim_enrich.form.entity.switch.yes') %>\" data-off-label=\"<%- _.__('pim_enrich.form.entity.switch.no') %>\">\n                <input type=\"checkbox\" id=\"loading_message_enabled\" <%- loading_message_enabled === '1' ? 'checked' : '' %> />\n            </div>\n        </div>\n    </div>\n\n    <div class=\"AknFieldContainer\">\n        <div class=\"AknFieldContainer-header\">\n            <label class=\"AknFieldContainer-label\" for=\"loading_messages\"><%- _.__('oro_config.form.config.group.loading_messages.label') %></label>\n        </div>\n        <div class=\"AknFieldContainer-inputContainer system-loading-messages-field\">\n            <textarea class=\"AknTextareaField\" id=\"loading_messages\"><%- loading_messages %></textarea>\n        </div>\n    </div>\n</div>\n"

/***/ }),
/* 484 */
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter/collection-filters-manager.js ***!
  \****************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/datafilter/filters-manager */ 487)], __WEBPACK_AMD_DEFINE_RESULT__ = function(_, FiltersManager) {
    'use strict';

    /**
     * View that represents all grid filters
     *
     * @export  oro/datafilter/collection-filters-manager
     * @class   oro.datafilter.CollectionFiltersManager
     * @extends oro.datafilter.FiltersManager
     */
    return FiltersManager.extend({
        /**
         * Initialize filter list options
         *
         * @param {Object} options
         * @param {oro.PageableCollection} [options.collection]
         * @param {Object} [options.filters]
         * @param {String} [options.addButtonHint]
         */
        initialize: function(options)
        {
            this.collection = options.collection;

            this.collection.on('beforeFetch', this._beforeCollectionFetch, this);
            this.collection.on('updateState', this._onUpdateCollectionState, this);
            this.collection.on('reset', this._onCollectionReset, this);

            FiltersManager.prototype.initialize.apply(this, arguments);
        },

        /**
         * Triggers when filter is updated
         *
         * @param {oro.filter.AbstractFilter} filter
         * @protected
         */
        _onFilterUpdated: function(filter) {
            if (this.ignoreFiltersUpdateEvents) {
                return;
            }
            this.collection.state.currentPage = 1;
            this.collection.fetch();

            FiltersManager.prototype._onFilterUpdated.apply(this, arguments);
        },

        /**
         * Triggers before collection fetch it's data
         *
         * @protected
         */
        _beforeCollectionFetch: function(collection) {
            collection.state.filters = this._createState();
        },

        /**
         * Triggers when collection state is updated
         *
         * @param {oro.PageableCollection} collection
         */
        _onUpdateCollectionState: function(collection) {
            this.ignoreFiltersUpdateEvents = true;
            this._applyState(collection.state.filters || {});
            this.ignoreFiltersUpdateEvents = false;
        },

        /**
         * Triggers after collection resets it's data
         *
         * @protected
         */
        _onCollectionReset: function(collection) {
            if (collection.state.totalRecords > 0 && this.$el.children().length > 0) {
                this.$el.show();
            }
        },

        /**
         * Create state according to filters parameters
         *
         * @return {Object}
         * @protected
         */
        _createState: function() {
            var state = {};
            _.each(this.filters, function(filter, name) {
                var shortName = '__' + name;
                if (filter.enabled) {
                    if (!filter.isEmpty()) {
                        state[name] = filter.getValue();
                    } else if (!filter.defaultEnabled) {
                        state[shortName] = 1;
                    }
                } else if (filter.defaultEnabled) {
                    state[shortName] = 0;
                }
            }, this);

            this.trigger('collection-filters:createState.post', state);

            return state;
        },

        /**
         * Apply filter values from state
         *
         * @param {Object} state
         * @protected
         * @return {*}
         */
        _applyState: function(state) {
            var toEnable  = [],
                toDisable = [];

            _.each(this.filters, function(filter, name) {
                var shortName = '__' + name,
                    filterState;
                if (_.has(state, name) && 0 !== _.size(state)) {
                    filterState = state[name];
                    if (!_.isObject(filterState)) {
                        filterState = {
                            value: filterState
                        };
                    }
                    filter.setValue(filterState);
                    toEnable.push(filter);
                } else if (_.has(state, shortName)) {
                    filter.reset();
                    if (Number(state[shortName])) {
                        toEnable.push(filter);
                    } else {
                        toDisable.push(filter);
                    }
                } else {
                    filter.reset();
                    if (filter.defaultEnabled) {
                        toEnable.push(filter);
                    } else {
                        toDisable.push(filter);
                    }
                }
            }, this);

            this.enableFilters(toEnable);
            this.disableFilters(toDisable);

            return this;
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 485 */
/* unknown exports provided */
/* all exports used */
/*!********************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter/filter/date-filter.js ***!
  \********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/translator */ 2),
        __webpack_require__(/*! oro/datafilter/choice-filter */ 89),
        __webpack_require__(/*! datepicker */ 44),
        __webpack_require__(/*! pim/date-context */ 42),
        __webpack_require__(/*! pim/formatter/date */ 60),
        __webpack_require__(/*! pim/template/datagrid/filter/date-filter */ 464)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function(
    $,
    _,
    __,
    ChoiceFilter,
    Datepicker,
    DateContext,
    DateFormatter,
    template
) {
    'use strict';

    /**
     * Date filter: filter type as option + interval begin and end dates
     *
     * @export  oro/datafilter/date-filter
     * @class   oro.datafilter.DateFilter
     * @extends oro.datafilter.ChoiceFilter
     */
    return ChoiceFilter.extend({
        /**
         * Template for filter criteria
         *
         * @property {function(Object, ?Object=): String}
         */
        popupCriteriaTemplate: _.template(template),

        /**
         * Selectors for filter data
         *
         * @property
         */
        criteriaValueSelectors: {
            type: '.AknFilterDate-select',
            value: {
                start: '.AknFilterDate-start',
                end: '.AknFilterDate-end'
            }
        },

        /**
         * CSS class for visual date input elements
         *
         * @property
         */
        inputClass: 'AknTextField',

        /**
         * Date widget options
         *
         * @property
         */
        datetimepickerOptions: {
            format: DateContext.get('date').format,
            defaultFormat: DateContext.get('date').defaultFormat,
            language: DateContext.get('language'),
        },

        /**
         * References to date widgets
         *
         * @property
         */
        dateWidgets: {
            start: null,
            end: null
        },

        /**
         * Date filter type values
         *
         * @property
         */
        typeValues: {
            between:    1,
            notBetween: 2,
            moreThan:   3,
            lessThan:   4
        },

        /**
         * Date widget selector
         *
         * @property
         */
        dateWidgetSelector: 'datepicker',

        /**
         * @inheritDoc
         */
        initialize: function () {
            // init empty value object if it was not initialized so far
            if (_.isUndefined(this.emptyValue)) {
                this.emptyValue = {
                    type: (_.isEmpty(this.choices) ? '' : _.first(this.choices).value),
                    value: {
                        start: '',
                        end: ''
                    }
                };
            }

            ChoiceFilter.prototype.initialize.apply(this, arguments);
        },

        /**
         * @param {Event} e
         */
        changeFilterType: function (e) {
            var select = this.$el.find(e.currentTarget);
            var selectedValue = select.val();

            this._displayFilterType(selectedValue);
        },

        /**
         * Manage how to display a filter type
         *
         * @param {String} type
         * @protected
         */
        _displayFilterType: function(type) {
            this.$el.find('.AknFilterDate-separator').show();
            this.$el.find(this.criteriaValueSelectors.value.end).show();
            this.$el.find(this.criteriaValueSelectors.value.start).show();
            if (this.typeValues.moreThan == parseInt(type)) {
                this.$el.find('.AknFilterDate-separator').hide();
                this.$el.find(this.criteriaValueSelectors.value.end).hide();
            } else if (this.typeValues.lessThan == parseInt(type)) {
                this.$el.find('.AknFilterDate-separator').hide();
                this.$el.find(this.criteriaValueSelectors.value.start).hide();
            } else if (_.contains(['empty', 'not empty'], type)) {
                this.$el.find('.AknFilterDate-separator').hide();
                this.$el.find(this.criteriaValueSelectors.value.end).hide();
                this.$el.find(this.criteriaValueSelectors.value.start).hide();
            }
        },

        /**
         * @inheritDoc
         */
        _renderCriteria: function(el) {
            $(el).append(
                this.popupCriteriaTemplate({
                    name: this.name,
                    choices: this.choices,
                    selectedChoice: this.emptyValue.type,
                    inputClass: this.inputClass
                })
            );

            $(el).find(this.criteriaValueSelectors.type).bind('change', _.bind(this.changeFilterType, this));

            _.each(this.criteriaValueSelectors.value, function(selector, name) {
                this.dateWidgets[name] = Datepicker.init(this.$(selector), this.datetimepickerOptions);
            }, this);

            return this;
        },

        /**
         * @inheritDoc
         */
        _getCriteriaHint: function() {
            var hint = '',
                option, start, end, type,
                value = (arguments.length > 0) ? this._getDisplayValue(arguments[0]) : this._getDisplayValue();

            if (_.contains(['empty', 'not empty'], value.type)) {
                return this._getChoiceOption(value.type).label;
            }

            if (value.value) {
                start = value.value.start;
                end   = value.value.end;
                type  = value.type ? value.type.toString() : '';

                switch (type) {
                    case this.typeValues.moreThan.toString():
                        hint += [__('more than'), start].join(' ');
                        break;
                    case this.typeValues.lessThan.toString():
                        hint += [__('less than'), end].join(' ');
                        break;
                    case this.typeValues.notBetween.toString():
                        if (start && end) {
                            option = this._getChoiceOption(this.typeValues.notBetween);
                            hint += [option.label, start, __('and'), end].join(' ');
                        } else if (start) {
                            hint += [__('before'), start].join(' ');
                        } else if (end) {
                            hint += [__('after'), end].join(' ');
                        }
                        break;
                    case this.typeValues.between.toString():
                    default:
                        if (start && end) {
                            option = this._getChoiceOption(this.typeValues.between);
                            hint += [option.label, start, __('and'), end].join(' ');
                        } else if (start) {
                            hint += [__('from'), start].join(' ');
                        } else if (end) {
                            hint += [__('to'), end].join(' ');
                        }
                        break;
                }
                if (hint) {
                    return hint;
                }
            }

            return this.placeholder;
        },

        /**
         * @inheritDoc
         */
        _formatDisplayValue: function(value) {
            Datepicker.init($('<input>'), this.datetimepickerOptions).data('datetimepicker');
            _.each(value.value, function(dateValue, name) {
                if (dateValue) {
                    value.value[name] = DateFormatter.format(
                        dateValue,
                        this.datetimepickerOptions.defaultFormat,
                        this.datetimepickerOptions.format
                    );
                }
            }, this);

            return value;
        },

        /**
         * @inheritDoc
         */
        _formatRawValue: function(value) {
            _.each(value.value, function(dateValue, name) {
                if (dateValue) {
                    value.value[name] = DateFormatter.format(
                        dateValue,
                        this.datetimepickerOptions.format,
                        this.datetimepickerOptions.defaultFormat
                    );
                }
            }, this);

            return value;
        },

        /**
         * @inheritDoc
         */
        _writeDOMValue: function(value) {
            this._setInputValue(this.criteriaValueSelectors.value.start + ' input', value.value.start);
            this._setInputValue(this.criteriaValueSelectors.value.end + ' input', value.value.end);
            this._setInputValue(this.criteriaValueSelectors.type, value.type);

            return this;
        },

        /**
         * @inheritDoc
         */
        _readDOMValue: function() {
            return {
                type: this._getInputValue(this.criteriaValueSelectors.type),
                value: {
                    start: this._getInputValue(this.criteriaValueSelectors.value.start + ' input'),
                    end:   this._getInputValue(this.criteriaValueSelectors.value.end + ' input')
                }
            };
        },

        /**
         * @inheritDoc
         */
        _focusCriteria: function() {},

        /**
         * @inheritDoc
         */
        _hideCriteria: function() {
            ChoiceFilter.prototype._hideCriteria.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        _triggerUpdate: function(newValue, oldValue) {},

        /**
         * @inheritDoc
         */
        _isValueValid: function(value) {
            if (_.isEqual(value, this.emptyValue) && !_.isEqual(this.value, value)) {
                return true;
            }

            return _.contains(['empty', 'not empty'], value.type) || value.value.start || value.value.end;
        },

        /**
         * @inheritDoc
         */
        _onValueUpdated: function(newValue, oldValue) {
            ChoiceFilter.prototype._onValueUpdated.apply(this, arguments);
            if (_.contains(['empty', 'not empty'], newValue.type)) {
                this.$el.find('.AknFilterDate-separator').hide().end()
                    .find(this.criteriaValueSelectors.value.end).hide().end()
                    .find(this.criteriaValueSelectors.value.start).hide();
            } else {
                this._displayFilterType(newValue.type);
            }
        },

        /**
         * @inheritDoc
         */
        _onClickUpdateCriteria: function(e) {
            this._hideCriteria();
            this.setValue(this._formatRawValue(this._readDOMValue()));
            this.trigger('update');
        },

        /**
         * @inheritDoc
         */
        reset: function() {
            this.setValue(this.emptyValue);
            this.trigger('update');

            return this;
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 486 */
/* unknown exports provided */
/* all exports used */
/*!***************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter/filter/multiselect-filter.js ***!
  \***************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/translator */ 2), __webpack_require__(/*! oro/datafilter/select-filter */ 450)], __WEBPACK_AMD_DEFINE_RESULT__ = function(_, __, SelectFilter) {
    'use strict';

    /**
     * Multiple select filter: filter values as multiple select options
     *
     * @export  oro/datafilter/multiselect-filter
     * @class   oro.datafilter.MultiSelectFilter
     * @extends oro.datafilter.SelectFilter
     */
    return SelectFilter.extend({
        /**
         * Multiselect filter template
         *
         * @property
         */
        template: _.template(
            '<div class="AknActionButton filter-select filter-criteria-selector">' +
                '<% if (showLabel) { %><%= label %>: <% } %>' +
                '<select multiple>' +
                    '<% _.each(options, function (option) { %>' +
                        '<% if(_.isObject(option.value)) { %>' +
                            '<optgroup label="<%= option.label %>">' +
                                '<% _.each(option.value, function (value) { %>' +
                                    '<option value="<%= value.value %>"><%= value.label %></option>' +
                                '<% }); %>' +
                            '</optgroup>' +
                        '<% } else { %>' +
                            '<option value="<%= option.value %>"><%= option.label %></option>' +
                        '<% } %>' +
                    '<% }); %>' +
                '</select>' +
            '</div>' +
            '<% if (canDisable) { %><a href="<%= nullLink %>" class="AknFilterBox-disableFilter disable-filter"><i class="icon-remove hide-text"><%- _.__("Close") %></i></a><% } %>'
        ),

        /**
         * Select widget options
         *
         * @property
         */
        widgetOptions: {
            multiple: true,
            classes: 'AknActionButton-selectButton select-filter-widget multiselect-filter-widget'
        },

        _onSelectChange: function() {
            var data = this._readDOMValue();

            // At initialization, the value is `''` which mean 'All' but it should be `['']`
            var previousValue = '' === this.getValue().value ? [''] : this.getValue().value;

            // We try to guess if the user added 'All' to remove all previous selection
            var addAll = _.contains(_.difference(data.value, previousValue), '');

            data.value = _.contains(data.value, '') ? _.without(data.value, '') : data.value;
            data.value = _.isEmpty(data.value) ? [''] : data.value;
            data.value = addAll ? [''] : data.value;

            // set value
            this.setValue(this._formatRawValue(data));

            // update dropdown
            var widget = this.$(this.containerSelector);
            this.selectWidget.updateDropdownPosition(widget);
            this._setDropdownWidth();
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 487 */
/* unknown exports provided */
/* all exports used */
/*!*****************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter/filters-manager.js ***!
  \*****************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backbone */ 6), __webpack_require__(/*! oro/mediator */ 8), __webpack_require__(/*! oro/multiselect-decorator */ 455)], __WEBPACK_AMD_DEFINE_RESULT__ = function($, _, Backbone, mediator, MultiselectDecorator) {
    'use strict';

    /**
     * View that represents all grid filters
     *
     * @export  oro/datafilter/filters-manager
     * @class   oro.datafilter.FiltersManager
     * @extends Backbone.View
     *
     * @event updateList    on update of filter list
     * @event updateFilter  on update data of specific filter
     * @event disableFilter on disable specific filter
     */
    return Backbone.View.extend({
        /**
         * List of filter objects
         *
         * @property
         */
        filters: {},

        /**
         * Container tag name
         *
         * @property
         */
        tagName: 'div',

        /**
         * Container classes
         *
         * @property
         */
        className: 'AknFilterBox filter-box oro-clearfix-width',

        /**
         * Filter list template
         *
         * @property
         */
        addButtonTemplate: _.template(
            '<select id="add-filter-select" multiple>' +
                '<%  var groups = [_.__("system_filter_group")];' +
                    '_.each(filters, function(filter) {' +
                        'if (filter.group) {' +
                            'var key = filter.groupOrder !== null ? filter.groupOrder : "last";' +
                            'if (_.isUndefined(groups[key])) {' +
                                'groups[key] = filter.group;' +
                            '} else if (!_.contains(groups, filter.group)) {' +
                                'groups.push(filter.group);' +
                            '}' +
                        '} else {' +
                            'filter.group = _.__("system_filter_group");' +
                        '} ' +
                   '});' +
                '%>' +
                '<% _.each(groups, function (group) { %>' +
                    '<optgroup label="<%= group %>">' +
                        '<% _.each(filters, function (filter, name) { %>' +
                            '<% if (filter.group == group) { %>' +
                                '<option value="<%= name %>" <% if (filter.enabled) { %>selected<% } %>>' +
                                    '<%= filter.label %>' +
                                '</option>' +
                                '<% } %>' +
                        '<% }); %>' +
                    '</optgroup>' +
                '<% }); %>' +
            '</select>'
        ),

        /**
         * Filter list input selector
         *
         * @property
         */
        filterSelector: '#add-filter-select',

        /**
         * Add filter button hint
         *
         * @property
         */
        addButtonHint: _.__('oro_filter.filters.manage'),

        /**
         * Select widget object
         *
         * @property {oro.MultiselectDecorator}
         */
        selectWidget: null,

        /**
         * ImportExport button selector
         *
         * @property
         */
        buttonSelector: '.ui-multiselect.filter-list',

        /** @property */
        events: {
            'change #add-filter-select': '_onChangeFilterSelect'
        },

        /**
         * Initialize filter list options
         *
         * @param {Object} options
         * @param {Object} [options.filters]
         * @param {String} [options.addButtonHint]
         */
        initialize: function (options)
        {
            if (options.filters) {
                this.filters = options.filters;
            }

            _.each(this.filters, function (filter) {
                this.listenTo(filter, "update", this._onFilterUpdated);
                this.listenTo(filter, "disable", this._onFilterDisabled);
            }, this);

            if (options.addButtonHint) {
                this.addButtonHint = options.addButtonHint;
            }

            Backbone.View.prototype.initialize.apply(this, arguments);

            // destroy events bindings
            mediator.once('hash_navigation_request:start', function () {
                _.each(this.filters, function (filter) {
                    this.stopListening(filter, "update", this._onFilterUpdated);
                    this.stopListening(filter, "disable", this._onFilterDisabled);
                }, this);
            }, this);
        },

        /**
         * Triggers when filter is updated
         *
         * @param {oro.datafilter.AbstractFilter} filter
         * @protected
         */
        _onFilterUpdated: function (filter) {
            this.trigger('updateFilter', filter);
        },

        /**
         * Triggers when filter is disabled
         *
         * @param {oro.datafilter.AbstractFilter} filter
         * @protected
         */
        _onFilterDisabled: function (filter) {
            this.trigger('disableFilter', filter);
            this.disableFilter(filter);
        },

        /**
         * Returns list of filter raw values
         */
        getValues: function () {
            var values = {};
            _.each(this.filters, function (filter) {
                if (filter.enabled) {
                    values[filter.name] = filter.getValue();
                }
            }, this);

            return values;
        },

        /**
         * Sets raw values for filters
         */
        setValues: function (values) {
            _.each(values, function (value, name) {
                if (_.has(this.filters, name)) {
                    this.filters[name].setValue(value);
                }
            }, this);
        },

        /**
         * Triggers when filter select is changed
         *
         * @protected
         */
        _onChangeFilterSelect: function () {
            this.trigger('updateList', this);
            this._processFilterStatus();
        },

        /**
         * Enable filter
         *
         * @param {oro.datafilter.AbstractFilter} filter
         * @return {*}
         */
        enableFilter: function (filter) {
            return this.enableFilters([filter]);
        },

        /**
         * Disable filter
         *
         * @param {oro.datafilter.AbstractFilter} filter
         * @return {*}
         */
        disableFilter: function (filter) {
            return this.disableFilters([filter]);
        },

        /**
         * Enable filters
         *
         * @param filters []
         * @return {*}
         */
        enableFilters: function (filters) {
            if (_.isEmpty(filters)) {
                return this;
            }
            var optionsSelectors = [];

            _.each(filters, function (filter) {
                filter.enable();
                optionsSelectors.push('option[value="' + filter.name + '"]:not(:selected)');
            }, this);

            var options = this.$(this.filterSelector).find(optionsSelectors.join(','));
            if (options.length) {
                options.attr('selected', true);
            }

            if (optionsSelectors.length) {
                this.selectWidget.multiselect('refresh');
            }

            return this;
        },

        /**
         * Disable filters
         *
         * @param filters []
         * @return {*}
         */
        disableFilters: function (filters) {
            if (_.isEmpty(filters)) {
                return this;
            }
            var optionsSelectors = [];

            _.each(filters, function (filter) {
                filter.disable();
                optionsSelectors.push('option[value="' + filter.name + '"]:selected');
            }, this);

            var options = this.$(this.filterSelector).find(optionsSelectors.join(','));
            if (options.length) {
                options.removeAttr('selected');
            }

            if (optionsSelectors.length) {
                this.selectWidget.multiselect('refresh');
            }

            return this;
        },

        /**
         * Render filter list
         *
         * @return {*}
         */
        render: function () {
            this.$el.empty();
            var fragment = document.createDocumentFragment();

            _.each(this.filters, function (filter) {
                if (!filter.enabled) {
                    filter.hide();
                }
                if (filter.enabled) {
                    filter.render();
                }
                if (filter.$el.length > 0) {
                    fragment.appendChild(filter.$el.get(0));
                }
            }, this);

            this.trigger("rendered");

            if (_.isEmpty(this.filters)) {
                this.$el.hide();
            } else {
                this.$el.append(this.addButtonTemplate({filters: this.filters}));
                this.$el.append(fragment);
                this._initializeSelectWidget();
            }

            return this;
        },

        /**
         * Initialize multiselect widget
         *
         * @protected
         */
        _initializeSelectWidget: function () {
            this.selectWidget = new MultiselectDecorator({
                element: this.$(this.filterSelector),
                parameters: {
                    multiple: true,
                    selectedList: 0,
                    selectedText: this.addButtonHint,
                    classes: 'AknFilterBox-addFilterButton filter-list select-filter-widget',
                    open: $.proxy(function () {
                        if (this.$el.is(':visible')) {
                            this.selectWidget.onOpenDropdown();
                            this._setDropdownWidth();
                            this._updateDropdownPosition();
                        }
                    }, this)
                }
            });

            this.selectWidget.setViewDesign(this);
            this.selectWidget.getWidget().addClass('pimmultiselect');

            this.$('.filter-list span:first').replaceWith(
                '<a id="add-filter-button" href="javascript:void(0);">' + this.addButtonHint +'</a>'
            );
        },

        /**
         * Set design for select dropdown
         *
         * @protected
         */
        _setDropdownWidth: function () {
            var widget = this.selectWidget.getWidget();
            var requiredWidth = this.selectWidget.getMinimumDropdownWidth() + 24;
            widget.width(requiredWidth).css('min-width', requiredWidth + 'px');
            widget.find('input[type="search"]').width(requiredWidth - 22);
        },

        /**
         * Activate/deactivate all filter depends on its status
         *
         * @protected
         */
        _processFilterStatus: function () {
            var activeFilters = this.$(this.filterSelector).val();

            _.each(this.filters, function (filter, name) {
                if (!filter.enabled && _.indexOf(activeFilters, name) != -1) {
                    this.enableFilter(filter);
                } else if (filter.enabled && _.indexOf(activeFilters, name) == -1) {
                    this.disableFilter(filter);
                }
            }, this);

            this._updateDropdownPosition();
        },

        /**
         * Set dropdown position according to current element
         *
         * @protected
         */
        _updateDropdownPosition: function () {
            var button = this.$(this.buttonSelector);
            var buttonPosition = button.offset();
            var widgetWidth = this.selectWidget.getWidget().outerWidth();
            var windowWidth = $(window).width();
            var widgetLeftOffset = buttonPosition.left;
            if (buttonPosition.left + widgetWidth > windowWidth) {
                widgetLeftOffset = buttonPosition.left + button.outerWidth() - widgetWidth;
            }

            this.selectWidget.getWidget().css({
                top: buttonPosition.top + button.outerHeight(),
                left: widgetLeftOffset
            });
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 488 */
/* unknown exports provided */
/* all exports used */
/*!*********************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datagrid/listener/oro-column-form-listener.js ***!
  \*********************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/*global define*/
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/translator */ 2), __webpack_require__(/*! oro/mediator */ 8), __webpack_require__(/*! oro/modal */ 36), __webpack_require__(/*! oro/datagrid/abstract-listener */ 451)], __WEBPACK_AMD_DEFINE_RESULT__ = function($, _, __, mediator, Modal, AbstractListener) {
    'use strict';

    /**
     * Listener for entity edit form and datagrid
     *
     * @export  oro/datagrid/column-form-listener
     * @class   oro.datagrid.ColumnFormListener
     * @extends oro.datagrid.AbstractListener
     */
    var ColumnFormListener = AbstractListener.extend({

        /** @param {Object} */
        selectors: {
            included: null,
            excluded: null
        },

        /**
         * Initialize listener object
         *
         * @param {Object} options
         */
        initialize: function (options) {
            if (!_.has(options, 'selectors')) {
                throw new Error('Field selectors is not specified');
            }
            this.selectors = options.selectors;

            AbstractListener.prototype.initialize.apply(this, arguments);
        },

        /**
         * Set datagrid instance
         */
        setDatagridAndSubscribe: function () {
            AbstractListener.prototype.setDatagridAndSubscribe.apply(this, arguments);

            this.$gridContainer.on('preExecute:refresh:' + this.gridName, this._onExecuteRefreshAction.bind(this));
            this.$gridContainer.on('preExecute:reset:' + this.gridName, this._onExecuteResetAction.bind(this));
            mediator.on('grid_load:complete', this._restoreState.bind(this));

            this._clearState();
            this._restoreState();
        },

        /**
         * Fills inputs referenced by selectors with ids need to be included and to excluded
         *
         * @param {*} id model id
         * @param {Backbone.Model} model
         * @protected
         */
        _processValue: function(id, model) {
            var original = this.get('original');
            var included = this.get('included');
            var excluded = this.get('excluded');

            var isActive = model.get(this.columnName);
            var originallyActive;
            if (_.has(original, id)) {
                originallyActive = original[id];
            } else {
                originallyActive = !isActive;
                original[id] = originallyActive;
            }

            if (isActive) {
                if (originallyActive) {
                    included = _.without(included, [id]);
                } else {
                    included = _.union(included, [id]);
                }
                excluded = _.without(excluded, id);
            } else {
                included = _.without(included, id);
                if (!originallyActive) {
                    excluded = _.without(excluded, [id]);
                } else {
                    excluded = _.union(excluded, [id]);
                }
            }

            this.set('included', included);
            this.set('excluded', excluded);
            this.set('original', original);

            this._synchronizeState();
        },

        /**
         * Clears state of include and exclude properties to empty values
         *
         * @private
         */
        _clearState: function () {
            this.set('included', []);
            this.set('excluded', []);
            this.set('original', {});
        },

        /**
         * Synchronize values of include and exclude properties with form fields and datagrid parameters
         *
         * @private
         */
        _synchronizeState: function () {
            var included = this.get('included');
            var excluded = this.get('excluded');
            if (this.selectors.included) {
                $(this.selectors.included).val(included.join(','));
            }
            if (this.selectors.excluded) {
                $(this.selectors.excluded).val(excluded.join(','));
            }
        },

        /**
         * Explode string into int array
         *
         * @param string
         * @return {Array}
         * @private
         */
        _explode: function(string) {
            if (!string) {
                return [];
            }
            return _.map(string.split(','), function(val) {return val ? parseInt(val, 10) : null});
        },

        /**
          * Restore values of include and exclude properties
          *
          * @private
          */
        _restoreState: function () {
            var included = '';
            var excluded = '';
            if (this.selectors.included && $(this.selectors.included).length) {
                included = this._explode($(this.selectors.included).val());
                this.set('included', included);
            }
            if (this.selectors.excluded && $(this.selectors.excluded).length) {
                excluded = this._explode($(this.selectors.excluded).val());
                this.set('excluded', excluded)
            }
            if (included || excluded) {
                mediator.trigger('datagrid:restoreState:' + this.gridName, this.columnName, this.dataField, included, excluded);
            }
         },

        /**
         * Confirms refresh action that before it will be executed
         *
         * @param {oro.datagrid.AbstractAction} action
         * @param {Object} options
         * @private
         */
        _onExecuteRefreshAction: function (e, action, options) {
            this._confirmAction(action, options, 'refresh', {
                title: __('Refresh Confirmation'),
                content: __('Your local changes will be lost. Are you sure you want to refresh grid?')
            });
        },

        /**
         * Confirms reset action that before it will be executed
         *
         * @param {oro.datagrid.AbstractAction} action
         * @param {Object} options
         * @private
         */
        _onExecuteResetAction: function(e, action, options) {
            this._confirmAction(action, options, 'reset', {
                title: __('Reset Confirmation'),
                content: __('Your local changes will be lost. Are you sure you want to reset grid?')
            });
        },

        /**
         * Asks user a confirmation if there are local changes, if user confirms then clears state and runs action
         *
         * @param {oro.datagrid.AbstractAction} action
         * @param {Object} actionOptions
         * @param {String} type "reset" or "refresh"
         * @param {Object} confirmModalOptions Options for confirm dialog
         * @private
         */
        _confirmAction: function(action, actionOptions, type, confirmModalOptions) {
            this.confirmed = this.confirmed || {};
            if (!this.confirmed[type] && this._hasChanges()) {
                actionOptions.doExecute = false; // do not execute action until it's confirmed
                this._openConfirmDialog(type, confirmModalOptions, function () {
                    // If confirmed, clear state and run action
                    this.confirmed[type] = true;
                    this._clearState();
                    this._synchronizeState();
                    action.run();
                });
            }
            this.confirmed[type] = false;
        },

        /**
         * Returns TRUE if listener contains user changes
         *
         * @return {Boolean}
         * @private
         */
        _hasChanges: function() {
            return !_.isEmpty(this.get('included')) || !_.isEmpty(this.get('excluded'));
        },

        /**
         * Opens confirm modal dialog
         */
        _openConfirmDialog: function(type, options, callback) {
            this.confirmModal = this.confirmModal || {};
            if (!this.confirmModal[type]) {
                this.confirmModal[type] = new Modal(_.extend({
                    title: __('Confirmation'),
                    okText: __('Ok, got it.'),
                    okButtonClass: 'btn-primary btn-large'
                }, options));
                this.confirmModal[type].on('ok', _.bind(callback, this));
            }
            this.confirmModal[type].open();
        }
    });

    ColumnFormListener.init = function ($gridContainer, gridName) {
        var metadata = $gridContainer.data('metadata');
        var options = metadata.options || {};
        if (options.columnListener) {
            new ColumnFormListener(_.extend({ $gridContainer: $gridContainer, gridName: gridName }, options.columnListener));
        }
    };

    return ColumnFormListener;
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 489 */
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/common/column-list-view.js ***!
  \************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
    __webpack_require__(/*! jquery */ 1),
    __webpack_require__(/*! underscore */ 0),
    __webpack_require__(/*! oro/translator */ 2),
    __webpack_require__(/*! backbone */ 6),
    __webpack_require__(/*! pim/template/datagrid/configure-columns-action */ 463)
], __WEBPACK_AMD_DEFINE_RESULT__ = function (
    $,
    _,
    __,
    Backbone,
    template
) {
    var Column = Backbone.Model.extend({
        defaults: {
            label: '',
            displayed: false,
            group: __('system_filter_group')
        }
    });

    var ColumnList = Backbone.Collection.extend({ model: Column });

    return Backbone.View.extend({
        collection: ColumnList,

        template: _.template(template),

        events: {
            'input input[type="search"]':      'search',
            'click .nav-list li':              'filter',
            'click button.reset':              'reset',
            'click #column-selection .action': 'remove'
        },

        search: function (e) {
            var search = $(e.currentTarget).val();

            var matchesSearch = function (text) {
                return ('' + text).toUpperCase().indexOf(('' + search).toUpperCase()) >= 0;
            };

            this.$('#column-list').find('li').each(function () {
                if (matchesSearch($(this).data('value')) || matchesSearch($(this).text())) {
                    $(this).removeClass('AknVerticalList-item--hide');
                } else {
                    $(this).addClass('AknVerticalList-item--hide');
                }
            });
        },

        filter: function (e) {
            var filter = $(e.currentTarget).data('value');

            $(e.currentTarget).addClass('active').siblings('.active').removeClass('active');

            if (_.isUndefined(filter)) {
                this.$('#column-list li').removeClass('AknVerticalList-item--hide');
            } else {
                this.$('#column-list').find('li').each(function () {
                    if (filter === $(this).data('group')) {
                        $(this).removeClass('AknVerticalList-item--hide');
                    } else {
                        $(this).addClass('AknVerticalList-item--hide');
                    }
                });
            }
        },

        remove: function (e) {
            var $item = $(e.currentTarget).parent();
            $item.appendTo(this.$('#column-list'));

            var model = _.first(this.collection.where({code: $item.data('value')}));
            model.set('displayed', false);

            this.validateSubmission();
        },

        reset: function () {
            _.each(this.collection.where({displayed: true, removable: true}), function (model) {
                model.set('displayed', false);
                this.$('#column-selection li[data-value="' + model.get('code') + '"]').appendTo(this.$('#column-list'));
            }.bind(this));
            this.validateSubmission();
        },

        render: function () {
            var systemColumn = this.collection.where({group: __('system_filter_group')});

            var groups = 0 !== systemColumn.length ?
                [{ position: 0, name: _.__('system_filter_group'), itemCount: 0 }] :
                [];

            _.each(this.collection.toJSON(), function (column) {
                if (_.isEmpty(_.where(groups, {name: column.group}))) {
                    var position = parseInt(column.groupOrder, 10);
                    if (!_.isNumber(position) || !_.isEmpty(_.where(groups, {position: position}))) {
                        position = _.max(groups, function (group) {
                            return group.position;
                        }) + 1;
                    }

                    groups.push({
                        position:  position,
                        name:      column.group,
                        itemCount: 1
                    });
                } else {
                    _.first(_.where(groups, {name: column.group})).itemCount += 1;
                }
            });

            groups = _.sortBy(groups, function (group) {
                return group.position;
            });

            this.$el.html(
                this.template({
                    groups:  groups,
                    columns: this.collection.toJSON()
                })
            );

            this.$('#column-list, #column-selection').sortable({
                connectWith: '.connected-sortable',
                containment: this.$el,
                tolerance: 'pointer',
                cursor: 'move',
                cancel: 'div.alert',
                receive: function (event, ui) {
                    var model = _.first(this.collection.where({code: ui.item.data('value')}));
                    model.set('displayed', ui.sender.is('#column-list') && model.get('removable'));

                    if (!model.get('removable')) {
                        $(ui.sender).sortable('cancel');
                    } else {
                        this.validateSubmission();
                    }
                }.bind(this)
            }).disableSelection();

            this.$('ul').css('height', $(window).height() * 0.7);

            return this;
        },

        validateSubmission: function () {
            if (this.collection.where({displayed: true}).length) {
                this.$('.alert').hide();
                this.$('.AknMessageBox--error').addClass('AknMessageBox--hide');
                this.$el.closest('.modal')
                    .find('.btn.ok:not(.btn-primary)')
                    .addClass('btn-primary')
                    .attr('disabled', false);
            } else {
                this.$('.alert').show();
                this.$('.AknMessageBox--error').removeClass('AknMessageBox--hide');
                this.$el.closest('.modal')
                    .find('.btn.ok.btn-primary')
                    .removeClass('btn-primary')
                    .attr('disabled', true);
            }
        },

        getDisplayed: function () {
            return _.map(this.$('#column-selection li'), function (el) {
                return $(el).data('value');
            });
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 490 */
/* unknown exports provided */
/* all exports used */
/*!******************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/generator/media-url-generator.js ***!
  \******************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! routing */ 7)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        _,
        Routing
    ) {
        return {
            /**
             * Get the show media URL
             *
             * @param string filePath
             * @param string filter
             *
             * @return {string}
             */
            getMediaShowUrl: function (filePath, filter) {
                var filename = encodeURIComponent(filePath);

                return Routing.generate('pim_enrich_media_show', {
                    filename: filename,
                    filter: filter
                });
            },

            /**
             * Get the download media URL
             *
             * @param string filePath
             *
             * @return {string}
             */
            getMediaDownloadUrl: function (filePath) {
                var filename = encodeURIComponent(filePath);

                return Routing.generate('pim_enrich_media_download', {
                    filename: filename
                });
            }
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 491 */
/* unknown exports provided */
/* all exports used */
/*!***************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/pim-optionform.js ***!
  \***************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! pim/dialogform */ 91), __webpack_require__(/*! oro/messenger */ 12), __webpack_require__(/*! pim/initselect2 */ 30), __webpack_require__(/*! jquery.select2 */ 11)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, DialogForm, messenger, initSelect2) {
        'use strict';

        var init = function (fieldId) {
            var $field = $(fieldId);
            var $target = $field.parent().find('.icons-container').first();
            if ($target.length) {
                $field.insertBefore($target).attr('tabIndex', -1);
            }
            var callback = function (data) {
                if (data.status) {
                    var $select = $field.siblings('input.pim-ajax-entity');
                    var selectData = { id: data.option.id, text: data.option.label };
                    if ($select.attr('data-multiple')) {
                        selectData = (function (newElement) {
                            var selectData = $select.select2('data');
                            selectData.push(newElement);

                            return selectData;
                        })(selectData);
                    }
                    $select.select2('destroy');
                    initSelect2.initSelect($select);
                    $select.trigger('change');
                    $select.select2('data', selectData);
                    messenger.notificationFlashMessage('success', $field.data('success-message'));
                } else {
                    messenger.notificationFlashMessage('error', $field.data('error-message'));
                }
            };
            new DialogForm(fieldId, callback);
        };

        return {
            init: init
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 492 */
/* unknown exports provided */
/* all exports used */
/*!*********************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/product/field/multi-select-field.js ***!
  \*********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Multi select field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! pim/field */ 84),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! pim/template/product/field/multi-select */ 475),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! pim/attribute-option/create */ 458),
        __webpack_require__(/*! pim/security-context */ 21),
        __webpack_require__(/*! pim/initselect2 */ 30),
        __webpack_require__(/*! pim/user-context */ 5),
        __webpack_require__(/*! pim/i18n */ 9),
        __webpack_require__(/*! pim/attribute-manager */ 24)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        Field,
        _,
        fieldTemplate,
        Routing,
        createOption,
        SecurityContext,
        initSelect2,
        UserContext,
        i18n,
        AttributeManager
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            choicePromise: null,
            promiseIdentifiers: null,
            events: {
                'change .field-input:first input.select-field': 'updateModel',
                'click .add-attribute-option': 'createOption'
            },

            /**
             * {@inheritdoc}
             */
            getTemplateContext: function () {
                return Field.prototype.getTemplateContext.apply(this, arguments).then(function (templateContext) {
                    var isAllowed = SecurityContext.isGranted('pim_enrich_attribute_edit');
                    templateContext.userCanAddOption = this.editable && isAllowed;

                    return templateContext;
                }.bind(this));
            },

            /**
             * Create a new option for this multi select field
             */
            createOption: function () {
                if (!SecurityContext.isGranted('pim_enrich_attribute_edit')) {
                    return;
                }
                createOption(this.attribute).then(function (option) {
                    if (this.isEditable()) {
                        var value = this.getCurrentValue().data;
                        value.push(option.code);
                        this.setCurrentValue(value);
                    }

                    this.choicePromise = null;
                    this.render();
                }.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },

            /**
             * {@inheritdoc}
             */
            postRender: function () {
                this.$('[data-toggle="tooltip"]').tooltip();
                this.getChoiceUrl().then(function (choiceUrl) {
                    var options = {
                        ajax: {
                            url: choiceUrl,
                            quietMillis: 250,
                            cache: true,
                            data: function (term, page) {
                                return {
                                    search: term,
                                    options: {
                                        limit: 20,
                                        page: page
                                    }
                                };
                            }.bind(this),
                            results: function (response) {
                                if (response.results) {
                                    response.more = 20 === _.keys(response.results).length;

                                    return response;
                                }

                                var data = {
                                    more: 20 === _.keys(response).length,
                                    results: []
                                };
                                _.each(response, function (value) {
                                    data.results.push(this.convertBackendItem(value));
                                }.bind(this));

                                return data;
                            }.bind(this)
                        },
                        initSelection: function (element, callback) {
                            var identifiers = AttributeManager.getValue(
                                this.model.attributes.values,
                                this.attribute,
                                UserContext.get('catalogLocale'),
                                UserContext.get('catalogScope')
                            ).data;

                            if (null === this.choicePromise || this.promiseIdentifiers !== identifiers) {
                                this.choicePromise = $.get(choiceUrl, {
                                    options: {
                                        identifiers: identifiers
                                    }
                                });
                                this.promiseIdentifiers = identifiers;
                            }

                            this.choicePromise.then(function (results) {
                                if (_.has(results, 'results')) {
                                    results = results.results;
                                }

                                var choices = _.map($(element).val().split(','), function (choice) {
                                    var option = _.findWhere(results, {code: choice});
                                    if (option) {
                                        return this.convertBackendItem(option);
                                    }

                                    return _.findWhere(results, {id: choice});
                                }.bind(this));
                                callback(_.compact(choices));
                            }.bind(this));
                        }.bind(this),
                        multiple: true
                    };

                    initSelect2.init(this.$('input.select-field'), options);
                }.bind(this));
            },

            /**
             * Get the URL to retrieve the choice list for this select field
             *
             * @returns {Promise}
             */
            getChoiceUrl: function () {
                return $.Deferred().resolve(
                    Routing.generate(
                        'pim_enrich_attributeoption_get',
                        {
                            identifier: this.attribute.code
                        }
                    )
                ).promise();
            },

            /**
             * {@inheritdoc}
             */
            updateModel: function () {
                var data = this.$('.field-input:first input.select-field').val().split(',');
                if (1 === data.length && '' === data[0]) {
                    data = [];
                }

                this.choicePromise = null;

                this.setCurrentValue(data);
            },

            /**
             * Convert the item returned from the backend to fit select2 needs
             *
             * @param {object} item
             *
             * @return {object}
             */
            convertBackendItem: function (item) {
                return {
                    id: item.code,
                    text: i18n.getLabel(item.labels, UserContext.get('catalogLocale'), item.code)
                };
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 493 */
/* unknown exports provided */
/* all exports used */
/*!**********************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/product/field/simple-select-field.js ***!
  \**********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Simple select field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! pim/field */ 84),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! pim/template/product/field/simple-select */ 478),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! pim/attribute-option/create */ 458),
        __webpack_require__(/*! pim/security-context */ 21),
        __webpack_require__(/*! pim/initselect2 */ 30),
        __webpack_require__(/*! pim/user-context */ 5),
        __webpack_require__(/*! pim/i18n */ 9)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, Field, _, fieldTemplate, Routing, createOption, SecurityContext, initSelect2, UserContext, i18n) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            choicePromise: null,
            promiseIdentifier: null,
            events: {
                'change .field-input:first input[type="hidden"].select-field': 'updateModel',
                'click .add-attribute-option': 'createOption'
            },

            /**
             * {@inheritdoc}
             */
            getTemplateContext: function () {
                return Field.prototype.getTemplateContext.apply(this, arguments).then(function (templateContext) {
                    var isAllowed = SecurityContext.isGranted('pim_enrich_attribute_edit');
                    templateContext.userCanAddOption = this.editable && isAllowed;

                    return templateContext;
                }.bind(this));
            },

            /**
             * Create a new option for this simple select field
             */
            createOption: function () {
                if (!SecurityContext.isGranted('pim_enrich_attribute_edit')) {
                    return;
                }

                createOption(this.attribute).then(function (option) {
                    if (this.isEditable()) {
                        this.setCurrentValue(option.code);
                    }

                    this.choicePromise = null;
                    this.render();
                }.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },

            /**
             * {@inheritdoc}
             */
            postRender: function () {
                this.$('[data-toggle="tooltip"]').tooltip();
                this.getChoiceUrl().then(function (choiceUrl) {
                    var options = {
                        ajax: {
                            url: choiceUrl,
                            cache: true,
                            data: function (term, page) {
                                return {
                                    search: term,
                                    options: {
                                        limit: 20,
                                        page: page
                                    }
                                };
                            },
                            results: function (response) {
                                if (response.results) {
                                    response.more = 20 === _.keys(response.results).length;

                                    return response;
                                }

                                var data = {
                                    more: 20 === _.keys(response).length,
                                    results: []
                                };
                                _.each(response, function (value) {
                                    data.results.push(this.convertBackendItem(value));
                                }.bind(this));

                                return data;
                            }.bind(this)
                        },
                        initSelection: function (element, callback) {
                            var id = $(element).val();
                            if ('' !== id) {
                                if (null === this.choicePromise || this.promiseIdentifier !== id) {
                                    this.choicePromise = $.get(choiceUrl, {options: {identifiers: [id]}});
                                    this.promiseIdentifier = id;
                                }

                                this.choicePromise.then(function (response) {
                                    var selected = _.findWhere(response, {code: id});

                                    if (!selected) {
                                        selected = _.findWhere(response.results, {id: id});
                                    } else {
                                        selected = this.convertBackendItem(selected);
                                    }
                                    callback(selected);
                                }.bind(this));
                            }
                        }.bind(this),
                        placeholder: ' ',
                        allowClear: true
                    };

                    initSelect2.init(this.$('input.select-field'), options);
                }.bind(this));
            },

            /**
             * Get the URL to retrieve the choice list for this select field
             *
             * @returns {Promise}
             */
            getChoiceUrl: function () {
                return $.Deferred().resolve(
                    Routing.generate(
                        'pim_enrich_attributeoption_get',
                        {
                            identifier: this.attribute.code
                        }
                    )
                ).promise();
            },

            /**
             * {@inheritdoc}
             */
            updateModel: function () {
                var data = this.$('.field-input:first input[type="hidden"].select-field').val();
                data = '' === data ? this.attribute.empty_value : data;

                this.choicePromise = null;

                this.setCurrentValue(data);
            },

            /**
             * Convert the item returned from the backend to fit select2 needs
             *
             * @param {object} item
             *
             * @return {object}
             */
            convertBackendItem: function (item) {
                return {
                    id: item.code,
                    text: i18n.getLabel(item.labels, UserContext.get('catalogLocale'), item.code)
                };
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 494 */
/* unknown exports provided */
/* all exports used */
/*!*********************************************************************************************!*\
  !*** ./src/Pim/Bundle/NavigationBundle/Resources/public/js/navigation/dotmenu/item-view.js ***!
  \*********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backbone */ 6)], __WEBPACK_AMD_DEFINE_RESULT__ = function($, _, Backbone) {
    'use strict';

    /**
     * @export  oro/navigation/dotmenu/item-view
     * @class   oro.navigation.dotmenu.ItemView
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        tagName:  'li',

        template: _.template($('#template-dot-menu-item').html()),

        events: {
            'click .close': 'close',
            'click span': 'activate'
        },

        initialize: function() {
            this.listenTo(this.model, 'destroy', this.remove);
        },

        activate: function(e) {
            var el = Backbone.$(e.currentTarget);
            window.location.href = el.data('url');
        },

        close: function() {
            this.model.destroy({wait: true});
        },

        render: function() {
            this.$el.html(
                this.template(this.model.toJSON())
            );
            if (this.model.get('url') ===  window.location.pathname) {
                this.$el.addClass('active');
            }
            return this;
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 495 */
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************************!*\
  !*** ./src/Pim/Bundle/NavigationBundle/Resources/public/js/navigation/dotmenu/view.js ***!
  \****************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backbone */ 6), __webpack_require__(/*! oro/mediator */ 8), __webpack_require__(/*! oro/navigation/dotmenu/item-view */ 494)], __WEBPACK_AMD_DEFINE_RESULT__ = function($, _, Backbone, mediator, DotmenuItemView) {
    'use strict';

    /**
     * @export  oro/navigation/dotmenu/view
     * @class   oro.navigation.dotmenu.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        options: {
            el: '.pin-menus .tabbable',
            defaultTabOptions: {
                hideOnEmpty: false
            }
        },
        tabs: {},

        templates: {
            tab: _.template($("#template-dot-menu-tab").html()),
            content: _.template($("#template-dot-menu-tab-content").html()),
            emptyMessage: _.template($("#template-dot-menu-empty-message").html())
        },

        initialize: function() {
            this.$tabsContainer = this.$('.pin-menus .nav-tabs');
            this.$tabsContent = this.$('.pin-menus .tab-content');
            this.init();
            mediator.bind(
                "hash_navigation_request:complete",
                function() {
                    this.init();
                },
                this
            );
            mediator.bind(
                "tab:changed",
                function(tabId) {
                    this.chooseActiveTab(tabId);
                },
                this
            );
            this.chooseActiveTab();
        },

        init: function() {
            this.$tabsContent.find('.menu-close').click(_.bind(this.close, this));
        },

        addTab: function(options) {
            var data = _.extend(this.options.defaultTabOptions, options);

            data.$tab = this.$('#' + data.key + '-tab');
            if (!data.$tab.length) {
                data.$tab = $(this.templates.tab(data));
                this.$tabsContainer.append(data.$tab);
            }

            data.$tabContent = this.$('#' + data.key + '-content');
            if (!data.$tabContent.length) {
                data.$tabContent = $(this.templates.content(data));
                this.$tabsContent.append(data.$tabContent);
            }

            data.$tabContentContainer = data.$tabContent.find('ul');
            this.tabs[data.key] = _.clone(data);
        },

        getTab: function(key) {
            return this.tabs[key];
        },

        addTabItem: function(tabKey, item, prepend) {
            if (this.isTabEmpty(tabKey)) {
                this.cleanup(tabKey);
            }
            var el = null;
            if (_.isElement(item)) {
                el = item;
            } else if (_.isObject(item)) {
                if (!_.isFunction(item.render)) {
                    item = new DotmenuItemView({model: item});
                }
                el = item.render().$el;
            }

            if (el) {
                if (prepend) {
                    this.getTab(tabKey).$tabContentContainer.prepend(el);
                } else {
                    this.getTab(tabKey).$tabContentContainer.append(el);
                }
            }
            /**
             * Backbone event. Fired when item is added to menu
             * @event navigation_item:added
             */
            mediator.trigger("navigation_item:added", el);
        },

        cleanup: function(tabKey) {
            this.getTab(tabKey).$tabContentContainer.empty();
        },

        checkTabContent: function(tabKey) {
            var isEmpty = this.isTabEmpty(tabKey);
            if (isEmpty) {
                this.hideTab(tabKey);
            } else {
                this.showTab(tabKey);
            }
        },

        /**
         * Checks if first tab in 3 dots menu is empty
         *
         * @return {Boolean}
         */
        isFirstTabEmpty: function() {
            var children = this.$tabsContent.children();
            return children && children.first().size() &&
                (!children.first().html().trim() ||
                !children.first().find('ul').html());
        },

        /**
         * Set default tab as active based on config class
         */
        setDefaultNonEmptyTab: function() {
            this.$('.show-if-empty a').tab('show');
        },

        /**
         * Set active dots menu tab.
         *
         * @param tabId
         */
        chooseActiveTab: function(tabId) {
            if (_.isUndefined(tabId)) {
                if (this.isFirstTabEmpty()) {
                    this.setDefaultNonEmptyTab();
                }
            } else {
                if (this.getTab(tabId).$tab.index() == 0) {
                    if (!this.isTabEmpty(tabId)) {
                        this.tabs[tabId].$tab.find('a').tab('show');
                    } else {
                        this.setDefaultNonEmptyTab();
                    }
                }
            }
        },

        isTabEmpty: function(tabKey) {
            var tab = this.getTab(tabKey);
            return !tab.$tabContentContainer.children().length || tab.$tabContentContainer.html() == this.templates.emptyMessage();
        },

        hideTab: function(tabKey) {
            var tab = this.getTab(tabKey);
            if (tab.hideOnEmpty) {
                tab.$tab.hide();
            } else {
                this.getTab(tabKey).$tabContentContainer.html(this.templates.emptyMessage());
            }
        },

        showTab: function(tabKey) {
            this.getTab(tabKey).$tab.show();
        },

        close: function() {
            this.$el.parents('.open').removeClass('open');
            return false;
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 496 */
/* unknown exports provided */
/* all exports used */
/*!*********************************************************************************************!*\
  !*** ./src/Pim/Bundle/NavigationBundle/Resources/public/js/navigation/pinbar/collection.js ***!
  \*********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! oro/navigation/collection */ 460), __webpack_require__(/*! oro/navigation/pinbar/model */ 461)], __WEBPACK_AMD_DEFINE_RESULT__ = function(NavigationCollection, PinbarModel) {
    'use strict';

    /**
     * @export  oro/navigation/pinbar/collection
     * @class   oro.navigation.pinbar.Collection
     * @extends oro.navigation.Collection
     */
    return NavigationCollection.extend({
        model: PinbarModel,

        initialize: function() {
            this.on('change:position', this.onPositionChange, this);
            this.on('change:url', this.onUrlChange, this);
            this.on('change:maximized', this.onStateChange, this);
        },

        onPositionChange: function(item) {
            this.trigger('positionChange', item);
        },

        onStateChange: function(item) {
            this.trigger('stateChange', item);
        },

        onUrlChange: function(item) {
            this.trigger('urlChange', item);
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 497 */
/* unknown exports provided */
/* all exports used */
/*!********************************************************************************************!*\
  !*** ./src/Pim/Bundle/NavigationBundle/Resources/public/js/navigation/pinbar/item-view.js ***!
  \********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backbone */ 6), __webpack_require__(/*! oro/app */ 33), __webpack_require__(/*! oro/mediator */ 8), __webpack_require__(/*! oro/error */ 85)], __WEBPACK_AMD_DEFINE_RESULT__ = function($, _, Backbone, app, mediator, error) {
    'use strict';

    /**
     * @export  oro/navigation/pinbar/item-view
     * @class   oro.navigation.pinbar.ItemView
     * @extends Backbone.View
     */
    return Backbone.View.extend({

        options: {
            type: 'list'
        },

        tagName:  'li',

        isRemoved: false,

        templates: {
            list: _.template($("#template-list-pin-item").html()),
            tab: _.template($("#template-tab-pin-item").html())
        },

        events: {
            'click .btn-close': 'unpin',
            'click .close': 'unpin',
            'click .pin-holder .AknHeader-pinLink': 'maximize',
            'click span': 'maximize'
        },

        initialize: function() {
            this.listenTo(this.model, 'destroy', this.removeItem);
            this.listenTo(this.model, 'change:display_type', this.removeItem);
            this.listenTo(this.model, 'change:remove', this.unpin);
            /**
             * Change active pinbar item after hash navigation request is completed
             */
            mediator.bind(
                "route_complete",
                function() {
                    /*if (!this.isRemoved && this.checkCurrentUrl()) {
                        this.maximize();
                    }*/
                    this.setActiveItem();
                },
                this
            );
        },

        unpin: function() {
            mediator.trigger("pinbar_item_remove_before", this.model);
            this.model.destroy({
                wait: true,
                error: _.bind(function(model, xhr, options) {
                    if (xhr.status == 404 && !app.debug) {
                        // Suppress error if it's 404 response and not debug mode
                        this.removeItem();
                    } else {
                        error.dispatch(model, xhr, options);
                    }
                }, this)
            });
            return false;
        },

        maximize: function() {
            this.model.set('maximized', new Date().toISOString());
            return false;
        },

        removeItem: function() {
            this.isRemoved = true;
            this.remove();
        },

        checkCurrentUrl: function() {
            var url = Backbone.history.getFragment();
            var modelUrl = this.model.get('url');

            return modelUrl === url;
        },

        setActiveItem: function() {
            if (this.checkCurrentUrl()) {
                this.$el.addClass('active');
            } else {
                this.$el.removeClass('active');
            }
        },

        render: function () {
            this.$el.html(
                this.templates[this.options.type](this.model.toJSON())
            );
            this.setActiveItem();
            return this;
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 498 */
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************!*\
  !*** ./src/Pim/Bundle/NotificationBundle/Resources/public/js/indicator.js ***!
  \****************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! backbone */ 6), __webpack_require__(/*! underscore */ 0)], __WEBPACK_AMD_DEFINE_RESULT__ = function (Backbone, _) {
        'use strict';

        var Indicator = Backbone.Model.extend({
            defaults: {
                value: null,
                className: 'AknBell-count',
                emptyClass: 'AknBell-count--hidden',
                nonEmptyClass: ''
            }
        });

        var IndicatorView = Backbone.View.extend({
            model: Indicator,

            template: _.template(
                '<span class="<%= className %> <%= value ? nonEmptyClass : emptyClass %>"><%= value %></span>'
            ),

            initialize: function () {
                this.listenTo(this.model, 'change', this.render);

                this.render();
            },

            render: function () {
                this.$el.html(this.template(this.model.toJSON()));

                return this;
            }
        });

        return function (opts) {
            var el = opts.el || null;
            delete opts.el;
            var indicator = new Indicator(opts);
            var indicatorView = new IndicatorView({el: el, model: indicator});
            indicator.setElement = function () {
                indicatorView.setElement.apply(indicatorView, arguments);

                return indicatorView.render();
            };

            return indicator;
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 499 */
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************!*\
  !*** ./src/Pim/Bundle/NotificationBundle/Resources/public/js/notification-list.js ***!
  \************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! backbone */ 6),
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! pim/router */ 13),
        __webpack_require__(/*! pim/template/notification/notification-list */ 481)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (Backbone, $, _, Routing, router, template) {
        'use strict';

        var Notification = Backbone.Model.extend({
            defaults: {
                viewed:            false,
                url:               null,
                message:           '',
                id:                null,
                type:              'success',
                createdAt:         null,
                actionType:        null,
                actionTypeMessage: null,
                showReportButton:  true,
                comment:           null
            }
        });

        var NotificationList = Backbone.Collection.extend({
            model:     Notification,
            loading:   false,
            hasMore:   true
        });

        var NotificationView = Backbone.View.extend({
            tagName: 'li',
            className: 'AknNotification',
            model: Notification,
            template: _.template(template),
            events: {
                'click .icon-trash':     'remove',
                'click .icon-eye-close': 'markAsRead',
                'click i':               'preventOpen',
                'click a.new':           'markAsRead',
                'click a':               'open'
            },

            remove: function () {
                this.model.destroy({
                    url: Routing.generate('pim_notification_notification_remove', { id: this.model.get('id') }),
                    wait: false,
                    _method: 'DELETE'
                });

                this.$el.fadeOut(function () {
                    this.remove();
                });
            },

            open: function (e) {
                this.preventOpen(e);
                if (this.model.get('url')) {
                    router.redirect(this.model.get('url'));
                }
                this.$el.closest('.dropdown').removeClass('open');
            },

            preventOpen: function (e) {
                e.preventDefault();
                e.stopPropagation();
            },

            markAsRead: function () {
                this.model.trigger('mark_as_read', this.model.id);
                this.model.set('viewed', true);
                $.ajax({
                    type: 'POST',
                    url: Routing.generate('pim_notification_notification_mark_viewed', {id: this.model.id}),
                    async: true
                });
            },

            initialize: function () {
                this.listenTo(this.model, 'change', this.render);

                this.render();
            },

            render: function () {
                this.$el.html(
                    this.template({
                        viewed: this.model.get('viewed'),
                        message: this.model.get('message'),
                        url: this.model.get('url'),
                        icon: this.getIcon(this.model.get('type')),
                        type: this.model.get('type'),
                        createdAt: this.model.get('createdAt'),
                        actionType: this.camelize(this.model.get('actionType')),
                        buttonLabel: this.model.get('buttonLabel'),
                        actionTypeMessage: this.model.get('actionTypeMessage'),
                        showReportButton: this.model.get('showReportButton'),
                        comment: this.model.get('comment')
                    }
                ));

                return this;
            },

            getIcon: function (type) {
                var icons = {
                    'success': 'ok',
                    'warning': 'warning-sign',
                    'error':   'remove',
                    'add':     'plus'
                };

                return _.result(icons, type, 'remove');
            },

            camelize: function (str) {
                return str.toLowerCase()
                    .replace(/_(.)/g, function ($firstLetter) {
                        return $firstLetter.toUpperCase();
                    })
                    .replace(/_/g, '');
            }
        });

        var NotificationListView = Backbone.View.extend({
            tagName: 'ol',

            collection: NotificationList,

            events: {
                'scroll': 'onScroll'
            },

            initialize: function () {
                _.bindAll(this, 'render');

                this.collection.on('add reset', this.render);

                this.render();
            },

            onScroll: function () {
                var self = this;
                this.$el.on('scroll', function () {
                    if ($(this).scrollTop() + $(this).innerHeight() >= this.scrollHeight) {
                        self.loadNotifications();
                    }
                });
            },

            loadNotifications: function () {
                if (this.collection.loading || !this.collection.hasMore) {
                    return;
                }

                this.collection.loading = true;

                this.collection.trigger('loading:start');

                $.getJSON(Routing.generate('pim_notification_notification_list') + '?skip=' + this.collection.length)
                    .then(_.bind(function (data) {
                        this.collection.add(data.notifications);
                        this.collection.hasMore = data.notifications.length >= 10;

                        this.collection.trigger('load:unreadCount', data.unreadCount);
                        this.collection.loading = false;
                        this.collection.trigger('loading:finish');
                    }, this));
            },

            render: function () {
                this.$el.empty();

                _.each(this.collection.models, function (model) {
                    this.renderNotification(model);
                }, this);
            },

            renderNotification: function (item) {
                var itemView = new NotificationView({
                    model: item
                });

                this.$el.append(itemView.$el);
            }
        });

        return function (opts) {
            var notificationList = new NotificationList();
            var options = _.extend({}, { el: null, collection: notificationList }, opts);
            var notificationListView = new NotificationListView(options);

            notificationList.setElement = function (element) {
                notificationListView.$el.prependTo(element);
                notificationListView.delegateEvents();
                notificationListView.render();
            };
            notificationList.loadNotifications = function () {
                return notificationListView.loadNotifications();
            };

            return notificationList;
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 500 */
/* unknown exports provided */
/* all exports used */
/*!**********************************************************************!*\
  !*** ./src/Pim/Bundle/UIBundle/Resources/public/js/pim-fileinput.js ***!
  \**********************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! jquery.slimbox */ 462)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($) {
        'use strict';

        var maxFilenameLength = 20;
        var init = function (id) {
            var $el = $('#' + id);
            if (!$el.length) {
                return;
            }

            $el.on('change', function () {
                var $input          = $(this);
                var filename        = $input.val().split('\\').pop();
                var $zone           = $input.parent();
                var $info           = $input.siblings('.upload-info').first();
                var $filename       = $info.find('.upload-filename');
                var $removeBtn      = $input.siblings('.remove-upload');
                var $removeCheckbox = $input.siblings('input[type="checkbox"]');
                var $preview        = $info.find('.upload-preview');

                if (filename) {
                    var title = filename.length > maxFilenameLength ?
                        filename.substring(0, maxFilenameLength - 3) + '...' :
                        filename;
                    $filename.html(title);
                    $zone.removeClass('empty');
                    $preview.removeClass('empty').attr('title', filename);
                    $removeBtn.removeClass('hide');
                    $input.addClass('hide');
                    $removeCheckbox.removeAttr('checked');
                } else {
                    $filename.html($filename.attr('data-empty-title'));
                    $zone.addClass('empty');
                    $preview.addClass('empty').removeAttr('title');
                    $removeBtn.addClass('hide');
                    $input.removeAttr('disabled').removeClass('hide');
                    $removeCheckbox.attr('checked', 'checked');
                }
            });

            $el.parent().on('click', '.remove-upload:not(.disabled)', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $el.wrap('<form>').closest('form').get(0).reset();
                $el.unwrap().trigger('change');
            });

            $el.parent().on('mouseover', '.upload-zone:not(.empty)', function () {
                $el.attr('disabled', 'disabled');
            }).on('mouseout', '.upload-zone:not(.empty)', function () {
                $el.removeAttr('disabled');
            });

            // Initialize slimbox
            if (!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
                $el.parent().find('a[rel^="slimbox"]').slimbox({
                    overlayOpacity: 0.3
                }, null, function (el) {
                    return (this === el) || ((this.rel.length > 8) && (this.rel === el.rel));
                });
            }
        };

        return {
            init: init
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 501 */
/* unknown exports provided */
/* all exports used */
/*!*****************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/filter/simpleselect.html ***!
  \*****************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<label class=\"control-label required\"><%- field %></label>\n<div class=\"controls\">\n    <input type=\"text\" name=\"filter-operator\" value=\"<%- operator %>\"/>\n    <input\n        class=\"select2\"\n        name=\"filter-value\"\n        type=\"hidden\"\n        value=\"<%- value ? value : '' %>\"\n    />\n    <% if (removable) { %><i class=\"remove icon-trash\"></i><% } %>\n</div>\n"

/***/ }),
/* 502 */
/* unknown exports provided */
/* all exports used */
/*!*******************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/save.html ***!
  \*******************************************************************************/
/***/ (function(module, exports) {

module.exports = "<button class=\"AknButton AknButton--apply AknButton--withIcon save\">\n    <i class=\"AknButton-icon icon-ok\"></i>\n    <%- label %>\n</button>\n"

/***/ }),
/* 503 */
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/templates/form/tab/history.html ***!
  \**************************************************************************************/
/***/ (function(module, exports) {

module.exports = "<div class=\"grid-drop\" data-type=\"datagrid\"></div>\n"

/***/ }),
/* 504 */
/* unknown exports provided */
/* all exports used */
/*!********************************!*\
  !*** ./~/text-loader/index.js ***!
  \********************************/
/***/ (function(module, exports) {

module.exports = function (content) {
  this.cacheable && this.cacheable();
  this.value = content;
  return "module.exports = " + JSON.stringify(content);
}


/***/ }),
/* 505 */
/* unknown exports provided */
/* all exports used */
/*!*********************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter-builder.js ***!
  \*********************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/tools */ 94), __webpack_require__(/*! oro/mediator */ 8), __webpack_require__(/*! oro/datafilter/collection-filters-manager */ 484)], __WEBPACK_AMD_DEFINE_RESULT__ = function($, _, tools,  mediator, FiltersManager) {
    'use strict';

    var initialized = false,
        filterModuleName = 'oro/datafilter/{{type}}-filter',
        filterTypes = {
            string:      'choice',
            choice:      'select',
            selectrow:   'select-row',
            multichoice: 'multiselect',
            boolean:     'select'
        },
        methods = {
            initBuilder: function () {
                this.metadata = _.extend({filters: {}}, this.$el.data('metadata'));
                this.modules = {};
                methods.collectModules.call(this);
                tools.loadModules(this.modules, _.bind(methods.build, this));
            },

            /**
             * Collects required modules
             */
            collectModules: function () {
                var modules = this.modules;
                _.each((this.metadata.filters || {}) || {}, function (filter) {
                     var type = filter.type;
                     modules[type] = filterModuleName.replace('{{type}}', filterTypes[type] || type);
                });
            },

            build: function () {
                var options = methods.combineOptions.call(this);
                options.collection = this.collection;
                var filtersList = new FiltersManager(options);
                this.$el.prepend(filtersList.render().$el);
                mediator.trigger('datagrid_filters:rendered', this.collection);
                if (this.collection.length === 0) {
                    filtersList.$el.hide();
                }
                mediator.trigger('datagrid_filters:build.post', filtersList);
            },

            /**
             * Process metadata and combines options for filters
             *
             * @returns {Object}
             */
            combineOptions: function () {
                var filters= {},
                    modules = this.modules,
                    collection = this.collection;
                _.each(this.metadata.filters, function (options) {
                    if (_.has(options, 'name') && _.has(options, 'type')) {
                        // @TODO pass collection only for specific filters
                        if (options.type == 'selectrow') {
                            options.collection = collection
                        }
                        filters[options.name] = new (modules[options.type].extend(options))(options);
                    }
                });
                return {filters: filters};
            }
        },
        initHandler = function (collection, $el) {
            methods.initBuilder.call({$el: $el, collection: collection});
            initialized = true;
        };

    return {
        init: function () {
            initialized = false;

            mediator.once('datagrid_collection_set_after', initHandler);
            mediator.once('hash_navigation_request:start', function() {
                if (!initialized) {
                    mediator.off('datagrid_collection_set_after', initHandler);
                }
            });
        }
    };
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 506 */
/* unknown exports provided */
/* all exports used */
/*!***************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter/filter/ajax-choice-filter.js ***!
  \***************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/datafilter/multiselect-filter */ 486), __webpack_require__(/*! routing */ 7)], __WEBPACK_AMD_DEFINE_RESULT__ = function($, _, MultiSelectFilter, Routing) {
        'use strict';

        return MultiSelectFilter.extend({
            choicesFetched: false,
            choiceUrl: null,
            choiceUrlParams: {},

            initialize: function(options) {
                options = options || {};
                if (_.has(options, 'choiceUrl')) {
                    this.choiceUrl = options.choiceUrl;
                }
                if (_.has(options, 'choiceUrlParams')) {
                    this.choiceUrlParams = options.choiceUrlParams;
                }

                MultiSelectFilter.prototype.initialize.apply(this, arguments);
            },

            render: function () {
                var options =  this.choices.slice(0);
                this.$el.empty();

                if (this.populateDefault) {
                    options.unshift({value: '', label: this.placeholder});
                }

                this.$el.append(
                    this.template({
                        label: this.label,
                        showLabel: this.showLabel,
                        options: options,
                        placeholder: this.placeholder,
                        nullLink: this.nullLink,
                        canDisable: this.canDisable,
                        emptyValue: this.emptyValue
                    })
                );

                if (this.value.value) {
                    _.each(this.value.value, function(item) {
                        this.$(this.inputSelector).find('option[value="' + item + '"]').attr('selected', 'selected');
                    }, this);
                }

                this._initializeSelectWidget();

                return this;
            },

            show: function() {
                if (!this.choicesFetched && !this.choices.length && this.choiceUrl) {
                    var url = Routing.generate(this.choiceUrl, this.choiceUrlParams);

                    $.get(url, _.bind(function(data) {
                        this._updateChoices(data.results);
                        this.render();

                        MultiSelectFilter.prototype.show.apply(this, arguments);
                    }, this));
                } else {
                    MultiSelectFilter.prototype.show.apply(this, arguments);
                }
            },

            _updateChoices: function(results) {
                var choices = [];

                _.each(results, function(result) {
                    choices.push({ value: result.id, label: result.text });
                });
                choices.push({ value: 'empty', label: _.__('pim.grid.ajax_choice_filter.label_empty') });
                choices.push({ value: 'not empty', label: _.__('pim.grid.ajax_choice_filter.label_not_empty') });

                this.choices        = choices;
                this.choicesFetched = true;
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 507 */
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter/filter/datetime-filter.js ***!
  \************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/datafilter/date-filter */ 485), __webpack_require__(/*! pim/date-context */ 42)], __WEBPACK_AMD_DEFINE_RESULT__ = function($, _, DateFilter, DateContext) {
    'use strict';
    /**
     * Datetime filter: filter type as option + interval begin and end dates
     *
     * @export  oro/datafilter/datetime-filter
     * @class   oro.datafilter.DatetimeFilter
     * @extends oro.datafilter.DateFilter
     */
    return DateFilter.extend({
        /**
         * CSS class for visual datetime input elements
         *
         * @property
         */
        inputClass: 'datetime-visual-element',

        /**
         * Date widget options
         *
         * @property
         */
        datetimepickerOptions: {
            format: DateContext.get('time').format,
            defaultFormat: DateContext.get('time').defaultFormat,
            language: DateContext.get('language'),
            pickTime: true,
            pickSeconds: false,
            pick12HourFormat: DateContext.get('12_hour_format'),
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 508 */
/* unknown exports provided */
/* all exports used */
/*!**********************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter/filter/metric-filter.js ***!
  \**********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/datafilter/number-filter */ 87),
        __webpack_require__(/*! oro/app */ 33),
        __webpack_require__(/*! pim/template/datagrid/filter/metric-filter */ 465)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, NumberFilter, app, template) {
        'use strict';

        /**
         * Metric filter
         *
         * @author    Romain Monceau <romain@akeneo.com>
         * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
         * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         *
         * @export  oro/datafilter/metric-filter
         * @class   oro.datafilter.MetricFilter
         * @extends oro.datafilter.NumberFilter
         */
        return NumberFilter.extend({
            /**
             * @inheritDoc
             */
            initialize: function() {
                NumberFilter.prototype.initialize.apply(this, arguments);

                this.on('disable', this._onDisable, this);

            },

            _onDisable: function() {
                this.$('.choicefilter button.dropdown-toggle').first().html(_.__('Action') + '<span class="caret"></span>');
                this.$('.choicefilter button.dropdown-toggle').last().html(_.__('Unit') + '<span class="caret"></span>');
            },

            /**
             * @inheritDoc
             */
            _renderCriteria: function (el) {
                $(el).append(this.popupCriteriaTemplate({
                    name:    this.name,
                    choices: this.choices,
                    units:   this.units
                }));

                return this;
            },

            /**
             * @inheritDoc
             */
            _writeDOMValue: function (value) {
                this._setInputValue(this.criteriaValueSelectors.value, value.value);
                this._setInputValue(this.criteriaValueSelectors.type, value.type);
                this._setInputValue(this.criteriaValueSelectors.unit, value.unit);

                return this;
            },

            /**
             * @inheritDoc
             */
            _readDOMValue: function () {
                return {
                    value: this._getInputValue(this.criteriaValueSelectors.value),
                    type: this._getInputValue(this.criteriaValueSelectors.type),
                    unit: this._getInputValue(this.criteriaValueSelectors.unit)
                };
            },

            /**
             * @inheritDoc
             */
            _getCriteriaHint: function () {
                var value = (arguments.length > 0) ? this._getDisplayValue(arguments[0]) : this._getDisplayValue();
                if (_.contains(['empty', 'not empty'], value.type)) {
                    return this._getChoiceOption(value.type).label;
                }
                if (!value.value) {
                    return this.placeholder;
                } else {
                    var operator = _.find(this.choices, function(choice) {
                        return choice.value == value.type;
                    });
                    operator = operator ? operator.label : '';

                    return operator + ' "' + value.value + ' ' + _.__(value.unit) + '"';
                }
            },

            /**
             * @inheritDoc
             */
            popupCriteriaTemplate: _.template(template),

            /**
             * Selectors for filter criteria elements
             *
             * @property {Object}
             */
            criteriaValueSelectors: {
                unit:  'input[name="metric_unit"]',
                type:  'input[name="metric_type"]',
                value: 'input[name="value"]'
            },

            /**
             * Empty value object
             *
             * @property {Object}
             */
            emptyValue: {
                unit:  '',
                type:  '',
                value: ''
            },

            /**
             * @inheritDoc
             */
            _triggerUpdate: function(newValue, oldValue) {
                if (!app.isEqualsLoosely(newValue, oldValue)) {
                    this.trigger('update');
                }
            },

            /**
             * @inheritDoc
             */
            _onValueUpdated: function(newValue, oldValue) {
                var menu = this.$('.choicefilter .dropdown-menu');

                menu.find('li a').each(function() {
                    var item = $(this),
                        value = item.data('value');

                    if (item.parent().hasClass('active')) {
                        if (value == newValue.type || value == newValue.unit) {
                            item.parent().removeClass('active');
                        } else {
                        }
                    } else if (value == newValue.type || value == newValue.unit) {
                        item.parent().addClass('active');
                        item.closest('.btn-group').find('button').html(item.html() + '<span class="caret"></span>');
                    }
                });
                if (_.contains(['empty', 'not empty'], newValue.type)) {
                    this.$(this.criteriaValueSelectors.value).hide().siblings('.btn-group:eq(1)').hide();
                } else {
                    this.$(this.criteriaValueSelectors.value).show().siblings('.btn-group:eq(1)').show();
                }

                this._triggerUpdate(newValue, oldValue);
                this._updateCriteriaHint();
            },

            /**
             * @inheritDoc
             */
            setValue: function(value) {
                value = this._formatRawValue(value);
                if (this._isNewValueUpdated(value)) {
                    var oldValue = this.value;
                    this.value = app.deepClone(value);
                    this._updateDOMValue();
                    this._onValueUpdated(this.value, oldValue);
                }

                return this;
            },

            /**
             * @inheritDoc
             */
            _onClickChoiceValue: function(e) {
                NumberFilter.prototype._onClickChoiceValue.apply(this, arguments);
                var parentDiv = $(e.currentTarget).closest('.metricfilter');
                if (_.contains(['empty', 'not empty'], $(e.currentTarget).attr('data-value'))) {
                    parentDiv.find('input[name="value"], .btn-group:eq(1)').hide();
                } else {
                    parentDiv.find('input[name="value"], .btn-group:eq(1)').show();
                }
            },

            /**
             * @inheritDoc
             */
            reset: function() {
                this.setValue(this.emptyValue);
                this.trigger('update');

                return this;
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 509 */
/* unknown exports provided */
/* all exports used */
/*!********************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter/filter/none-filter.js ***!
  \********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/translator */ 2), __webpack_require__(/*! oro/datafilter/abstract-filter */ 86)], __WEBPACK_AMD_DEFINE_RESULT__ = function($, _, __, AbstractFilter) {
    'use strict';

    /**
     * None filter: an empty filter implements 'null object' pattern
     *
     * Triggers events:
     *  - "disable" when filter is disabled
     *
     * @export  oro/datafilter/none-filter
     * @class   oro.datafilter.NoneFilter
     * @extends oro.datafilter.AbstractFilter
     */
    return AbstractFilter.extend({
        /**
         * Filter template
         *
         * @property
         */
        template: _.template(
            '<button type="button" class="btn filter-criteria-selector oro-drop-opener oro-dropdown-toggle">' +
                '<% if (showLabel) { %><%= label %>: <% } %>' +
                '<span class="filter-criteria-hint"><%= criteriaHint %></span>' +
                '<span class="caret"></span>' +
            '</button>' +
            '<% if (canDisable) { %><a href="<%= nullLink %>" class="AknFilterBox-disableFilter disable-filter"><i class="icon-remove hide-text"><%- _.__("Close") %></i></a><% } %>' +
            '<div class="filter-criteria dropdown-menu" />'
        ),

        /**
         * Template for filter criteria
         *
         * @property
         */
        popupCriteriaTemplate: _.template(
            '<div>' +
                '<%= popupHint %>' +
            '</div>'
        ),

        /**
         * @property {Boolean}
         */
        popupCriteriaShowed: false,

        /**
         * Selector to element of criteria hint
         *
         * @property {String}
         */
        criteriaHintSelector: '.filter-criteria-hint',

        /**
         * Selector to criteria popup container
         *
         * @property {String}
         */
        criteriaSelector: '.filter-criteria',

        /**
         * A value showed as filter's popup hint
         *
         * @property {String}
         */
        popupHint: 'Choose a value first',

        /**
         * View events
         *
         * @property {Object}
         */
        events: {
            'click .filter-criteria-selector': '_onClickCriteriaSelector',
            'click .filter-criteria .filter-criteria-hide': '_onClickCloseCriteria',
            'click .disable-filter': '_onClickDisableFilter'
        },

        /**
         * Initialize.
         *
         * @param {Object} options
         */
        initialize: function(options) {
            options = options || {};
            if (_.has(options, 'popupHint')) {
                this.popupHint = options.popupHint;
            }
            this.label = 'None';
            AbstractFilter.prototype.initialize.apply(this, arguments);
        },

        /**
         * Makes sure the criteria popup dialog is closed
         */
        ensurePopupCriteriaClosed: function () {
            if (this.popupCriteriaShowed) {
                this._hideCriteria();
            }
        },

        /**
         * Handle click on criteria selector
         *
         * @param {Event} e
         * @protected
         */
        _onClickCriteriaSelector: function(e) {
            e.stopPropagation();
            $('body').trigger('click');
            if (!this.popupCriteriaShowed) {
                this._showCriteria();
            } else {
                this._hideCriteria();
            }
        },

        /**
         * Handle click on criteria close button
         *
         * @private
         */
        _onClickCloseCriteria: function() {
            this._hideCriteria();
            this._updateDOMValue();
        },

        /**
         * Handle click on filter disabler
         *
         * @param {Event} e
         */
        _onClickDisableFilter: function(e) {
            e.preventDefault();
            this.disable();
        },

        /**
         * Handle click outside of criteria popup to hide it
         *
         * @param {Event} e
         * @protected
         */
        _onClickOutsideCriteria: function(e) {
            var elem = this.$(this.criteriaSelector);

            if (elem.get(0) !== e.target && !elem.has(e.target).length) {
                this._hideCriteria();
                e.stopPropagation();
            }
        },

        /**
         * Render filter view
         *
         * @return {*}
         */
        render: function () {
            this.$el.empty();
            this.$el.append(
                this.template({
                    label: this.label,
                    showLabel: this.showLabel,
                    criteriaHint:  this._getCriteriaHint(),
                    nullLink: this.nullLink,
                    canDisable: this.canDisable
                })
            );

            this._renderCriteria(this.$(this.criteriaSelector));
            this._clickOutsideCriteriaCallback = _.bind(function(e) {
                if (this.popupCriteriaShowed) {
                    this._onClickOutsideCriteria(e);
                }
            }, this);
            $('body').on('click', this._clickOutsideCriteriaCallback);

            return this;
        },

        /**
         * Render filter criteria popup
         *
         * @param {Object} el
         * @protected
         * @return {*}
         */
        _renderCriteria: function(el) {
            $(el).append(
                this.popupCriteriaTemplate({
                    popupHint: this._getPopupHint()
                })
            );
            return this;
        },

        /**
         * Unsubscribe from click on body event
         *
         * @return {*}
         */
        remove: function() {
            $('body').off('click', this._clickOutsideCriteriaCallback);
            AbstractFilter.prototype.remove.call(this);
            return this;
        },

        /**
         * Show criteria popup
         *
         * @protected
         */
        _showCriteria: function() {
            this.$(this.criteriaSelector).show();
            this._setButtonPressed(this.$(this.criteriaSelector), true);
            setTimeout(_.bind(function() {
                this.popupCriteriaShowed = true;
            }, this), 100);
        },

        /**
         * Hide criteria popup
         *
         * @protected
         */
        _hideCriteria: function() {
            this.$(this.criteriaSelector).hide();
            this._setButtonPressed(this.$(this.criteriaSelector), false);
            setTimeout(_.bind(function() {
                this.popupCriteriaShowed = false;
            }, this), 100);
        },

        /**
         * @inheritDoc
         */
        _writeDOMValue: function(value) {
            return this;
        },

        /**
         * @inheritDoc
         */
        _readDOMValue: function() {
            return {};
        },

        /**
         * Get popup hint value
         *
         * @return {String}
         * @protected
         */
        _getPopupHint: function() {
            return this.popupHint ? this.popupHint: this.popupHint;
        },

        /**
         * Get criteria hint value
         *
         * @return {String}
         * @protected
         */
        _getCriteriaHint: function() {
            return this.criteriaHint ? this.criteriaHint: this.placeholder;
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 510 */
/* unknown exports provided */
/* all exports used */
/*!*********************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter/filter/price-filter.js ***!
  \*********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/datafilter/number-filter */ 87), __webpack_require__(/*! oro/app */ 33)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, NumberFilter, app) {
        'use strict';

        /**
         * Price filter
         *
         * @author    Romain Monceau <romain@akeneo.com>
         * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
         * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         *
         * @export  pim/datafilter/price-filter
         * @class   pim.datafilter.PriceFilter
         * @extends oro.datafilter.NumberFilter
         */
        return NumberFilter.extend({
            /**
             * @inheritDoc
             */
            initialize: function() {
                NumberFilter.prototype.initialize.apply(this, arguments);

                this.on('disable', this._onDisable, this);

            },

            _onDisable: function() {
                this.$('.choicefilter button.dropdown-toggle').first().html(_.__('Action') + '<span class="AknActionButton-caret AknCaret"></span>');
                this.$('.choicefilter button.dropdown-toggle').last().html(_.__('Currency') + '<span class="AknActionButton-caret AknCaret"></span>');
            },

            /**
             * @inheritDoc
             */
            _renderCriteria: function (el) {
                $(el).append(this.popupCriteriaTemplate({
                    name: this.name,
                    choices: this.choices,
                    currencies: this.currencies
                }));

                return this;
            },

            /**
             * @inheritDoc
             */
            _writeDOMValue: function (value) {
                this._setInputValue(this.criteriaValueSelectors.value, value.value);
                this._setInputValue(this.criteriaValueSelectors.type, value.type);
                this._setInputValue(this.criteriaValueSelectors.currency, value.currency);

                return this;
            },

            /**
             * @inheritDoc
             */
            _readDOMValue: function () {
                return {
                    value: this._getInputValue(this.criteriaValueSelectors.value),
                    type: this._getInputValue(this.criteriaValueSelectors.type),
                    currency: this._getInputValue(this.criteriaValueSelectors.currency)
                };
            },

            /**
             * @inheritDoc
             */
            _getCriteriaHint: function () {
                var value = this._getDisplayValue();
                if (_.contains(['empty', 'not empty'], value.type) && value.currency) {
                    return this._getChoiceOption(value.type).label + ': ' + value.currency;
                }
                if (!value.value) {
                    return this.placeholder;
                } else {
                    var option = this._getChoiceOption(value.type);
                    return option.label + ' ' + value.value + ' ' + value.currency;
                }
            },

            /**
             * @inheritDoc
             */
            popupCriteriaTemplate: _.template(
                '<div class="AknFilterChoice currencyfilter choicefilter">' +
                    '<div class="AknFilterChoice-operator AknDropdown">' +
                        '<button class="AknActionButton AknActionButton--big AknActionButton--noRightBorder dropdown-toggle" data-toggle="dropdown">' +
                            '<%= _.__("Action") %>' +
                            '<span class="AknActionButton-caret AknCaret"></span>' +
                        '</button>' +
                        '<ul class="dropdown-menu">' +
                            '<% _.each(choices, function (option) { %>' +
                                '<li><a class="choice_value" href="#" data-value="<%= option.value %>" data-input-toggle="true"><%= option.label %></a></li>' +
                            '<% }); %>' +
                        '</ul>' +
                        '<input class="name_input" type="hidden" name="currency_type" value=""/>' +
                    '</div>' +
                    '<input class="AknTextField AknTextField--noRadius AknFilterChoice-field" type="text" name="value" value="">' +
                    '<div class="AknDropdown">' +
                        '<button class="AknActionButton AknActionButton--big AknActionButton--noRightBorder AknActionButton--noLeftBorder dropdown-toggle" data-toggle="dropdown">' +
                            '<%= _.__("Currency") %>' +
                            '<span class="AknActionButton-caret AknCaret"></span>' +
                        '</button>' +
                        '<ul class="dropdown-menu">' +
                            '<% _.each(currencies, function (currency) { %>' +
                                '<li><a class="choice_value" href="#" data-value="<%= currency %>"><%= currency %></a></li>' +
                            '<% }); %>' +
                        '</ul>' +
                        '<input class="name_input" type="hidden" name="currency_currency" value=""/>' +
                    '</div>' +
                    '<button class="AknFilterChoice-button AknButton AknButton--apply AknButton--noLeftRadius filter-update" type="button"><%= _.__("Update") %></button>' +
                '</div>'
            ),

            /**
             * Selectors for filter criteria elements
             *
             * @property {Object}
             */
            criteriaValueSelectors: {
                currency: 'input[name="currency_currency"]',
                type:     'input[name="currency_type"]',
                value:    'input[name="value"]'
            },

            /**
             * Empty value object
             *
             * @property {Object}
             */
            emptyValue: {
                currency: '',
                type:     '',
                value:    ''
            },

            /**
             * Check if all properties of the value have been specified or all are empty (for reseting filter)
             *
             * @param value
             * @return boolean
             */
            _isValueValid: function(value) {
                return (value.currency && value.type && !_.isUndefined(value.value)) ||
                       (!value.currency && !value.type && _.isUndefined(value.value)) ||
                       (_.contains(['empty', 'not empty'], value.type) && value.currency);
            },

            /**
             * @inheritDoc
             */
            _triggerUpdate: function(newValue, oldValue) {
                if (!app.isEqualsLoosely(newValue, oldValue)) {
                    this.trigger('update');
                }
            },

            /**
             * @inheritDoc
             */
            _onValueUpdated: function(newValue, oldValue) {
                var menu = this.$('.choicefilter .dropdown-menu');

                menu.find('li a').each(function() {
                    var item = $(this),
                        value = item.data('value');

                    if (item.parent().hasClass('active')) {
                        if (value == newValue.type || value == newValue.currency) {
                            item.parent().removeClass('active');
                        } else {
                        }
                    } else if (value == newValue.type || value == newValue.currency) {
                        item.parent().addClass('active');
                        item.closest('.AknDropdown').find('AknActionButton').html(item.html() + '<span class="AknActionButton-caret AknCaret"></span>');
                    }
                });
                if (_.contains(['empty', 'not empty'], newValue.type)) {
                    this.$(this.criteriaValueSelectors.value).hide();
                } else {
                    this.$(this.criteriaValueSelectors.value).show();
                }

                this._triggerUpdate(newValue, oldValue);
                this._updateCriteriaHint();
            },

            /**
             * @inheritDoc
             */
            setValue: function(value) {
                value = this._formatRawValue(value);
                if (this._isNewValueUpdated(value)) {
                    var oldValue = this.value;
                    this.value = app.deepClone(value);
                    this._updateDOMValue();
                    this._onValueUpdated(this.value, oldValue);
                }

                return this;
            },

            /**
             * @inheritDoc
             */
            _onClickChoiceValue: function(e) {
                NumberFilter.prototype._onClickChoiceValue.apply(this, arguments);
                if ($(e.currentTarget).attr('data-input-toggle')) {
                    var filterContainer = $(e.currentTarget).closest('.AknFilterChoice');
                    if (_.contains(['empty', 'not empty'], $(e.currentTarget).attr('data-value'))) {
                        filterContainer.find(this.criteriaValueSelectors.value).hide();
                    } else {
                        filterContainer.find(this.criteriaValueSelectors.value).show();
                    }
                }
            },

            /**
             * @inheritDoc
             */
            reset: function() {
                this.setValue(this.emptyValue);
                this.trigger('update');

                return this;
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 511 */
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter/filter/product_completeness-filter.js ***!
  \************************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/datafilter/select-filter */ 450)], __WEBPACK_AMD_DEFINE_RESULT__ = function (_, SelectFilter) {
        'use strict';

        /**
         * Scope filter
         *
         * @author    Nicolas Dupont <nicolas@akeneo.com>
         * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
         * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         *
         * @export  oro/datafilter/product_completeness-filter
         * @class   oro.datafilter.ProductCompletenessFilter
         * @extends oro.datafilter.SelectFilter
         */
        return SelectFilter.extend({});
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 512 */
/* unknown exports provided */
/* all exports used */
/*!*****************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter/filter/product_scope-filter.js ***!
  \*****************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/mediator */ 8), __webpack_require__(/*! oro/datafilter/select-filter */ 450), __webpack_require__(/*! pim/user-context */ 5), __webpack_require__(/*! pim/datagrid/state */ 34)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, mediator, SelectFilter, UserContext, DatagridState) {
        'use strict';

        /**
         * Scope filter
         *
         * @author    Romain Monceau <romain@akeneo.com>
         * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
         * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         *
         * @export  oro/datafilter/product_scope-filter
         * @class   oro.datafilter.ScopeFilter
         * @extends oro.datafilter.SelectFilter
         */
        return SelectFilter.extend({
            /**
             * @override
             * @property {Boolean}
             * @see Oro.Filter.SelectFilter
             */
            contextSearch: false,
            catalogScope: null,

            initialize: function() {
                SelectFilter.prototype.initialize.apply(this, arguments);
                this.catalogScope = UserContext.get('catalogScope');

                mediator.once('datagrid_filters:rendered', this.resetValue.bind(this));
                mediator.once('datagrid_filters:rendered', this.moveFilter.bind(this));

                mediator.bind('grid_load:complete', function(collection) {
                    $('#grid-' + collection.inputName).find('div.toolbar').show();
                });
            },

            /**
             * Move the filter to its proper position
             *
             * @param {Array} collection
             */
            moveFilter: function (collection) {
                var $grid = $('#grid-' + collection.inputName);

                if (0 === $grid.length) {
                    $grid = $('[data-type="datagrid"]:first');
                }
                this.$el.addClass('AknFilterBox-filter--inline').insertBefore($grid.find('.actions-panel'));

                var $filterChoices = $grid.find('#add-filter-select');
                $filterChoices.find('option[value="scope"]').remove();
                $filterChoices.multiselect('refresh');

                this.selectWidget.multiselect('refresh');
            },

            /**
             * Update the current filter value using the UserContext.
             */
            resetValue: function () {
                var scope = DatagridState.get('product-grid', 'scope');
                if (!scope) {
                    scope = this.catalogScope;
                }

                this.setValue({value: scope});
                UserContext.set('catalogScope', scope);
                this.selectWidget.multiselect('refresh');
            },

            /**
             * @inheritDoc
             */
            disable: function () {
                return this;
            },

            /**
             * @inheritDoc
             */
            hide: function () {
                return this;
            },

            /**
             * @inheritDoc
             */
            _onValueUpdated: function (newValue) {
                if ('' === newValue.value) {
                    return;
                }

                UserContext.set('catalogScope', newValue.value);

                return SelectFilter.prototype._onValueUpdated.apply(this, arguments);
            },

            /**
             * @inheritDoc
             *
             * Override to save the scope into the product grid state.
             *
             * We don't put this logic in the setValue method because we want this behavior only when the value
             * comes from a change of the select element, not from a view/url for example.
             */
            _onSelectChange: function() {
                SelectFilter.prototype._onSelectChange.apply(this, arguments);

                var value = this._formatRawValue(this._readDOMValue());
                DatagridState.set('product-grid', 'scope', value.value);
            },

            /**
             * Filter template
             *
             * @override
             * @property
             * @see Oro.Filter.SelectFilter
             */
            template: _.template(
                '<div class="AknActionButton filter-select filter-criteria-selector scope-filter">' +
                    '<i class="icon-eye-open" title="<%= label %>"></i>' +
                    '<select>' +
                        '<% _.each(options, function (option) { %>' +
                            '<option value="<%= option.value %>"><%= option.label %></option>' +
                        '<% }); %>' +
                    '</select>' +
                '</div>'
            )
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 513 */
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter/filter/select-row-filter.js ***!
  \**************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backbone */ 6), __webpack_require__(/*! oro/datafilter/select-filter */ 450)], __WEBPACK_AMD_DEFINE_RESULT__ = function(_, Backbone, SelectFilter) {
    'use strict';

    /**
     * Fetches information of rows selection
     * and implements filter by selected/Not selected rows
     *
     * @export  oro/datafilter/select-row-filter
     * @class   oro.datafilter.SelectRowFilter
     * @extends oro.datafilter.SelectFilter
     */
    return SelectFilter.extend({

        /**
         * Fetches raw format value on getting current value
         * in order to give always actual information about selected rows
         *
         * @return {Object}
         */
        getValue: function() {
            return this._formatRawValue(_.omit(this.value, 'in', 'out'));
        },

        /**
         * Converts a display value into raw format. Adds to value 'in' or 'out' property
         * with comma-separated string of ids, e.g. {'in': '4,35,23,65'} or {'out': '7,31,63,12'}
         *
         * @param {Object} value
         * @return {Object}
         * @protected
         */
        _formatRawValue: function(value) {
            // if a display value already contains raw information assume it's an initialization
            if (_.has(value, 'in') || _.has(value, 'out')) {
                this._initialSelection(value);
            }
            if (value.value !== '') {
                var ids = this._getSelection(),
                    scope;
                if (_.isArray(ids.selected)) {
                    scope = (ids.inset === Boolean(parseInt(value.value, 10)) ? 'in' : 'out');
                    value[scope] = ids.selected.join(',');
                }
            }
            return value;
        },

        /**
         * Converts a raw value into display format, opposite to _formatRawValue.
         * Removes extra properties of raw value representation.
         *
         * @param {Object} value
         * @return {Object}
         * @protected
         */
        _formatDisplayValue: function(value) {
            return _.omit(value, 'in', 'out');
        },

        /**
         * Fetches selection of grid rows
         * Triggers an event 'backgrid:getSelected' on collection to get selected rows.
         * oro.datagrid.SelectAllHeaderCell is listening to this event and
         * fills in a passes flat object with selection information
         *
         * @returns {Object}
         * @protected
         */
        _getSelection: function () {
            var selection = {};
            this.collection.trigger('backgrid:getSelected', selection);
            return _.defaults(selection, {inset : true, selected : []});
        },

        /**
         * Triggers selection events for models on grid's initial stage
         * (if display value has raw data, it's initial stage)
         *
         * @param {Object} value
         * @param {string} value.value "0" - not selected, "1" - selected
         * @param {string} value.in comma-separated ids
         * @param {string} value.out comma-separated ids
         * @protected
         */
        _initialSelection: function(value) {
            var checked = true;
            if (Boolean(parseInt(value.value, 10)) !== _.has(value, 'in')) {
                this.collection.trigger('backgrid:selectAll');
                checked = false;
            }
            _.each(
                _.values(_.pick(value, 'in', 'out'))[0].split(',') || [],
                _.partial(function(collection, id) {
                    var model = collection.get(id);
                    if (model instanceof Backbone.Model) {
                        model.trigger("backgrid:select", model, checked);
                    }
                }, this.collection)
            );
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 514 */
/* unknown exports provided */
/* all exports used */
/*!******************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter/filter/select2-choice-filter.js ***!
  \******************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/datafilter/text-filter */ 57),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! pim/template/datagrid/filter/select2-choice-filter */ 453),
        __webpack_require__(/*! pim/initselect2 */ 30),
        __webpack_require__(/*! pim/user-context */ 5),
        __webpack_require__(/*! jquery.select2 */ 11)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function($, _, TextFilter, Routing, template, initSelect2, UserContext) {
        'use strict';

        return TextFilter.extend({
            operatorChoices: [],
            choiceUrl: null,
            choiceUrlParams: {},
            emptyChoice: false,
            resultCache: {},
            resultsPerPage: 20,
            choices: [],
            popupCriteriaTemplate: _.template(template),

            events: {
                'click .operator_choice': '_onSelectOperator'
            },

            initialize: function(options) {
                _.extend(this.events, TextFilter.prototype.events);

                options = options || {};
                if (_.has(options, 'choiceUrl')) {
                    this.choiceUrl = options.choiceUrl;
                }
                if (_.has(options, 'choiceUrlParams')) {
                    this.choiceUrlParams = options.choiceUrlParams;
                }
                if (_.has(options, 'emptyChoice')) {
                    this.emptyChoice = options.emptyChoice;
                }

                if (_.isUndefined(this.emptyValue)) {
                    this.emptyValue = {
                        type: 'in',
                        value: ''
                    };
                }

                this.resultCache = {};

                TextFilter.prototype.initialize.apply(this, arguments);
            },

            _onSelectOperator: function(e) {
                $(e.currentTarget).parent().parent().find('li').removeClass('active');
                $(e.currentTarget).parent().addClass('active');
                var parentDiv = $(e.currentTarget).parent().parent().parent();

                if (_.contains(['empty', 'not empty'], $(e.currentTarget).attr('data-value'))) {
                    this._disableInput();
                } else {
                    this._enableInput();
                }
                parentDiv.find('button').html($(e.currentTarget).html() + '<span class="caret"></span>');
                e.preventDefault();
            },

            _enableInput: function() {
                initSelect2.init(this.$(this.criteriaValueSelectors.value), this._getSelect2Config());
                this.$(this.criteriaValueSelectors.value).show();
            },

            _disableInput: function() {
                this.$(this.criteriaValueSelectors.value).val('').select2('destroy');
                this.$(this.criteriaValueSelectors.value).hide();
            },

            _getSelect2Config: function() {
                var config = {
                    multiple: true,
                    width: '290px',
                    minimumInputLength: 0
                };

                if (this.choiceUrl) {
                    config.ajax = {
                        url: Routing.generate(this.choiceUrl, this.choiceUrlParams),
                        cache: true,
                        data: _.bind(function(term, page) {
                            return {
                                search: term,
                                options: {
                                    limit: this.resultsPerPage,
                                    page: page,
                                    locale: UserContext.get('catalogLocale')
                                }
                            };
                        }, this),
                        results: _.bind(function(data) {
                            this._cacheResults(data.results);
                            data.more = this.resultsPerPage === data.results.length;

                            return data;
                        }, this)
                    };
                } else {
                    config.data = _.map(this.choices, function(choice) {
                        return {
                            id: choice.value,
                            text: choice.label
                        };
                    });
                }

                return config;
            },

            _writeDOMValue: function(value) {
                this.$('li .operator_choice[data-value="' + value.type + '"]').trigger('click');
                var operator = this.$('li.active .operator_choice').data('value');
                if (_.contains(['empty', 'not empty'], operator)) {
                    this._setInputValue(this.criteriaValueSelectors.value, []);
                } else {
                    this._setInputValue(this.criteriaValueSelectors.value, value.value);
                }

                return this;
            },

            _readDOMValue: function() {
                var operator = this.emptyChoice ? this.$('li.active .operator_choice').data('value') : 'in';

                return {
                    value: _.contains(['empty', 'not empty'], operator) ? {} : this._getInputValue(this.criteriaValueSelectors.value),
                    type: operator
                };
            },

            _renderCriteria: function(el) {
                this.operatorChoices = {
                    'in':    _.__('pim.grid.choice_filter.label_in_list'),
                    'empty': _.__('pim.grid.choice_filter.label_empty'),
                    'not empty': _.__('pim.grid.choice_filter.label_not_empty')
                };

                $(el).append(
                    this.popupCriteriaTemplate({
                        emptyChoice:           this.emptyChoice,
                        selectedOperatorLabel: this.operatorChoices[this.emptyValue.type],
                        operatorChoices:       this.operatorChoices,
                        selectedOperator:      this.emptyValue.type
                    })
                );

                initSelect2.init(this.$(this.criteriaValueSelectors.value), this._getSelect2Config());
            },

            _onClickCriteriaSelector: function(e) {
                e.stopPropagation();
                $('body').trigger('click');
                if (!this.popupCriteriaShowed) {
                    this._showCriteria();

                    initSelect2.init(this.$(this.criteriaValueSelectors.value), this._getSelect2Config())
                        .select2('data', this._getCachedResults(this.getValue().value))
                        .select2('open');
                } else {
                    this._hideCriteria();
                }
            },

            _onClickCloseCriteria: function() {
                TextFilter.prototype._onClickCloseCriteria.apply(this, arguments);

                this.$(this.criteriaValueSelectors.value).select2('close');
            },

            _onClickOutsideCriteria: function(e) {
                var elem = this.$(this.criteriaSelector);

                if (e.target != $('body').get(0) && e.target !== elem.get(0) && !elem.has(e.target).length) {
                    this._hideCriteria();
                    this.setValue(this._formatRawValue(this._readDOMValue()));
                    e.stopPropagation();
                }
            },

            _onReadCriteriaInputKey: function(e) {
                if (e.which == 13) {
                    this.$(this.criteriaValueSelectors.value).select2('close');
                    this._hideCriteria();
                    this.setValue(this._formatRawValue(this._readDOMValue()));
                }
            },

            _cacheResults: function (results) {
                _.each(results, function (result) {
                    this.resultCache[result.id] = result.text;
                }, this);
            },

            _getCachedResults: function(ids) {
                var results = [],
                    missingResults = [];

                _.each(ids, function(id) {
                    if (_.isUndefined(this.resultCache[id])) {
                        if (_.isEmpty(this.choices)) {
                            missingResults.push(id);
                        } else {
                            var choice = _.findWhere(this.choices, { value: id });
                            if (_.isUndefined(choice)) {
                                missingResults.push(id);
                            } else {
                                results.push({ id: choice.value, text: choice.label });
                            }
                        }
                    } else {
                        results.push({ id: id, text: this.resultCache[id] });
                    }
                }, this);

                if (missingResults.length) {
                    var params = { options: { ids: missingResults } };

                    $.ajax({
                        url: Routing.generate(this.choiceUrl, this.choiceUrlParams) + '&' + $.param(params),
                        success: _.bind(function(data) {
                            this._cacheResults(data.results);
                            results = _.union(results, data.results);
                        }, this),
                        async: false
                    });
                }

                return results;
            },

            _getInputValue: function(input) {
                return this.$(input).select2('val');
            },

            _setInputValue: function(input, value) {
                this.$(input).select2('data', this._getCachedResults(value));

                return this;
            },

            _updateDOMValue: function() {
                return this._writeDOMValue(this.getValue());
            },

            _formatDisplayValue: function(value) {
                if (_.isEmpty(value.value)) {
                    return value;
                }

                return {
                    value: _.pluck(this._getCachedResults(value.value), 'text').join(', ')
                };
            },

            _getCriteriaHint: function() {
                var operator = this.$('li.active .operator_choice').data('value');
                if (_.contains(['empty', 'not empty'], operator)) {
                    return this.operatorChoices[operator];
                }

                var value = (arguments.length > 0) ? this._getDisplayValue(arguments[0]) : this._getDisplayValue();
                return !_.isEmpty(value.value) ? '"' + value.value + '"': this.placeholder;
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 515 */
/* unknown exports provided */
/* all exports used */
/*!***********************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter/filter/select2-rest-choice-filter.js ***!
  \***********************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/translator */ 2),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! oro/datafilter/text-filter */ 57),
        __webpack_require__(/*! pim/formatter/choices/base */ 45),
        __webpack_require__(/*! pim/user-context */ 5),
        __webpack_require__(/*! pim/template/datagrid/filter/select2-choice-filter */ 453),
        __webpack_require__(/*! pim/initselect2 */ 30),
        __webpack_require__(/*! jquery.select2 */ 11)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function($, _, __, Routing, TextFilter, ChoicesFormatter, UserContext, template, initSelect2) {
        return TextFilter.extend({
            operatorChoices: [],
            choiceUrl: null,
            choiceUrlParams: {},
            emptyChoice: false,
            resultsPerPage: 20,
            popupCriteriaTemplate: _.template(template),

            events: {
                'click .operator_choice': '_onSelectOperator'
            },

            initialize: function(options) {
                _.extend(this.events, TextFilter.prototype.events);

                if (!_.isUndefined(options)) {
                    _.extend(this, _.pick(options, 'choiceUrl', 'choiceUrlParams', 'emptyChoice'));
                }

                if (_.isUndefined(this.emptyValue)) {
                    this.emptyValue = {
                        type: 'in',
                        value: ''
                    };
                }

                TextFilter.prototype.initialize.apply(this, arguments);
            },

            _onSelectOperator: function(e) {
                $(e.currentTarget).parent().parent().find('li').removeClass('active');
                $(e.currentTarget).parent().addClass('active');
                var parentDiv = $(e.currentTarget).parent().parent().parent();

                if (_.contains(['empty', 'not empty'], $(e.currentTarget).attr('data-value'))) {
                    this._disableInput();
                } else {
                    this._enableInput();
                }
                parentDiv.find('button').html($(e.currentTarget).html() + '<span class="caret"></span>');
                e.preventDefault();
            },

            _enableInput: function() {
                this.$(this.criteriaValueSelectors.value).select2(this._getSelect2Config());
                this.$(this.criteriaValueSelectors.value).show();
            },

            _disableInput: function() {
                this.$(this.criteriaValueSelectors.value).val('').select2('destroy');
                this.$(this.criteriaValueSelectors.value).hide();
            },

            _getSelect2Config: function() {
                var config = {
                    multiple: true,
                    width: '290px',
                    minimumInputLength: 0
                };

                if (null !== this.choiceUrl) {
                    config.ajax = {
                        url: Routing.generate(this.choiceUrl, this.choiceUrlParams),
                        cache: true,
                        data: function(term, page) {
                                return {
                                    search: term,
                                    options: {
                                        limit: this.resultsPerPage,
                                        page: page,
                                        locale: UserContext.get('catalogLocale')
                                    }
                                };
                            }.bind(this),
                        results: function(data) {
                                data.results = ChoicesFormatter.format(data);
                                data.more    = this.resultsPerPage === data.results.length;

                                return data;
                            }.bind(this)
                    };
                }

                return config;
            },

            _writeDOMValue: function(value) {
                this.$('li .operator_choice[data-value="' + value.type + '"]').trigger('click');
                var operator = this.$('li.active .operator_choice').data('value');
                if (_.contains(['empty', 'not empty'], operator)) {
                    this._setInputValue(this.criteriaValueSelectors.value, []);
                } else {
                    this._setInputValue(this.criteriaValueSelectors.value, value.value);
                }

                return this;
            },

            _readDOMValue: function() {
                var operator = this.emptyChoice ? this.$('li.active .operator_choice').data('value') : 'in';

                return {
                    value: _.contains(['empty', 'not empty'], operator) ? {} : this._getInputValue(this.criteriaValueSelectors.value),
                    type: operator
                };
            },

            _renderCriteria: function(el) {
                this.operatorChoices = {
                    'in':        __('pim.grid.choice_filter.label_in_list'),
                    'empty':     __('pim.grid.choice_filter.label_empty'),
                    'not empty': __('pim.grid.choice_filter.label_not_empty')
                };

                $(el).append(
                    this.popupCriteriaTemplate({
                        emptyChoice:           this.emptyChoice,
                        selectedOperatorLabel: this.operatorChoices[this.emptyValue.type],
                        operatorChoices:       this.operatorChoices,
                        selectedOperator:      this.emptyValue.type
                    })
                );

                initSelect2.init(this.$(this.criteriaValueSelectors.value), this._getSelect2Config());
            },

            _onClickCriteriaSelector: function(e) {
                e.stopPropagation();
                $('body').trigger('click');
                if (!this.popupCriteriaShowed) {
                    this._showCriteria();
                    this.$(this.criteriaValueSelectors.value).select2('open');
                } else {
                    this._hideCriteria();
                }
            },

            _onClickCloseCriteria: function() {
                TextFilter.prototype._onClickCloseCriteria.apply(this, arguments);

                this.$(this.criteriaValueSelectors.value).select2('close');
            },

            _onClickOutsideCriteria: function(e) {
                var elem = this.$(this.criteriaSelector);

                if (e.target != $('body').get(0) && e.target !== elem.get(0) && !elem.has(e.target).length) {
                    this._hideCriteria();
                    this.setValue(this._formatRawValue(this._readDOMValue()));
                    e.stopPropagation();
                }
            },

            _onReadCriteriaInputKey: function(e) {
                if (e.which == 13) {
                    this.$(this.criteriaValueSelectors.value).select2('close');
                    this._hideCriteria();
                    this.setValue(this._formatRawValue(this._readDOMValue()));
                }
            },

            _getResults: function(identifiers) {
                var results = [];
                var params  = {options: {identifiers: identifiers}};

                $.ajax({
                    url: Routing.generate(this.choiceUrl, this.choiceUrlParams) + '?' + $.param(params),
                    success: function(data) {
                        results = ChoicesFormatter.format(data);
                    },
                    async: false
                });

                return results;
            },

            _getInputValue: function(input) {
                return this.$(input).select2('val');
            },

            _setInputValue: function(input, value) {
                this.$(input).select2('data', this._getResults(value));

                return this;
            },

            _updateDOMValue: function() {
                var currentValue = this.getValue();
                var data         = this.$(this.criteriaValueSelectors.value).select2('data');
                if (0 === _.difference(currentValue.value, _.pluck(data, 'id')).length) {
                    return;
                }

                return this._writeDOMValue(currentValue);
            },

            _formatDisplayValue: function(value) {
                if (_.isEmpty(value.value)) {
                    return value;
                }

                return {
                    value: _.pluck(
                        this.$(this.criteriaValueSelectors.value).select2('data'),
                        'text'
                    ).join(', ')
                };
            },

            _getCriteriaHint: function() {
                var operator = this.$('li.active .operator_choice').data('value');
                if (_.contains(['empty', 'not empty'], operator)) {
                    return this.operatorChoices[operator];
                }

                var value = (arguments.length > 0) ? this._getDisplayValue(arguments[0]) : this._getDisplayValue();

                return !_.isEmpty(value.value) ? '"' + value.value + '"': this.placeholder;
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 516 */
/* unknown exports provided */
/* all exports used */
/*!******************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datafilter/formatter/abstract-formatter.js ***!
  \******************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_RESULT__ = function() {
    'use strict';

    /**
     * Just a convenient class for interested parties to subclass.
     *
     * The default Cell classes don't require the formatter to be a subclass of
     * Formatter as long as the fromRaw(rawData) and toRaw(formattedData) methods
     * are defined.
     *
     * @abstract
     * @export  oro/datafilter/abstract-formatter
     * @class   oro.datafilter.AbstractFormatter
     */
    var AbstractFormatter = function() {};

    AbstractFormatter.prototype = {
        /**
         * Takes a raw value from a model and returns a formatted string for display.
         *
         * @memberOf oro.datafilter.AbstractFormatter
         * @param {*} rawData
         * @return {string}
         */
        fromRaw: function(rawData) {
            return rawData;
        },

        /**
         * Takes a formatted string, usually from user input, and returns a
         * appropriately typed value for persistence in the model.
         *
         * If the user input is invalid or unable to be converted to a raw value
         * suitable for persistence in the model, toRaw must return `undefined`.
         *
         * @memberOf oro.datafilter.AbstractFormatter
         * @param {string} formattedData
         * @return {*|undefined}
         */
        toRaw: function(formattedData) {
            return formattedData;
        }
    };

    return AbstractFormatter;
}.call(exports, __webpack_require__, exports, module),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 517 */
/* unknown exports provided */
/* all exports used */
/*!*******************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datagrid/action/configure-columns-action.js ***!
  \*******************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/translator */ 2),
        __webpack_require__(/*! backbone */ 6),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! oro/loading-mask */ 15),
        __webpack_require__(/*! pim/datagrid/state */ 34),
        __webpack_require__(/*! pim/common/column-list-view */ 489),
        __webpack_require__(/*! bootstrap-modal */ 38),
        __webpack_require__(/*! jquery-ui */ 59)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function(
        $,
        _,
        __,
        Backbone,
        Routing,
        LoadingMask,
        DatagridState,
        ColumnListView
    ) {
        var Column = Backbone.Model.extend({
            defaults: {
                removable: true,
                label: '',
                displayed: false,
                group: __('system_filter_group')
            }
        });

        var ColumnList = Backbone.Collection.extend({ model: Column });

        /**
         * Configure columns action
         *
         * @export  pim/datagrid/configure-columns-action
         * @class   pim.datagrid.ConfigureColumnsAction
         * @extends Backbone.View
         */
        var ConfigureColumnsAction = Backbone.View.extend({

            locale: null,

            label: _.__('pim_datagrid.column_configurator.label'),

            icon: 'th',

            target: '.AknGridToolbar .actions-panel',

            template: _.template(
                '<div class="AknGridToolbar-actionButton">' +
                    '<a href="javascript:void(0);" class="AknActionButton" title="<%= label %>" id="configure-columns">' +
                        '<i class="icon-<%= icon %>"></i>' +
                        '<%= label %>' +
                    '</a>' +
                '</div>'
            ),

            configuratorTemplate: _.template(
                '<div id="column-configurator" class="AknColumnConfigurator"></div>'
            ),

            initialize: function (options) {
                if (_.has(options, 'label')) {
                    this.label = _.__(options.label);
                }
                if (_.has(options, 'icon')) {
                    this.icon = options.icon;
                }

                if (!options.$gridContainer) {
                    throw new Error('Grid selector is not specified');
                }

                this.$gridContainer = options.$gridContainer;
                this.gridName = options.gridName;
                this.locale = decodeURIComponent(options.url).split('dataLocale]=').pop();

                Backbone.View.prototype.initialize.apply(this, arguments);

                this.render();
            },

            render: function() {
                this.$gridContainer
                    .find(this.target)
                    .append(
                        this.template({
                            icon: this.icon,
                            label: this.label
                        })
                    );
                this.subscribe();
            },

            subscribe: function() {
                $('#configure-columns').one('click', this.execute.bind(this));
            },

            execute: function(e) {
                e.preventDefault();
                var url = Routing.generate('pim_datagrid_view_list_available_columns', {
                    alias: this.gridName,
                    dataLocale: this.locale
                });

                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo($('#container'));
                loadingMask.show();


                $.get(url, _.bind(function (columns) {
                    var displayedCodes = DatagridState.get(this.gridName, 'columns');

                    if (displayedCodes) {
                        displayedCodes = displayedCodes.split(',');
                    } else {
                        displayedCodes = _.pluck(this.$gridContainer.data('metadata').columns, 'name');
                    }

                    displayedCodes = _.map(displayedCodes, function(displayedCode, index) {
                        return {
                            code: displayedCode,
                            position: index
                        }
                    });

                    var columnList = new ColumnList();
                    _.each(columns, function(column) {
                        var displayedCode = _.findWhere(displayedCodes, {code: column.code});
                        if (!_.isUndefined(displayedCode)) {
                            column.displayed = true;
                            column.position = displayedCode.position;
                        }

                        columnList.add(column);
                    });

                    var columnListView = new ColumnListView({collection: columnList});

                    var modal = new Backbone.BootstrapModal({
                        className: 'modal modal-large column-configurator-modal',
                        modalOptions: {
                            backdrop: 'static',
                            keyboard: false
                        },
                        allowCancel: true,
                        okCloses: false,
                        cancelText: _.__('pim_datagrid.column_configurator.cancel'),
                        title: _.__('pim_datagrid.column_configurator.title'),
                        content: this.configuratorTemplate(),
                        okText: _.__('pim_datagrid.column_configurator.apply')
                    });

                    loadingMask.hide();
                    loadingMask.$el.remove();

                    modal.open();
                    columnListView.setElement('#column-configurator').render();

                    modal.on('cancel', this.subscribe.bind(this));
                    modal.on('ok', _.bind(function() {
                        var values = columnListView.getDisplayed();
                        if (!values.length) {
                            return;
                        } else {
                            DatagridState.set(this.gridName, 'columns', values.join(','));
                            modal.close();
                            var url = window.location.hash;
                            Backbone.history.fragment = new Date().getTime();
                            Backbone.history.navigate(url, true);
                        }
                    }, this));
                }, this));
            }
        });

        ConfigureColumnsAction.init = function ($gridContainer, gridName) {
            var metadata = $gridContainer.data('metadata');
            var options = metadata.options || {};
            new ConfigureColumnsAction(
                _.extend({ $gridContainer: $gridContainer, gridName: gridName, url: options.url }, options.configureColumns)
            );
        };

        return ConfigureColumnsAction;
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 518 */
/* unknown exports provided */
/* all exports used */
/*!*****************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datagrid/cell/boolean-cell.js ***!
  \*****************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backgrid */ 20)], __WEBPACK_AMD_DEFINE_RESULT__ = function($, _, Backgrid) {
    'use strict';

    Backgrid = Backgrid.Backgrid;

    /**
     * Boolean column cell. Added missing behaviour.
     *
     * @export  oro/datagrid/boolean-cell
     * @class   oro.datagrid.BooleanCell
     * @extends Backgrid.BooleanCell
     */
    return Backgrid.BooleanCell.extend({
        /** @property {Boolean} */
        listenRowClick: true,

        /**
         * @inheritDoc
         */
        render: function() {
            Backgrid.BooleanCell.prototype.render.apply(this, arguments);
            this.$input = this.$el.find('input');
            if (!this.column.get('editable')) {
                this.$input.attr('disabled', 'disabled');
            }
            return this;
        },

        /**
         * @inheritDoc
         */
        enterEditMode: function(e) {
            Backgrid.BooleanCell.prototype.enterEditMode.apply(this, arguments);
            if (this.column.get('editable')) {
                var $editor = this.currentEditor.$el;
                $editor.prop('checked', !$editor.prop('checked')).change();
            }
        },

        /**
         * @param {Backgrid.Row} row
         * @param {Event} e
         */
        onRowClicked: function(row, e) {
            if (!this.$input.is(e.target) && !this.$el.is(e.target) && !this.$el.has(e.target).length){
                this.enterEditMode(e);
            }
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 519 */
/* unknown exports provided */
/* all exports used */
/*!*****************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datagrid/cell/integer-cell.js ***!
  \*****************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backgrid */ 20)], __WEBPACK_AMD_DEFINE_RESULT__ = function(_, Backgrid) {
        'use strict';

        Backgrid = Backgrid.Backgrid;

        /**
         * Integer column cell.
         *
         * @export  oro/datagrid/integer-cell
         * @class   oro.datagrid.NumberCell
         * @extends Backgrid.NumberCell
         */
        return Backgrid.NumberCell.extend({
            /** @property {String} */
            style: 'decimal',

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.decimals = 0;

                Backgrid.NumberCell.prototype.initialize.apply(this, arguments);
            },

            /**
             * @inheritDoc
             */
            enterEditMode: function (e) {
                if (this.column.get("editable")) {
                    e.stopPropagation();
                }
                return Backgrid.NumberCell.prototype.enterEditMode.apply(this, arguments);
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 520 */
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datagrid/cell/number-cell.js ***!
  \****************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backgrid */ 20)], __WEBPACK_AMD_DEFINE_RESULT__ = function(_, Backgrid) {
        'use strict';

        Backgrid = Backgrid.Backgrid;
        
        /**
         * Number column cell.
         *
         * @export  oro/datagrid/number-cell
         * @class   oro.datagrid.NumberCell
         * @extends Backgrid.NumberCell
         */
        return Backgrid.NumberCell.extend({
            /** @property {String} */
            style: 'decimal',

            /**
             * @inheritDoc
             */
            enterEditMode: function (e) {
                if (this.column.get("editable")) {
                    e.stopPropagation();
                }
                return Backgrid.NumberCell.prototype.enterEditMode.apply(this, arguments);
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 521 */
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datagrid/cell/select-cell.js ***!
  \****************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backgrid */ 20)], __WEBPACK_AMD_DEFINE_RESULT__ = function(_, Backgrid) {
    'use strict';

    Backgrid = Backgrid.Backgrid;
    
    /**
     * Select column cell. Added missing behaviour.
     *
     * @export  oro/datagrid/select-cell
     * @class   oro.datagrid.SelectCell
     * @extends Backgrid.SelectCell
     */
    return Backgrid.SelectCell.extend({
        /**
         * @inheritDoc
         */
        initialize: function (options) {
            if (this.choices) {
                this.optionValues = [];
                _.each(this.choices, function(value, key) {
                    this.optionValues.push([value, key]);
                }, this);
            }
            Backgrid.SelectCell.prototype.initialize.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        enterEditMode: function (e) {
            if (this.column.get("editable")) {
                e.stopPropagation();
            }
            return Backgrid.StringCell.prototype.enterEditMode.apply(this, arguments);
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 522 */
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datagrid/listener/callback-listener.js ***!
  \**************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(_) {var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! oro/datagrid/abstract-listener */ 451)], __WEBPACK_AMD_DEFINE_RESULT__ = function(AbstractListener) {
    'use strict';

    /**
     * Listener with custom callback to execute
     *
     * @export  oro/datagrid/callback-listener
     * @class   oro.datagrid.CallbackListener
     * @extends oro.datagrid.AbstractListener
     */
    return AbstractListener.extend({
        /** @param {Call} */
        processCallback: null,

        /**
         * Initialize listener object
         *
         * @param {Object} options
         */
        initialize: function(options) {
            if (!_.has(options, 'processCallback')) {
                throw new Error('Process callback is not specified');
            }

            this.processCallback = options.processCallback;

            AbstractListener.prototype.initialize.apply(this, arguments);
        },

        /**
         * Execute callback
         *
         * @param {*} value Value of model property with name of this.dataField
         * @param {Backbone.Model} model
         * @protected
         */
        _processValue: function(value, model) {
            this.processCallback(value, model, this);
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));

/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(/*! underscore */ 0)))

/***/ }),
/* 523 */
/* unknown exports provided */
/* all exports used */
/*!*****************************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datagrid/listener/column-form-listener.js ***!
  \*****************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/mediator */ 8), __webpack_require__(/*! oro/datagrid/column-form-listener */ 488)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, mediator, OroColumnFormListener) {
        'use strict';

        /**
         * Column form listener based on oro implementation that allows
         * changing of field selectors dynamically using mediator
         */
        var ColumnFormListener = OroColumnFormListener.extend({
            $checkbox: null,
            initialize: function () {
                OroColumnFormListener.prototype.initialize.apply(this, arguments);

                this.$checkbox = $('<input type="checkbox">').css('margin', 0);

                mediator.on('datagrid_collection_set_after', function (collection, $grid) {
                    if (collection.inputName === this.gridName) {
                        this.$el = $grid.find('table.grid thead th:not([style])').first();

                        this.$el.empty().html(this.$checkbox);

                        this.setStateFromCollection(collection);

                        this.$checkbox.on('click', _.bind(function () {
                            var state = this.$checkbox.is(':checked');
                            _.each(collection.models, function (model) {
                                model.set(this.columnName, state);
                            }, this);
                        }, this));
                    }
                }, this);

                mediator.on('grid_load:complete', function (collection) {
                    if (collection.inputName === this.gridName) {
                        this.setStateFromCollection(collection);
                    }
                }, this);

                mediator.bind('column_form_listener:set_selectors:' + this.gridName, function (selectors) {
                    this._clearState();
                    this.selectors = selectors;
                    this._restoreState();
                    this._synchronizeState();
                }, this);

                mediator.trigger('column_form_listener:initialized', this.gridName);
            },

            _explode: function (string) {
                if (!string) {
                    return [];
                }
                return _.map(string.split(','), function (val) {
                    return val ? String(val).trim() : null;
                });
            },

            setStateFromCollection: function (collection) {
                var checked = true;
                _.each(collection.models, function (model) {
                    if (checked) {
                        checked = model.get(this.columnName);
                    }
                }, this);
                this.$checkbox.prop('checked', checked);
            },

            _processValue: function (id, model) {
                OroColumnFormListener.prototype._processValue.apply(this, arguments);

                var selectEvent = model.get(this.columnName) ? 'selectModel' : 'unselectModel';
                mediator.trigger('datagrid:' + selectEvent + ':' + this.gridName, model);
            }
        });

        return {
            init: function ($gridContainer, gridName) {
                var metadata = $gridContainer.data('metadata');
                var options = metadata.options || {};
                if (options.columnListener) {
                    options.columnListener.selectors = options.columnListener.selectors || {};
                    new ColumnFormListener(_.extend({ $gridContainer: $gridContainer, gridName: gridName }, options.columnListener));
                }
            }
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 524 */
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datagrid/state-listener.js ***!
  \**************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/mediator */ 8), __webpack_require__(/*! oro/datagrid/abstract-listener */ 451), __webpack_require__(/*! pim/datagrid/state */ 34)], __WEBPACK_AMD_DEFINE_RESULT__ = function(_, mediator, AbstractListener, DatagridState) {
        'use strict';

        /**
         * Datagrid state listener
         */
        var StateListener = AbstractListener.extend({
            gridName: null,
            $gridContainer: null,

            initialize: function (options) {
                if (!_.has(options, 'gridName')) {
                    throw new Error('Grid name not specified');
                }
                if (!_.has(options, '$gridContainer')) {
                    throw new Error('Grid container not specified');
                }

                this.gridName       = options.gridName;
                this.$gridContainer = options.$gridContainer;

                this.subscribe();
            },

            subscribe: function () {
                mediator.once('datagrid_collection_set_after', this.afterCollectionSet, this);
                mediator.on('grid_load:complete', this.saveGridState, this);

                this.$gridContainer.on('preExecute:reset:' + this.gridName, this.onGridReset.bind(this));

                mediator.once('hash_navigation_request:start', this.unsubscribe, this);
            },

            unsubscribe: function () {
                mediator.off('grid_load:complete', this.saveGridState, this);
            },

            afterCollectionSet: function () {
                mediator.once(
                    'datagrid_filters:rendered',
                    function (collection) {
                        collection.trigger('updateState', collection);

                        // We have to use a timeout here because the toolbar is hidden right after triggering this event
                        setTimeout(_.bind(function() {
                            this.$gridContainer.find('div.toolbar, div.filter-box').show();
                        }, this), 20);
                    }, this
                );
            },

            saveGridState: function (collection) {
                if (collection.inputName === this.gridName) {
                    var $filterBox = this.$gridContainer.find('.filter-box');
                    if ($filterBox.length && !$filterBox.is(':visible')) {
                        $filterBox.show();
                    }

                    var encodedStateData = collection.encodeStateData(collection.state);
                    DatagridState.set(this.gridName, 'filters', encodedStateData);
                }
            },

            onGridReset: function (e, action) {
                action.collection.initialState.filters = {};
            }
        });

        StateListener.init = function ($gridContainer, gridName) {
            new StateListener({ $gridContainer: $gridContainer, gridName: gridName });
        };

        return StateListener;
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 525 */
/* unknown exports provided */
/* all exports used */
/*!********************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/datagrid/widget/export-widget.js ***!
  \********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backbone */ 6), __webpack_require__(/*! oro/messenger */ 12)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, Backbone, messenger) {
        'use strict';

        return Backbone.View.extend({

            action: null,

            initialize: function (action) {
                this.action = action;
            },

            run: function () {
                $.get(this.action.getLinkWithParameters())
                    .done(function () {
                        messenger.notificationFlashMessage(
                            'success',
                            _.__('pim.grid.mass_action.quick_export.launched')
                        );
                    })
                    .error(function (jqXHR) {
                        messenger.notificationFlashMessage(
                            'error',
                            _.__(jqXHR.responseText)
                        );
                    });
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 526 */
/* unknown exports provided */
/* all exports used */
/*!********************************************************************************************!*\
  !*** ./src/Pim/Bundle/DataGridBundle/Resources/public/js/fetcher/datagrid-view-fetcher.js ***!
  \********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/**
 * Datagrid View Fetcher.
 * We override the default fetcher to add additional methods
 * to fetch default columns & default user datagrid view.
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! pim/base-fetcher */ 56)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        Routing,
        BaseFetcher
    ) {
        return BaseFetcher.extend({
            /**
             * {@inheritdoc}
             */
            initialize: function (options) {
                BaseFetcher.prototype.initialize.apply(this, arguments);
            },

            /**
             * Fetch default columns for grid with given alias
             *
             * @param {string} alias
             *
             * @return Promise
             */
            defaultColumns: function (alias) {
                return $.getJSON(Routing.generate(this.options.urls.columns, { alias: alias }));
            },

            /**
             * Fetch default datagrid view for given alias of the current user
             *
             * @param {string} alias
             *
             * @return Promise
             */
            defaultUserView: function (alias) {
                return $.getJSON(Routing.generate(this.options.urls.userDefaultView, { alias: alias }));
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 527 */
/* unknown exports provided */
/* all exports used */
/*!********************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/controller/registry.js ***!
  \********************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! controllers */ 429)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, module) {
        var controllers       = controllers || {}
        var defaultController = module.config().defaultController;

        return {
            /**
             * Get the controller for the given name
             *
             * @param {String} name
             *
             * @return {Promise}
             */
            get: function (name) {
                var deferred = $.Deferred();

                var controller = controllers[name] || defaultController;
                __webpack_require__.e/* require */(2).then(function() { var __WEBPACK_AMD_REQUIRE_ARRAY__ = [__webpack_require__(/*! . */ 448)(controller.module)]; (function (Controller) {
                    controller.class = Controller;

                    deferred.resolve(controller);
                }.apply(null, __WEBPACK_AMD_REQUIRE_ARRAY__));}).catch(__webpack_require__.oe);

                return deferred.promise();
            }
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 528 */
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/fetcher/attribute-fetcher.js ***!
  \**************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! pim/base-fetcher */ 56), __webpack_require__(/*! routing */ 7)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, BaseFetcher, Routing) {
    return BaseFetcher.extend({
        identifierPromise: null,
        fetchByTypesPromises: [],

        /**
         * Return the identifier attribute
         *
         * @return {Promise}
         */
        getIdentifierAttribute: function () {
            if (null === this.identifierPromise) {
                this.identifierPromise = $.Deferred();

                return this.fetchByTypes([this.options.identifier_type])
                    .then(function (attributes) {
                        if (attributes.length > 0) {
                            this.identifierPromise.resolve(attributes[0]).promise();

                            return this.identifierPromise;
                        }

                        return this.identifierPromise
                            .reject()
                            .promise();
                    }.bind(this));
            }

            return this.identifierPromise;
        },

        /**
         * Fetch attributes by types
         *
         * @param {Array} attributeTypes
         *
         * @return {Promise}
         */
        fetchByTypes: function (attributeTypes) {
            var cacheKey = attributeTypes.sort().join('');

            if (!_.has(this.fetchByTypesPromises, cacheKey)) {
                this.fetchByTypesPromises[cacheKey] = this.getJSON(
                    this.options.urls.list,
                    {types: attributeTypes.join(',')}
                )
                .then(_.identity)
                .promise();
            }

            return this.fetchByTypesPromises[cacheKey];
        },

        /**
         * This method overrides the base method, to send a POST query instead of a GET query, because the request
         * URI can be too long.
         * TODO Should be deleted to set it back to GET.
         *
         * {@inheritdoc}
         */
        getJSON: function (url, parameters) {
            return $.post(Routing.generate(url), parameters, null, 'json');
        },

        /**
         * {@inheritdoc}
         */
        clear: function () {
            BaseFetcher.prototype.clear.apply(this, arguments);

            this.identifierPromise = null;
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 529 */
/* unknown exports provided */
/* all exports used */
/*!********************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/fetcher/attribute-group-fetcher.js ***!
  \********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/**
 * Attribute group fetcher
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
    __webpack_require__(/*! jquery */ 1),
    __webpack_require__(/*! pim/base-fetcher */ 56),
    __webpack_require__(/*! routing */ 7)
], __WEBPACK_AMD_DEFINE_RESULT__ = function (
    $,
    BaseFetcher,
    Routing
) {
    return BaseFetcher.extend({
        /**
         * Overrides base method, to send query using POST instead GET,
         * because the request URI can be too long.
         * TODO Should be deleted to set it back to GET.
         * SEE attribute fetcher
         *
         * {@inheritdoc}
         */
        getJSON: function (url, parameters) {
            return $.post(Routing.generate(url), parameters, null, 'json');
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 530 */
/* unknown exports provided */
/* all exports used */
/*!*****************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/fetcher/completeness-fetcher.js ***!
  \*****************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! routing */ 7), __webpack_require__(/*! pim/base-fetcher */ 56)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, Routing, BaseFetcher) {
    return BaseFetcher.extend({
        /**
         * Fetch completenesses for the given product id
         *
         * @param Integer productId
         *
         * @return Promise
         */
        fetchForProduct: function (productId, family) {
            if (!(productId in this.entityPromises)) {
                this.entityPromises[productId] = $.getJSON(
                    Routing.generate(this.options.urls.get, { id: productId })
                ).then(function (completenesses) {
                    return {completenesses: completenesses, family: family};
                });

                return this.entityPromises[productId];
            } else {
                return this.entityPromises[productId].then(function (completeness) {
                    return (family !== completeness.family) ?
                        {completenesses: {}, family: family} :
                        this.entityPromises[productId];
                }.bind(this));
            }

        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 531 */
/* unknown exports provided */
/* all exports used */
/*!***********************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/fetcher/locale-fetcher.js ***!
  \***********************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! pim/base-fetcher */ 56),
        __webpack_require__(/*! routing */ 7)

    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        _,
        BaseFetcher,
        Routing
    ) {
        return BaseFetcher.extend({
            entityActivatedListPromise: null,
            /**
             * @param {Object} options
             */
            initialize: function (options) {
                this.options = options || {};
            },

            /**
             * Fetch an element based on its identifier
             *
             * @param {string} identifier
             *
             * @return {Promise}
             */
            fetchActivated: function () {
                if (!this.entityActivatedListPromise) {
                    if (!_.has(this.options.urls, 'list')) {
                        return $.Deferred().reject().promise();
                    }

                    this.entityActivatedListPromise = $.getJSON(
                        Routing.generate(this.options.urls.list),
                        {activated: true}
                    ).then(_.identity).promise();
                }

                return this.entityActivatedListPromise;
            },

            /**
             * {inheritdoc}
             */
            clear: function () {
                this.entityActivatedListPromise = null;

                BaseFetcher.prototype.clear.apply(this, arguments);
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 532 */
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/fetcher/product-fetcher.js ***!
  \************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! backbone */ 6),
        __webpack_require__(/*! module-config */ 10),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! oro/mediator */ 8),
        __webpack_require__(/*! pim/cache-invalidator */ 88),
        __webpack_require__(/*! pim/product-manager */ 58)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        Backbone,
        module,
        Routing,
        mediator,
        CacheInvalidator,
        ProductManager
    ) {
        return Backbone.Model.extend({
            /**
             * @param {Object} options
             */
            initialize: function (options) {
                this.options = options || {};
            },

            /**
             * Fetch an element based on its identifier
             *
             * @param {string} identifier
             *
             * @return {Promise}
             */
            fetch: function (identifier) {
                return $.getJSON(Routing.generate(this.options.urls.get, { id: identifier }))
                    .then(function (product) {
                        var cacheInvalidator = new CacheInvalidator();
                        cacheInvalidator.checkStructureVersion(product);

                        return ProductManager.generateMissing(product);
                    }.bind(this))
                    .then(function (product) {
                        mediator.trigger('pim_enrich:form:product:post_fetch', product);

                        return product;
                    })
                    .promise();
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 533 */
/* unknown exports provided */
/* all exports used */
/*!******************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/fetcher/variant-group-fetcher.js ***!
  \******************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! pim/base-fetcher */ 56),
        __webpack_require__(/*! module-config */ 10),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! oro/mediator */ 8),
        __webpack_require__(/*! pim/cache-invalidator */ 88),
        __webpack_require__(/*! pim/product-manager */ 58)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        BaseFetcher,
        module,
        Routing,
        mediator,
        CacheInvalidator,
        ProductManager
    ) {
        return BaseFetcher.extend({
            /**
             * @param {Object} options
             */
            initialize: function (options) {
                this.options = options || {};
            },

            /**
             * Fetch an element based on its identifier
             *
             * @param {string} identifier
             * @param {Object} options
             *
             * @return {Promise}
             */
            fetch: function (identifier, options) {
                options = options || {};

                options.code = identifier;
                var promise = BaseFetcher.prototype.fetch.apply(this, [identifier, options]);

                return promise
                    .then(function (variantGroup) {
                        var cacheInvalidator = new CacheInvalidator();
                        cacheInvalidator.checkStructureVersion(variantGroup);

                        return variantGroup;
                    })
                    .then(ProductManager.generateMissing.bind(ProductManager))
                    .then(function (variantGroup) {
                        mediator.trigger('pim_enrich:form:variant_group:post_fetch', variantGroup);

                        return variantGroup;
                    });
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 534 */
/* unknown exports provided */
/* all exports used */
/*!*********************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/common/index/confirm-button.js ***!
  \*********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/**
 * Confirm button extension
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/translator */ 2),
        __webpack_require__(/*! pim/form */ 3),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! pim/template/form/index/confirm-button */ 470)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        _,
        __,
        BaseForm,
        Routing,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config || {};

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    buttonClass: this.config.buttonClass,
                    buttonLabel: __(this.config.buttonLabel),
                    title: __(this.config.title),
                    message: __(this.config.message),
                    url: Routing.generate(this.config.url),
                    redirectUrl: Routing.generate(this.config.redirectUrl),
                    errorMessage: __(this.config.errorMessage),
                    successMessage: __(this.config.successMessage),
                    iconName: this.config.iconName
                }));

                this.renderExtensions();

                return this;
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 535 */
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/form/form-modal.js ***!
  \****************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

/**
 * This service instantiates a modal with a custom form.
 * The custom form must be passed in as a service.
 *
 * A deferred object is returned on modal opening:
 * - Success: Resolved when the user clicks on the OK button, the callback contains the form data.
 * - Fail: the user canceled the modal form.
 *
 * Typical use example
 * ===================
 *
 * var onDataSubmission = function (form) {
 *     var deferred = $.Deferred();
 *     var formData = form.getFormData();
 *
 *     // validate your data...
 *
 *     if (validData) {
 *          deferred.resolve();
 *     } else {
 *          deferred.reject();
 *          // display errors on form, or whatever
 *     }
 *
 *     return deferred;
 * }
 *
 * var myFormModal = new FormModal('pim-product-edit-form', onDataSubmission);
 *
 * myFormModal.open()
 *      .then(function(myFormData) {
 *          // on success
 *      })
 *      .fail(function() {
 *          // user clicked Cancel button
 *      });
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! backbone */ 6),
        __webpack_require__(/*! oro/mediator */ 8),
        __webpack_require__(/*! pim/form-builder */ 16)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        _,
        Backbone,
        mediator,
        FormBuilder
    ) {
        return Backbone.View.extend({
            /**
             * The form name the modal should display.
             * This service must be registered with RequireJS, eg: 'pim-product-edit-form'
             */
            formName: '',

            /**
             * Instance of the UI modal element.
             */
            modal: null,

            /**
             * Callback triggered on form submission.
             * This callback should return a promise, resolved when data validation check is OK.
             */
            submitCallback: null,

            /**
             * UI modal parameters
             */
            modalParameters: {
                allowCancel: true,
                okCloses:    false,
                content:     '',
                title:       '[modal_title]',
                okText:      '[ok]',
                cancelText:  '[cancel]',
                modalOptions: {
                    backdrop: 'static',
                    keyboard: false
                }
            },

            /**
             * @param {string}   formName
             * @param {function} submitCallback
             * @param {Object}   modalParameters
             */
            initialize: function (formName, submitCallback, modalParameters) {
                this.formName        = formName;
                this.submitCallback  = submitCallback;
                this.modalParameters = _.extend(this.modalParameters, modalParameters);
            },

            /**
             * Render the modal with the custom form service.
             * Returns the deferred object to catch success (OK) & fail (Cancel) event of the modal.
             *
             * @return {Promise}
             */
            open: function () {
                var deferred = $.Deferred();

                FormBuilder
                    .build(this.formName)
                    .then(function (form) {
                        this.modal = new Backbone.BootstrapModal(this.modalParameters);
                        this.modal.open();

                        form.setElement(this.modal.$('.modal-body')).render();

                        mediator.on('pim_enrich:form:modal:ok_button:disable', function () {
                            this.disableOkBtn();
                        }.bind(this));

                        mediator.on('pim_enrich:form:modal:ok_button:enable', function () {
                            this.enableOkBtn();
                        }.bind(this));

                        this.modal.on('cancel', deferred.reject);
                        this.modal.on('ok', function () {
                            if (this.modal.$('.modal-footer .ok').hasClass('disabled')) {
                                return;
                            }
                            this.submitCallback(form).then(function () {
                                var data = form.getFormData();
                                deferred.resolve(data);

                                this.modal.close();
                            }.bind(this));
                        }.bind(this));
                    }.bind(this));

                return deferred;
            },

            /**
             * Close the modal UI element.
             */
            close: function () {
                this.modal.close();
            },

            /**
             * Enable the modal ok button.
             */
            enableOkBtn: function () {
                this.modal.$('.modal-footer .ok').removeClass('disabled');
            },

            /**
             * Disable the modal ok button.
             */
            disableOkBtn: function () {
                this.modal.$('.modal-footer .ok').addClass('disabled');
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 536 */
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/jquery.wizard.js ***!
  \**************************************************************************/
/***/ (function(module, exports) {

/* global jQuery */
(function ($) {
    'use strict';

    $.fn.wizard = function (options) {
        var opts = $.extend({}, $.fn.wizard.defaults, options);
        var $steps = $(this).find('li');
        var currentStep = opts.currentStep;

        if (!$(this).hasClass('wizard')) {
            $(this).addClass('wizard');
        }

        $steps.each(function () {
            $('div', this)
                .remove('.progress-start')
                .remove('.progress-end')
                .remove('.dot');
            $(this)
                .append('<div class="progress-start"></div>')
                .append('<div class="progress-end"></div>');
        });

        $steps.first().find('.progress-start').hide();
        $steps.last().find('.progress-end').hide();

        for (var i = 0; i < currentStep; i++) {
            if (i !== 0) {
                $steps.eq(i).find('.progress-start').addClass('active');
            }
            if (i !== currentStep - 1) {
                $steps.eq(i).find('.progress-end').addClass('active');
            }
            if (i === currentStep - 1) {
                $steps.eq(i).append('<div class="dot"><i class="icon-circle"></i></div>');
            }
        }
    };

    $.fn.wizard.defaults = {
        currentStep: 1
    };
})(jQuery);


/***/ }),
/* 537 */
/* unknown exports provided */
/* all exports used */
/*!*****************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/manager/history-item-manager.js ***!
  \*****************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! routing */ 7)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, Routing) {
        return {
            /**
             * Saves a history item.
             *
             * @param {string} url
             * @param {Object} title
             */
            save: function (url, title) {
                return $.post(
                    Routing.generate('pim_enrich_navigation_history_rest_post'),
                    JSON.stringify({url: url, title: title}),
                    null,
                    'json'
                );
            }
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 538 */
/* unknown exports provided */
/* all exports used */
/*!************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/pim-attributeoptionview.js ***!
  \************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! backbone */ 6),
        __webpack_require__(/*! oro/translator */ 2),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! oro/mediator */ 8),
        __webpack_require__(/*! oro/loading-mask */ 15),
        __webpack_require__(/*! pim/dialog */ 14),
        __webpack_require__(/*! pim/template/attribute-option/index */ 467),
        __webpack_require__(/*! pim/template/attribute-option/edit */ 466),
        __webpack_require__(/*! pim/template/attribute-option/show */ 468),
        __webpack_require__(/*! jquery-ui */ 59)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        $,
        _,
        Backbone,
        __,
        Routing,
        mediator,
        LoadingMask,
        Dialog,
        indexTemplate,
        editTemplate,
        showTemplate
    ) {
        'use strict';

        var AttributeOptionItem = Backbone.Model.extend({
            defaults: {
                code: '',
                optionValues: {}
            }
        });

        var ItemCollection = Backbone.Collection.extend({
            model: AttributeOptionItem,
            initialize: function (options) {
                this.url = options.url;
            }
        });

        var EditableItemView = Backbone.View.extend({
            tagName: 'tr',
            className: 'AknGrid-bodyRow editable-item-row',
            showTemplate: _.template(showTemplate),
            editTemplate: _.template(editTemplate),
            events: {
                'click .show-row':   'stopEditItem',
                'click .edit-row':   'startEditItem',
                'click .delete-row': 'deleteItem',
                'click .update-row': 'updateItem',
                'keyup input':       'soil',
                'keydown':           'cancelSubmit'
            },
            editable: false,
            parent: null,
            loading: false,
            locales: [],
            initialize: function (options) {
                this.locales       = options.locales;
                this.parent        = options.parent;
                this.model.urlRoot = this.parent.updateUrl;

                this.render();
            },
            render: function () {
                var template = null;

                if (this.editable) {
                    this.clean();
                    this.$el.addClass('in-edition');
                    template = this.editTemplate;
                } else {
                    this.$el.removeClass('in-edition');
                    template = this.showTemplate;
                }

                this.$el.html(template({
                    item: this.model.toJSON(),
                    locales: this.locales
                }));

                this.$el.attr('data-item-id', this.model.id);

                return this;
            },
            showReadableItem: function () {
                this.editable = false;
                this.parent.showReadableItem(this);
                this.clean();
                this.render();
            },
            showEditableItem: function () {
                this.editable = true;
                this.render();
                this.model.set(this.loadModelFromView().attributes);
            },
            startEditItem: function () {
                var rowIsEditable = this.parent.requestRowEdition(this);

                if (rowIsEditable) {
                    this.showEditableItem();
                }
            },
            stopEditItem: function () {
                if (!this.model.id || this.dirty) {
                    if (this.dirty) {
                        Dialog.confirm(
                            __('confirm.attribute_option.cancel_edition_on_new_option_text'),
                            __('confirm.attribute_option.cancel_edition_on_new_option_title'),
                            function () {
                                this.showReadableItem(this);
                                if (!this.model.id) {
                                    this.deleteItem();
                                }
                            }.bind(this));
                    } else {
                        if (!this.model.id) {
                            this.deleteItem();
                        } else {
                            this.showReadableItem();
                        }
                    }
                } else {
                    this.showReadableItem();
                }
            },
            deleteItem: function () {
                var itemCode = this.el.firstChild.innerText;

                Dialog.confirm(
                    __('pim_enrich.item.delete.confirm.content', {'itemName': itemCode}),
                    __('pim_enrich.item.delete.confirm.title', {'itemName': itemCode}),
                    function () {
                        this.parent.deleteItem(this);
                    }.bind(this)
                );
            },
            updateItem: function () {
                this.inLoading(true);

                var editedModel = this.loadModelFromView();

                editedModel.save(
                    {},
                    {
                        url: this.model.url(),
                        success: function () {
                            this.inLoading(false);
                            this.model.set(editedModel.attributes);
                            this.clean();
                            this.stopEditItem();
                        }.bind(this),
                        error: this.showValidationErrors.bind(this)
                    }
                );
            },
            showValidationErrors: function (data, xhr) {
                this.inLoading(false);

                var response = xhr.responseJSON;

                if (response.code) {
                    this.$el.find('.validation-tooltip')
                        .attr('data-original-title', response.code)
                        .removeClass('AknIconButton--hide')
                        .tooltip('destroy')
                        .tooltip('show');
                } else {
                    Dialog.alert(
                        __('alert.attribute_option.error_occured_during_submission'),
                        __('error.saving.attribute_option')
                    );
                }
            },
            cancelSubmit: function (e) {
                if (e.keyCode === 13) {
                    this.updateItem();

                    return false;
                }
            },
            loadModelFromView: function () {
                var attributeOptions = {};
                var editedModel = this.model.clone();

                editedModel.urlRoot = this.model.urlRoot;

                _.each(this.$el.find('.attribute-option-value'), function (input) {
                    var locale = input.dataset.locale;

                    attributeOptions[locale] = {
                        locale: locale,
                        value:  input.value,
                        id:     this.model.get('optionValues')[locale] ?
                            this.model.get('optionValues')[locale].id :
                            null
                    };
                }.bind(this));

                editedModel.set('code', this.$el.find('.attribute_option_code').val());
                editedModel.set('optionValues', attributeOptions);

                return editedModel;
            },
            inLoading: function (loading) {
                this.parent.inLoading(loading);
            },
            soil: function () {
                if (JSON.stringify(this.model.attributes) !== JSON.stringify(this.loadModelFromView().attributes)) {
                    this.dirty = true;
                } else {
                    this.dirty = false;
                }
            },
            clean: function () {
                this.dirty = false;
            }
        });

        var ItemCollectionView = Backbone.View.extend({
            tagName: 'table',
            className: 'AknGrid table attribute-option-view',
            template: _.template(
                '<!-- Pim/Bundle/EnrichBundle/Resources/public/js/pim-attributeoptionview.js -->' +
                '<colgroup>' +
                    '<col class="code" span="1"></col>' +
                    '<col class="fields" span="<%= locales.length %>"></col>' +
                    '<col class="action" span="1"></col>' +
                '</colgroup>' +
                '<thead>' +
                    '<tr>' +
                        '<th class="AknGrid-headerCell"><%= code_label %></th>' +
                        '<% _.each(locales, function (locale) { %>' +
                        '<th class="AknGrid-headerCell">' +
                            '<%= locale %>' +
                        '</th>' +
                        '<% }); %>' +
                        '<th class="AknGrid-headerCell AknGrid-headerCell--right">Action</th>' +
                    '</tr>' +
                '</thead>' +
                '<tbody></tbody>' +
                '<tfoot>' +
                    '<tr class="AknGrid-bodyRow">' +
                        '<td class="AknGrid-bodyCell AknGrid-bodyCell--right" colspan="<%= 2 + locales.length %>">' +
                            '<span class="AknButton AknButton--grey AknButton--small option-add">' +
                                '<%= add_option_label %>' +
                            '</span>' +
                        '</td>' +
                    '</tr>' +
                '</tfoot>'
            ),
            events: {
                'click .option-add': 'addItem'
            },
            $target: null,
            locales: [],
            sortable: true,
            sortingUrl: '',
            updateUrl: '',
            currentlyEditedItemView: null,
            itemViews: [],
            rendered: false,
            initialize: function (options) {
                this.$target    = options.$target;
                this.collection = new ItemCollection({url: options.updateUrl});
                this.locales    = options.locales;
                this.updateUrl  = options.updateUrl;
                this.sortingUrl = options.sortingUrl;
                this.sortable   = options.sortable;

                this.render();
                this.load();
            },
            render: function () {
                this.$el.empty();

                this.currentlyEditedItemView = null;
                this.updateEditionStatus();

                this.$el.html(this.template({
                    locales: this.locales,
                    add_option_label: __('label.attribute_option.add_option'),
                    code_label: __('Code')
                }));

                _.each(this.collection.models, function (attributeOptionItem) {
                    this.addItem({item: attributeOptionItem});
                }.bind(this));

                if (0 === this.collection.length) {
                    this.addItem();
                }

                if (!this.rendered) {
                    this.$target.html(this.$el);

                    this.rendered = true;
                }

                this.$el.sortable({
                    items: 'tbody tr',
                    axis: 'y',
                    connectWith: this.$el,
                    containment: this.$el,
                    distance: 5,
                    cursor: 'move',
                    helper: function (e, ui) {
                        ui.children().each(function () {
                            $(this).width($(this).width());
                        });

                        return ui;
                    },
                    stop: function () {
                        this.updateSorting();
                    }.bind(this)
                });

                this.updateSortableStatus(this.sortable);

                return this;
            },
            load: function () {
                this.itemViews = [];
                this.inLoading(true);
                this.collection
                    .fetch({
                        success: function () {
                            this.inLoading(false);
                            this.render();
                        }.bind(this)
                    });
            },
            addItem: function (opts) {
                var options = opts || {};

                //If no item model provided we create one
                var itemToAdd;
                if (!options.item) {
                    itemToAdd = new AttributeOptionItem();
                } else {
                    itemToAdd = options.item;
                }

                var newItemView = this.createItemView(itemToAdd);

                if (newItemView) {
                    this.$el.children('tbody').append(newItemView.$el);
                }
            },
            createItemView: function (item) {
                var itemView = new EditableItemView({
                    model:    item,
                    url:      this.updateUrl,
                    locales:  this.locales,
                    parent:   this
                });

                //If the item is new the view is changed to edit mode
                if (!item.id) {
                    if (!this.requestRowEdition(itemView)) {
                        return;
                    } else {
                        itemView.showEditableItem();
                    }
                }

                this.collection.add(item);
                this.itemViews.push(itemView);

                return itemView;
            },
            requestRowEdition: function (attributeOptionRow) {
                if (this.currentlyEditedItemView) {
                    if (this.currentlyEditedItemView.dirty) {
                        Dialog.alert(__('alert.attribute_option.save_before_edit_other'));

                        return false;
                    } else {
                        this.currentlyEditedItemView.stopEditItem();
                        this.currentlyEditedItemView = null;
                        this.updateEditionStatus();
                    }
                }

                if (attributeOptionRow.model.id) {
                    this.currentlyEditedItemView = attributeOptionRow;
                }

                this.updateEditionStatus();

                return true;
            },
            showReadableItem: function (item) {
                if (item === this.currentlyEditedItemView) {
                    this.currentlyEditedItemView = null;
                    this.updateEditionStatus();
                }
            },
            deleteItem: function (item) {
                this.inLoading(true);

                item.model.destroy({
                    success: function () {
                        this.inLoading(false);

                        this.collection.remove(item);
                        this.currentlyEditedItemView = null;
                        this.updateEditionStatus();

                        if (0 === this.collection.length) {
                            this.addItem();
                            item.$el.hide(0);
                        } else if (!item.model.id) {
                            item.$el.hide(0);
                        } else {
                            item.$el.hide(500);
                        }
                    }.bind(this),
                    error: function (data, response) {
                        this.inLoading(false);
                        var message;

                        if (response.responseJSON) {
                            message = response.responseJSON.message;
                        } else {
                            message = response.responseText;
                        }

                        Dialog.alert(message, __('error.removing.attribute_option'));
                    }.bind(this)
                });
            },
            updateEditionStatus: function () {
                if (this.currentlyEditedItemView) {
                    this.$el.addClass('in-edition');
                } else {
                    this.$el.removeClass('in-edition');
                }
            },
            updateSortableStatus: function (sortable) {
                this.sortable = sortable;

                if (sortable) {
                    this.$el.sortable('enable');
                } else {
                    this.$el.sortable('disable');
                }
            },
            updateSorting: function () {
                this.inLoading(true);
                var sorting = [];

                var rows = this.$el.find('tbody tr');
                for (var i = rows.length - 1; i >= 0; i--) {
                    sorting[i] = rows[i].dataset.itemId;
                }

                $.ajax({
                    url: this.sortingUrl,
                    type: 'PUT',
                    data: JSON.stringify(sorting)
                }).done(function () {
                    this.inLoading(false);
                }.bind(this));
            },
            inLoading: function (loading) {
                if (loading) {
                    var loadingMask = new LoadingMask();
                    loadingMask.render().$el.appendTo(this.$el);
                    loadingMask.show();
                } else {
                    this.$el.find('.loading-mask').remove();
                }
            }
        });

        return function ($element) {
            var itemCollectionView = new ItemCollectionView(
            {
                $target: $element,
                updateUrl: Routing.generate(
                    'pim_enrich_attributeoption_index',
                    {attributeId: $element.data('attribute-id')}
                ),
                sortingUrl: Routing.generate(
                    'pim_enrich_attributeoption_update_sorting',
                    {attributeId: $element.data('attribute-id')}
                ),
                locales: $element.data('locales'),
                sortable: $element.data('sortable')
            });

            mediator.on('attribute:auto_option_sorting:changed', function (autoSorting) {
                itemCollectionView.updateSortableStatus(!autoSorting);
            }.bind(this));
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 539 */
/* unknown exports provided */
/* all exports used */
/*!******************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/pim-currencyfield.js ***!
  \******************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! backbone */ 6), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/mediator */ 8), __webpack_require__(/*! bootstrap */ 35)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, Backbone, _, mediator) {
        'use strict';
        /**
         * Allow expanding/collapsing currency fields
         *
         * @author    Filips Alpe <filips@akeneo.com>
         * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
         * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         */

        return Backbone.View.extend({
            fieldSelector:   '.currency-field[data-metadata]',
            expandIcon:      'icon-caret-right',
            collapseIcon:    'icon-caret-down',
            first:           true,
            expanded:        true,
            currencies:      null,
            scopable:        false,
            inputClass:      'input-small',
            smallInputClass: 'input-mini',
            inputThreshold:  3,

            currencyTemplate: _.template(
                '<span class="currency-header<%= small ? " small" : "" %>">' +
                    '<% _.each(currencies, function (currency) { %>' +
                        '<span class="currency-label"><%= currency %></span>' +
                    '<% }); %>' +
                '</span>'
            ),

            template: _.template(
                '<% _.each(data, function (item) { %>' +
                    '<% _.each(currencies, function (currency, index) { %>' +
                        '<% if (item.label === currency) { %>' +
                            '<% if (scopable && index === 0) { %>' +
                                '<label class="control-label add-on" title="<%= item.scope %>">' +
                                    '<%= item.scope[0].toUpperCase() %>' +
                                '</label>' +
                                '<div class="scopable-input">' +
                            '<% } %>' +
                            '<input type="hidden" id="<%= item.currency.fieldId %>" ' +
                                'name="<%= item.currency.fieldName %>" value="<%= item.currency.data %>"' +
                                '<%= item.currency.disabled ? " disabled" : "" %> >' +
                            '<input type="text" class="<%= inputClass %>" id="<%= item.value.fieldId %>"' +
                                'name="<%= item.value.fieldName %>" value="<%= item.value.data %>"' +
                                '<% if (!scopable && index === 0) { %>' +
                                    ' style="border-top-left-radius:3px;border-bottom-left-radius:3px;"' +
                                '<% } %>' +
                                '<%= item.value.disabled ? " disabled" : "" %> >' +
                            '<% if (scopable && index + 1 === currencies.length) { %>' +
                                '</div>' +
                            '<% } %>' +
                        '<% } %>' +
                    '<% }); %>' +
                '<% }); %>'
            ),

            events: {
                'click label i.field-toggle': '_toggle'
            },

            initialize: function () {
                this._extractMetadata();
                this.render();

                if (this.scopable) {
                    mediator.on('scopablefield:changescope', function (scope) {
                        this._changeDefault(scope);
                    }.bind(this));

                    mediator.on('scopablefield:collapse', function (id) {
                        if (!id || this.$el.find('#' + id).length) {
                            this._collapse();
                        }
                    }.bind(this));

                    mediator.on('scopablefield:expand', function (id) {
                        if (!id || this.$el.find('#' + id).length) {
                            this._expand();
                        }
                    }.bind(this));
                }
            },

            _extractMetadata: function () {
                this.scopable = this.$el.hasClass('scopable');
                var currencies = [];

                this.$el.find(this.fieldSelector).each(function () {
                    var metadata = $(this).data('metadata');
                    currencies.push(metadata.label);
                });

                this.currencies = _.uniq(currencies);
            },

            _renderTarget: function (index, target) {
                var $target = $(target);
                var data = [];

                var extractScope = this.scopable;

                $target.find(this.fieldSelector).each(function () {
                    var metadata = $(this).data('metadata');
                    if (extractScope) {
                        metadata.scope = $(this).parent().parent().parent().data('scope');
                    }
                    data.push(metadata);
                });

                $target.empty();
                $target.prepend(
                    this.template({
                        currencies:   this.currencies,
                        data:         data,
                        scopable:     this.scopable,
                        first:        this.first,
                        collapseIcon: this.collapseIcon,
                        inputClass:   this.currencies.length > this.inputThreshold ?
                                        this.smallInputClass : this.inputClass
                    })
                );

                if (this.first) {
                    $target.parent().parent().addClass('first');
                    this.first = false;
                }
            },

            render: function () {
                this.$el.addClass('control-group').find('.control-group.hide').removeClass('hide');

                var $label = this.$el.find('label.control-label:first').prependTo(this.$el);
                this.$el.find('label.control-label:not(:first)').remove();

                var $fields = this.$el.find('div[data-scope]');

                if (this.scopable && $fields.length > 1) {
                    var $toggleIcon = $('<i>', { 'class': 'field-toggle ' + this.collapseIcon });
                    $label.prepend($toggleIcon);
                }

                $fields.each(function () {
                    var $parent = $(this).parent();
                    $(this).insertBefore($parent);
                    $parent.remove();
                });

                if (this.scopable) {
                    this.$el.find('div.controls').addClass('input-prepend');
                }

                var $header = $(this.currencyTemplate({
                    currencies: this.currencies,
                    scopable:   this.scopable,
                    small:      this.currencies.length > this.inputThreshold
                }));
                $header.insertAfter($label);
                var $iconsContainer = this.$el.find('.icons-container:first');
                $iconsContainer.insertAfter($header);

                _.each(this.$el.find('.validation-tooltip'), function (tooltip) {
                    $(tooltip).appendTo($iconsContainer);
                });

                var $targets = this.$el.find('div.controls');

                $targets.each(this._renderTarget.bind(this));

                if (this.scopable) {
                    $iconsContainer.appendTo(this.$el.find('div.first .scopable-input'));
                    this._collapse();
                    mediator.trigger('scopablefield:rendered', this.$el);
                } else {
                    $iconsContainer.appendTo(this.$el.find('.controls'));
                }

                return this;
            },

            _expand: function () {
                if (!this.expanded) {
                    this.expanded = true;

                    this.$el.find('div[data-scope]').removeClass('hide');
                    this.$el.find('i.field-toggle').removeClass(this.expandIcon).addClass(this.collapseIcon);
                    this.$el.removeClass('collapsed').addClass('expanded').trigger('expand');
                }

                return this;
            },

            _collapse: function () {
                if (this.expanded) {
                    this.expanded = false;

                    this.$el.find('div[data-scope]:not(:first)').addClass('hide');
                    this.$el.find('i.field-toggle').removeClass(this.collapseIcon).addClass(this.expandIcon);
                    this.$el.removeClass('expanded').addClass('collapsed').trigger('collapse');
                }

                return this;
            },

            _toggle: function (e) {
                if (e) {
                    e.preventDefault();
                }

                return this.expanded ? this._collapse() : this._expand();
            },

            _changeDefault: function (scope) {
                var $fields = this.$el.find('>div[data-scope]');
                this.$el.find('.first').removeClass('first');
                var $firstField = $fields.filter('[data-scope="' + scope + '"]');

                $firstField.addClass('first').insertBefore($fields.eq(0));

                if (this.scopable) {
                    var $iconsContainer = this.$el.find('.icons-container:first');
                    $iconsContainer.appendTo(this.$el.find('div.first .scopable-input'));
                }

                this._toggle();
                this._toggle();

                return this;
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 540 */
/* unknown exports provided */
/* all exports used */
/*!*******************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/pim-item-tableview.js ***!
  \*******************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backbone */ 6), __webpack_require__(/*! oro/translator */ 2), __webpack_require__(/*! routing */ 7), __webpack_require__(/*! oro/mediator */ 8), __webpack_require__(/*! oro/loading-mask */ 15), __webpack_require__(/*! pim/dialog */ 14)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, Backbone, __, Routing, mediator, LoadingMask, Dialog) {
        'use strict';

        return Backbone.View.extend({
            tagName: 'table',
            template: '',
            events: {},
            $target: null,
            itemViews: [],
            url: '',
            collectionClass: null,
            itemClass: null,
            itemViewClass: null,
            rendered: false,
            initialize: function (options) {
                this.$target         = options.$target;
                this.collectionClass = options.collectionClass;
                this.itemClass       = options.itemClass;
                this.itemViewClass   = options.itemViewClass;
                this.url             = options.url;
                this.collection      = new this.collectionClass({url: options.url});
                this.render();

                this.load();
            },
            render: function () {
                this.$el.empty();
                this.$el.html(this.renderTemplate());

                _.each(this.collection.models, function (ruleItem) {
                    this.addItem({item: ruleItem});
                }.bind(this));

                if (!this.rendered) {
                    this.$target.html(this.$el);

                    this.rendered = true;
                }

                return this;
            },
            renderTemplate: function () {
                return this.template({});
            },
            load: function () {
                this.itemViews = [];
                this.inLoading(true);
                this.collection
                    .fetch({
                        success: function () {
                            this.inLoading(false);
                            this.render();
                        }.bind(this)
                    });
            },
            addItem: function (opts) {
                var options = opts || {};

                var newItemView = this.createItemView(options.item);

                if (newItemView) {
                    this.$el.children('tbody').append(newItemView.$el);
                }
            },
            createItemView: function (item) {
                var itemView = new this.itemViewClass({
                    model:    item,
                    parent:   this
                });

                itemView.showReadableItem();

                this.collection.add(item);
                this.itemViews.push(itemView);

                return itemView;
            },
            deleteItem: function (item) {
                this.inLoading(true);

                item.model.destroy({
                    success: function () {
                        this.inLoading(false);

                        this.collection.remove(item);

                        if (0 === this.collection.length) {
                            this.render();
                            item.$el.hide(0);
                        } else if (!item.model.id) {
                            item.$el.hide(0);
                        } else {
                            item.$el.hide(500);
                        }
                    }.bind(this),
                    error: function (data, response) {
                        this.inLoading(false);
                        var message;

                        if (response.responseJSON) {
                            message = response.responseJSON;
                        } else {
                            message = response.responseText;
                        }

                        Dialog.alert(message, __('pim_enrich.item.list.delete.error'));
                    }.bind(this)
                });
            },
            inLoading: function (loading) {
                if (loading) {
                    var loadingMask = new LoadingMask();
                    loadingMask.render().$el.appendTo(this.$el);
                    loadingMask.show();
                } else {
                    this.$el.find('.loading-mask').remove();
                }
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));



/***/ }),
/* 541 */
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/pim-item-view.js ***!
  \**************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! backbone */ 6), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/translator */ 2), __webpack_require__(/*! pim/dialog */ 14)], __WEBPACK_AMD_DEFINE_RESULT__ = function (Backbone, _, __, Dialog) {
        'use strict';

        return Backbone.View.extend({
            tagName: 'tr',
            template: '',
            itemName: 'item',
            events: {
                'click .delete-row': 'deleteItem'
            },
            parent: null,
            loading: false,
            initialize: function (options) {
                this.parent    = options.parent;
                this.model.rootUrl = this.parent.url;

                this.render();
            },
            render: function () {
                this.$el.html(this.renderTemplate());

                this.$el.attr('data-item-id', this.model.id);

                return this;
            },
            renderTemplate: function () {
                return this.template({});
            },
            showReadableItem: function () {
                this.render();
            },
            deleteItem: function () {
                Dialog.confirm(
                    __('pim_enrich.item.delete.confirm.content', {'itemName': this.itemName}),
                    __('pim_enrich.item.delete.confirm.title', {'itemName': this.itemName}),
                    function () {
                        this.parent.deleteItem(this);
                    }.bind(this)
                );
            },
            inLoading: function (loading) {
                this.parent.inLoading(loading);
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 542 */
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/pim-popinform.js ***!
  \**************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backbone */ 6), __webpack_require__(/*! jquery.multiselect */ 457), __webpack_require__(/*! jquery.multiselect.filter */ 456)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, Backbone) {
        'use strict';

        return function (elementId) {
            var $el = $('#' + elementId);
            if (!$el || !$el.length || !_.isObject($el)) {
                throw new Error('Unable to instantiate available attributes form on this element');
            }

            var classes = 'pimmultiselect pimmultiselect_' + elementId;
            if (!_.isUndefined($el.attr('data-classes'))) {
                classes = classes + ' ' + $el.attr('data-classes');
            }

            var opts = {
                title: $el.attr('data-title'),
                placeholder: $el.attr('data-placeholder'),
                emptyText: $el.attr('data-empty-text'),
                header: '',
                height: 175,
                minWidth: 225,
                classes: classes,
                position: {
                    my: 'right top',
                    at: 'right bottom',
                    collision: 'none'
                }
            };
            opts.selectedText = opts.title;
            opts.noneSelectedText = opts.title;

            var $select = $el.find('select');

            $select.multiselect(opts).multiselectfilter({
                label: false,
                placeholder: opts.placeholder
            });

            var $menu = $('.ui-multiselect-menu.pimmultiselect_' + elementId).appendTo($('#container'));
            var saveButton = $el.attr('data-save-button');
            var target = $el.attr('data-target');

            var footerContainer = $('<div>').addClass('ui-multiselect-footer').appendTo($menu);
            var $saveButton = $('<a>').addClass('btn btn-small').html(saveButton).on('click', function () {
                $select.multiselect('close');
                if ($select.val() !== null) {
                    Backbone.Router.prototype.trigger('route');
                    $el.submit();
                }
            }).appendTo(footerContainer);

            var $openButton = $el.find('button.pimmultiselect').addClass('btn btn-group');
            $openButton.append($('<span>', { 'class': 'caret' })).removeAttr('style');
            if (target) {
                $openButton.prependTo($(target));
            }

            $menu.find('input[type="search"]').width(207);

            var $content = $menu.find('.ui-multiselect-checkboxes');
            if (!$content.html()) {
                $content.html(
                    $('<span>', { html: opts.emptyText, css: {
                        'position': 'absolute',
                        'color': '#999',
                        'padding': '15px',
                        'font-size': '13px'
                    }})
                );
                $saveButton.addClass('disabled');
            }
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 543 */
/* unknown exports provided */
/* all exports used */
/*!*************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/pim-scopable.js ***!
  \*************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! backbone */ 6),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! oro/mediator */ 8),
        __webpack_require__(/*! wysiwyg */ 93),
        __webpack_require__(/*! pim/optionform */ 491),
        __webpack_require__(/*! pim/fileinput */ 500),
        __webpack_require__(/*! bootstrap */ 35),
        __webpack_require__(/*! bootstrap.bootstrapswitch */ 43),
        __webpack_require__(/*! jquery.select2 */ 11)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, Backbone, _, mediator, wysiwyg, optionform, fileinput) {
        'use strict';
        /**
         * Allow expanding/collapsing scopable fields
         *
         * @author    Filips Alpe <filips@akeneo.com>
         * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
         * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         */
        var ScopableField = Backbone.View.extend({
            field:    null,
            rendered: false,
            isMetric: false,

            template: _.template(
                '<%= field.hiddenInput %>' +
                '<div class="control-group">' +
                    '<div class="controls input-prepend<%= isMetric ? " metric input-append" : "" %>">' +
                        '<label class="control-label add-on" for="<%= field.id %>" title="<%= field.scope %>">' +
                            '<%= field.scope[0].toUpperCase() %>' +
                        '</label>' +
                        '<div class="scopable-input">' +
                            '<%= field.input %>' +
                            '<div class="icons-container">' +
                                '<%= field.icons %>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            ),

            initialize: function () {
                var field = {};

                if (this.$el.find('.upload-zone').length) {
                    field.id = null;
                    field.input = this.$el.find('.upload-zone').get(0).outerHTML;
                } else if (this.$el.find('.switch').length) {
                    var $original = this.$el.find('.switch');
                    var $wrap = $original.clone().empty().removeClass('has-switch');
                    var $input = $original.find('input');

                    field.id = $input.attr('id');
                    $input.appendTo($wrap);

                    field.input = $wrap.get(0).outerHTML;
                } else if (this.$el.find('.control-label')) {
                    field.id = this.$el.find('.control-label').attr('for');

                    var $field = $('#' + field.id);

                    if ($field.hasClass('select2-input') || $field.hasClass('select2-focusser')) {
                        var id = $field.closest('.select2-container').attr('id');
                        if (/^s2id_.+/.test(id)) {
                            id = id.slice(5);
                            field.id = id;
                            $field = $('#' + id);
                        }
                        $field.select2('destroy');
                    }

                    field.input = $field.get(0).outerHTML;

                    _.each($field.siblings('input, select'), function (el) {
                        field.input += el.outerHTML;
                    });

                    if (this.$el.find('.controls.metric').length) {
                        this.isMetric = true;
                    }

                    if ($field.siblings('a.add-attribute-option').length) {
                        field.input += $field.siblings('a.add-attribute-option').get(0).outerHTML;
                    }

                    _.each($field.siblings('.validation-tooltip'), function (icon) {
                        $(icon).appendTo(this.$el.find('.icons-container'));
                    }.bind(this));
                }

                field.scope       = this.$el.data('scope');
                field.hiddenInput = this.$el.find('input[type="hidden"]').get(0).outerHTML;
                field.icons       = this.$el.find('.icons-container').html();

                this.field = field;
            },

            render: function () {
                if (!this.rendered) {
                    this.rendered = true;
                    this.$el.empty();
                    this.$el.append(
                        this.template({
                            field:    this.field,
                            isMetric: this.isMetric
                        })
                    );

                    this.$el.find('[data-toggle="tooltip"]').tooltip();
                    this.$el.find('.switch').bootstrapSwitch();
                    this.$el.find('select').select2();
                }

                return this;
            }
        });

        return Backbone.View.extend({
            label:        null,
            fieldViews:   [],
            fields:       [],
            expanded:     true,
            rendered:     false,
            expandIcon:   'icon-caret-right',
            collapseIcon: 'icon-caret-down',

            skipUIInit: false,

            template: _.template(
                '<%= label %>'
            ),

            initialize: function (opts) {
                var options = opts || {};
                this.fieldViews = [];
                this.fields     = [];
                this.expanded   = true;
                this.rendered   = false;

                this._reindexFields();

                _.each(this.fields, function ($field) {
                    this._addField($field);
                }.bind(this));

                this.label = this.$el.find('.control-label').first().get(0).outerHTML;

                this.render();

                if (_.has(options, 'initialScope')) {
                    this._changeDefault(options.initialScope);
                }

                mediator.on('scopablefield:changescope', function (scope) {
                    this._changeDefault(scope);
                }.bind(this));

                mediator.on('scopablefield:collapse', function (id) {
                    if (!id || this.$el.find('#' + id).length) {
                        this._collapse();
                    }
                }.bind(this));

                mediator.on('scopablefield:expand', function (id) {
                    if (!id || this.$el.find('#' + id).length) {
                        this._expand();
                    }
                }.bind(this));

                var self = this;
                this.$el.closest('form').on('validate', function () {
                    if (self.$el.find('.validation-tooltip:hidden').length) {
                        self._expand();
                    }
                });
            },

            render: function () {
                if (!this.rendered) {
                    this.rendered = true;
                    this.$el.empty().addClass('control-group');
                    this.$el.append(
                        this.template({
                            label: this.label
                        })
                    );

                    if (this.fieldViews.length > 1) {
                        var $toggleIcon = $('<i>', { 'class': 'field-toggle ' + this.collapseIcon });
                        this.$el.find('label').removeAttr('for').prepend($toggleIcon);
                    }

                    _.each(this.fieldViews, function (fieldView) {
                        fieldView.render().$el.appendTo(this.$el);
                    }.bind(this));

                    this._collapse();

                    var $optionLink = this.$el.find('a.add-attribute-option');
                    if ($optionLink.length) {
                        optionform.init('#' + $optionLink.attr('id'));
                    }

                    mediator.trigger('scopablefield:rendered', this.$el);
                }

                return this;
            },

            _addField: function ($field) {
                this.fieldViews.push(new ScopableField({ el: $field }));

                return this;
            },

            _expand: function () {
                if (!this.expanded) {
                    this.expanded = true;

                    this._reindexFields();

                    var first = true;
                    _.each(this.fields, function (field) {
                        this._showField(field, first);
                        first = false;
                    }.bind(this));

                    this._initUI();
                    this.$el.find('i.field-toggle').removeClass(this.expandIcon).addClass(this.collapseIcon);
                    this.$el.removeClass('collapsed').addClass('expanded').trigger('expand');
                }

                return this;
            },

            _collapse: function () {
                if (this.expanded) {
                    this.expanded = false;

                    this._reindexFields();

                    var first = true;
                    _.each(this.fields, function (field) {
                        if (first) {
                            this._showField(field, first);
                            first = false;
                        } else {
                            this._hideField(field);
                        }
                    }.bind(this));

                    this._initUI();
                    this.$el.find('i.field-toggle').removeClass(this.collapseIcon).addClass(this.expandIcon);
                    this.$el.removeClass('expanded').addClass('collapsed').trigger('collapse');
                }

                return this;
            },

            _refreshFieldsDisplay: function () {
                _.each(this.fields, function ($field) {
                    if (this.expanded || $field.hasClass('first')) {
                        this._showField($field);
                    } else {
                        this._hideField($field);
                    }
                }.bind(this));
            },

            _toggle: function (e) {
                if (e) {
                    e.preventDefault();
                }

                return this.expanded ? this._collapse() : this._expand();
            },

            _changeDefault: function (scope) {
                this.skipUIInit = true;
                this._toggle();
                this._setFieldFirst(this.$el.find('[data-scope="' + scope + '"]:first'));
                this._refreshFieldsDisplay();
                this._initUI();

                return this;
            },

            _reindexFields: function () {
                this.fields = _.map(this.$el.find('[data-scope]'), function (field) {
                    return $(field);
                });

                if (this.$el.find('[data-scope]').length) {
                    _.first(this.fields).addClass('first');
                }
            },

            _setFieldFirst: function ($field) {
                this.$el.find('[data-scope]').removeClass('first');
                $field.addClass('first');

                var $target = this.$el.find('>label');
                if ($target.length) {
                    $field.insertAfter($target);
                } else {
                    $field.prependTo(this.$el);
                }
            },

            _showField: function (field, first) {
                var $icons = $(field).find('.icons-container i:not(".validation-tooltip")');

                if (first) {
                    $(field).addClass('first');
                    $icons.attr('style', 'display: inline !important');
                    this._setFieldFirst(field);
                } else {
                    $(field).removeClass('first');
                    $icons.attr('style', 'display: none !important');
                }

                $(field).show();
            },

            _hideField: function (field) {
                $(field).hide();
            },

            _initUI: function () {
                if (!this.skipUIInit) {
                    _.each(this.fields, function ($field) {
                        var $textarea = $field.find('textarea.wysiwyg');
                        if ($textarea.length) {
                            wysiwyg.init($textarea);
                        }

                        var $fileInput = $field.find('input[type=file][id]');
                        if ($fileInput.length) {
                            fileinput.init($fileInput.attr('id'));
                        }
                    });
                }

                return this;
            },

            events: {
                'click label i.field-toggle': '_toggle'
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 544 */
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/product/field/boolean-field.js ***!
  \****************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Boolean field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! pim/field */ 84), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! pim/template/product/field/boolean */ 471), __webpack_require__(/*! bootstrap.bootstrapswitch */ 43)], __WEBPACK_AMD_DEFINE_RESULT__ = function (Field, _, fieldTemplate) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        events: {
            'change .field-input input[type="checkbox"]': 'updateModel'
        },
        renderInput: function (context) {
            return this.fieldTemplate(context);
        },
        postRender: function () {
            this.$('.switch').bootstrapSwitch();
        },
        updateModel: function () {
            var data = this.$('.field-input:first input[type="checkbox"]').prop('checked');

            this.setCurrentValue(data);
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 545 */
/* unknown exports provided */
/* all exports used */
/*!*************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/product/field/date-field.js ***!
  \*************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Date field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! pim/field */ 84),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! pim/template/product/field/date */ 472),
        __webpack_require__(/*! datepicker */ 44),
        __webpack_require__(/*! pim/date-context */ 42)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        Field,
        _,
        fieldTemplate,
        Datepicker,
        DateContext
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            events: {
                'change .field-input:first input[type="text"]': 'updateModel',
                'click .field-input:first input[type="text"]': 'click'
            },
            datetimepickerOptions: {
                format: DateContext.get('date').format,
                defaultFormat: DateContext.get('date').defaultFormat,
                language: DateContext.get('language')
            },
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            click: function () {
                Datepicker.init(this.$('.datetimepicker'), this.datetimepickerOptions).datetimepicker('show');

                this.$('.datetimepicker').on('changeDate', function (e) {
                    this.setCurrentValue(this.$(e.target).find('input[type="text"]').val());
                }.bind(this));
            },
            updateModel: function () {
                var data = this.$('.field-input:first input[type="text"]').val();
                data = '' === data ? this.attribute.empty_value : data;

                this.setCurrentValue(data);
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 546 */
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/product/field/media-field.js ***!
  \**************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Media field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! pim/field */ 84),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! pim/attribute-manager */ 24),
        __webpack_require__(/*! pim/template/product/field/media */ 473),
        __webpack_require__(/*! pim/dialog */ 14),
        __webpack_require__(/*! oro/mediator */ 8),
        __webpack_require__(/*! oro/messenger */ 12),
        __webpack_require__(/*! pim/media-url-generator */ 490),
        __webpack_require__(/*! jquery.slimbox */ 462)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, Field, _, Routing, AttributeManager, fieldTemplate, Dialog, mediator, messenger, MediaUrlGenerator) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            events: {
                'change .edit .field-input:first input[type="file"]': 'updateModel',
                'click  .clear-field': 'clearField',
                'click  .open-media': 'previewImage'
            },
            uploadContext: {},
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            getTemplateContext: function () {
                return Field.prototype.getTemplateContext.apply(this, arguments)
                    .then(function (templateContext) {
                        templateContext.inUpload          = !this.isReady();
                        templateContext.mediaUrlGenerator = MediaUrlGenerator;

                        return templateContext;
                    }.bind(this));
            },

            renderCopyInput: function (value) {
                return this.getTemplateContext()
                    .then(function (context) {
                        var copyContext = $.extend(true, {}, context);
                        copyContext.value = value;
                        copyContext.context.locale    = value.locale;
                        copyContext.context.scope     = value.scope;
                        copyContext.editMode          = 'view';
                        copyContext.mediaUrlGenerator = MediaUrlGenerator;

                        return this.renderInput(copyContext);
                    }.bind(this));
            },
            updateModel: function () {
                if (!this.isReady()) {
                    Dialog.alert(_.__(
                        'pim_enrich.entity.product.info.already_in_upload',
                        {'locale': this.context.locale, 'scope': this.context.scope}
                    ));
                }

                var input = this.$('.edit .field-input:first input[type="file"]').get(0);
                if (!input || 0 === input.files.length) {
                    return;
                }

                var formData = new FormData();
                formData.append('file', input.files[0]);

                this.setReady(false);
                this.uploadContext = {
                    'locale': this.context.locale,
                    'scope':  this.context.scope
                };


                $.ajax({
                    url: Routing.generate('pim_enrich_media_rest_post'),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    xhr: function () {
                        var myXhr = $.ajaxSettings.xhr();
                        if (myXhr.upload) {
                            myXhr.upload.addEventListener('progress', this.handleProcess.bind(this), false);
                        }

                        return myXhr;
                    }.bind(this)
                })
                .done(function (data) {
                    this.setUploadContextValue(data);
                    this.render();
                }.bind(this))
                .fail(function (xhr) {
                    var message = xhr.responseJSON && xhr.responseJSON.message ?
                        xhr.responseJSON.message :
                        _.__('pim_enrich.entity.product.error.upload');
                    messenger.addFlashMessage('error', message);
                })
                .always(function () {
                    this.$('> .akeneo-media-uploader-field .progress').css({opacity: 0});
                    this.setReady(true);
                    this.uploadContext = {};
                }.bind(this));
            },
            clearField: function () {
                this.setCurrentValue({
                    filePath: null,
                    originalFilename: null
                });

                this.render();
            },
            handleProcess: function (e) {
                if (this.uploadContext.locale === this.context.locale &&
                    this.uploadContext.scope === this.context.scope
                ) {
                    this.$('> .akeneo-media-uploader-field .progress').css({opacity: 1});
                    this.$('> .akeneo-media-uploader-field .progress .bar').css({
                        width: ((e.loaded / e.total) * 100) + '%'
                    });
                }
            },
            previewImage: function () {
                var mediaUrl = MediaUrlGenerator.getMediaShowUrl(this.getCurrentValue().data.filePath, 'preview');
                if (mediaUrl) {
                    $.slimbox(mediaUrl, '', {overlayOpacity: 0.3});
                }
            },
            setUploadContextValue: function (value) {
                var productValue = AttributeManager.getValue(
                    this.model.get('values'),
                    this.attribute,
                    this.uploadContext.locale,
                    this.uploadContext.scope
                );

                productValue.data = value;
                mediator.trigger('pim_enrich:form:entity:update_state');
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 547 */
/* unknown exports provided */
/* all exports used */
/*!***************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/product/field/metric-field.js ***!
  \***************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Metric field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
    __webpack_require__(/*! jquery */ 1),
    __webpack_require__(/*! pim/field */ 84),
    __webpack_require__(/*! underscore */ 0),
    __webpack_require__(/*! pim/fetcher-registry */ 4),
    __webpack_require__(/*! pim/template/product/field/metric */ 474),
    __webpack_require__(/*! pim/initselect2 */ 30)
], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, Field, _, FetcherRegistry, fieldTemplate, initSelect2) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        events: {
            'change .field-input:first .data, .field-input:first .unit': 'updateModel'
        },
        renderInput: function (context) {
            var $element = $(this.fieldTemplate(context));
            initSelect2.init($element.find('.unit'));

            return $element;
        },
        getTemplateContext: function () {
            return $.when(
                Field.prototype.getTemplateContext.apply(this, arguments),
                FetcherRegistry.getFetcher('measure').fetchAll()
            ).then(function (templateContext, measures) {
                templateContext.measures = measures;

                return templateContext;
            });
        },
        setFocus: function () {
            this.$('.data:first').focus();
        },
        updateModel: function () {
            var amount = this.$('.field-input:first .data').val();
            var unit = this.$('.field-input:first .unit').select2('val');

            this.setCurrentValue({
                unit: '' !== unit ? unit : this.attribute.default_metric_unit,
                amount: '' !== amount ? amount : null
            });
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 548 */
/* unknown exports provided */
/* all exports used */
/*!***************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/product/field/number-field.js ***!
  \***************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Number field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! pim/field */ 84),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! pim/template/product/field/number */ 476)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        Field,
        _,
        fieldTemplate
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            events: {
                'change .field-input:first input[type="text"]': 'updateModel'
            },
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            updateModel: function () {
                var data = this.$('.field-input:first input[type="text"]').val();

                if ('' === data) {
                    data = this.attribute.empty_value;
                }

                this.setCurrentValue(data);
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 549 */
/* unknown exports provided */
/* all exports used */
/*!*************************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/product/field/price-collection-field.js ***!
  \*************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Price collection field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! pim/field */ 84),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! pim/fetcher-registry */ 4),
        __webpack_require__(/*! pim/template/product/field/price-collection */ 477)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, Field, _, FetcherRegistry, fieldTemplate) {
    return Field.extend({
        fieldTemplate: _.template(fieldTemplate),
        events: {
            'change .field-input:first input[type="text"]': 'updateModel'
        },
        renderInput: function (context) {
            context.value.data = _.sortBy(context.value.data, 'currency');

            return this.fieldTemplate(context);
        },
        updateModel: function () {
            var prices = [];
            var inputs = this.$('.field-input:first .price-input input');
            _.each(inputs, function (input) {
                var $input = $(input);
                var inputData = $input.val();
                prices.push({
                    amount: '' === inputData ? null : inputData,
                    currency: $input.data('currency')
                });
            }.bind(this));

            this.setCurrentValue(_.sortBy(prices, 'currency'));
        },
        getTemplateContext: function () {
            return $.when(
                Field.prototype.getTemplateContext.apply(this, arguments),
                FetcherRegistry.getFetcher('currency').fetchAll()
            ).then(function (templateContext, currencies) {
                templateContext.currencies = currencies;

                return templateContext;
            });
        },
        setFocus: function () {
            this.$('input[type="text"]:first').focus();
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 550 */
/* unknown exports provided */
/* all exports used */
/*!*************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/product/field/text-field.js ***!
  \*************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Text field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! pim/field */ 84),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! pim/template/product/field/text */ 479)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        Field,
        _,
        fieldTemplate
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            events: {
                'change .field-input:first input[type="text"]': 'updateModel'
            },
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            updateModel: function () {
                var data = this.$('.field-input:first input[type="text"]').val();
                data = '' === data ? this.attribute.empty_value : data;

                this.setCurrentValue(data);
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 551 */
/* unknown exports provided */
/* all exports used */
/*!*****************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/product/field/textarea-field.js ***!
  \*****************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Textarea field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! pim/field */ 84),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! pim/template/product/field/textarea */ 454)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        Field,
        _,
        fieldTemplate
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            events: {
                'change .field-input:first textarea': 'updateModel'
            },

            /**
             * @inheritDoc
             */
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },

            /**
             * @inheritDoc
             */
            updateModel: function () {
                var data = this.$('.field-input:first textarea:first').val();
                data = '' === data ? this.attribute.empty_value : data;

                this.setCurrentValue(data);
            },

            /**
             * @inheritDoc
             */
            setFocus: function () {
                this.$('.field-input:first textarea').focus();
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 552 */
/* unknown exports provided */
/* all exports used */
/*!****************************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/product/field/wysiwyg-field.js ***!
  \****************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;
/**
 * Wysiwyg field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! pim/field */ 84),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! pim/template/product/field/textarea */ 454),
        __webpack_require__(/*! summernote */ 428)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        Field,
        _,
        fieldTemplate
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            events: {
                'change .field-input:first textarea:first': 'updateModel'
            },

            /**
             * @inheritDoc
             */
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },

            /**
             * @inheritDoc
             */
            postRender: function () {
                this.$('textarea').summernote({
                    disableResizeEditor: true,
                    height: 200,
                    iconPrefix: 'icon-',
                    toolbar: [
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['para', ['ul', 'ol']],
                        ['insert', ['link']],
                        ['view', ['codeview']]
                    ]
                }).on('summernote.blur', this.updateModel.bind(this));
            },

            /**
             * @inheritDoc
             */
            updateModel: function () {
                var data = this.$('.field-input:first textarea:first').code();
                data = '' === data ? this.attribute.empty_value : data;

                this.setCurrentValue(data);
            },

            /**
             * @inheritDoc
             */
            setFocus: function () {
                this.$('.field-input:first .note-editable').trigger('focus');
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 553 */
/* unknown exports provided */
/* all exports used */
/*!*******************************************************************************!*\
  !*** ./src/Pim/Bundle/EnrichBundle/Resources/public/js/tree-manage.jstree.js ***!
  \*******************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! backbone */ 6),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! oro/loading-mask */ 15),
        __webpack_require__(/*! oro/error */ 85),
        __webpack_require__(/*! pim/ui */ 92),
        __webpack_require__(/*! jquery.jstree */ 46),
        __webpack_require__(/*! jstree/jquery.jstree.tree_selector */ 90)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, _, Backbone, Routing, LoadingMask, OroError, UI) {
        'use strict';

        return function (elementId, prefixRoute) {
            var $el = $(elementId);
            if (!$el || !$el.length || !_.isObject($el)) {
                throw new Error('Unable to instantiate tree on this element');
            }
            var selectedNode       = $el.attr('data-node-id') || -1;
            var selectedTree       = $el.attr('data-tree-id') || -1;
            var selectedNodeOrTree = selectedNode in [0, -1] ? selectedTree : selectedNode;
            var preventFirst       = selectedNode > 0;
            var loadingMask        = new LoadingMask();

            loadingMask.render().$el.appendTo($('#container'));

            this.config = {
                core: {
                    animation: 200
                },
                plugins: [
                    'tree_selector',
                    'themes',
                    'json_data',
                    'ui',
                    'crrm',
                    'types'
                ],
                contextmenu: {
                    items: {
                        create: {
                            label: _.__('jstree.create')
                        },
                        ccp: false,
                        rename: false,
                        remove: false
                    }
                },
                tree_selector: {
                    ajax: {
                        url: Routing.generate(
                            prefixRoute + '_categorytree_listtree',
                            {
                                _format: 'json',
                                select_node_id: selectedNodeOrTree,
                                context: 'manage',
                                with_items_count: 0
                            }
                        )
                    },
                    auto_open_root: true,
                    node_label_field: 'label',
                    no_tree_message: _.__('jstree.no_tree'),
                    preselect_node_id: selectedNode
                },
                themes: {
                    dots: true,
                    icons: true
                },
                json_data: {
                    ajax: {
                        url: Routing.generate(
                            prefixRoute + '_categorytree_children',
                            {
                                _format: 'json',
                                context: 'manage'
                            }
                        ),
                        data: function (node) {
                            // the result is fed to the AJAX request `data` option
                            var id = null;

                            if (node && node !== -1 && node.attr) {
                                id = node.attr('id').replace('node_', '');
                            } else {
                                id = -1;
                            }

                            return {
                                id: id,
                                select_node_id: selectedNode,
                                with_items_count: 0
                            };
                        }
                    }
                },
                types: {
                    max_depth: -2,
                    max_children: -2,
                    valid_children: ['folder'],
                    types: {
                        'default': {
                            valid_children: 'folder'
                        }
                    }
                },
                ui: {
                    select_limit: 1,
                    select_multiple_modifier: false
                }
            };
            if ($el.attr('data-editable')) {
                this.config.plugins.push('dnd');
            }
            if ($el.attr('data-creatable')) {
                this.config.plugins.push('contextmenu');
            }
            this.init = function () {
                $el.jstree(this.config).bind('move_node.jstree', function (e, data) {
                    var this_jstree = $.jstree._focused();
                    data.rslt.o.each(function (i) {
                        $.ajax({
                            async: false,
                            type: 'POST',
                            url: Routing.generate(prefixRoute + '_categorytree_movenode'),
                            data: {
                                id: $(this).attr('id').replace('node_', ''),
                                parent: data.rslt.cr === -1 ? 1 : data.rslt.np.attr('id').replace('node_', ''),
                                prev_sibling: this_jstree._get_prev(this, true) ?
                                    this_jstree._get_prev(this, true).attr('id').replace('node_', '') : null,
                                position: data.rslt.cp + i,
                                code: data.rslt.name,
                                copy: data.rslt.cy ? 1 : 0
                            },
                            success: function (r) {
                                if (!r.status) {
                                    this_jstree.rollback(data.rlbk);
                                } else {
                                    $(data.rslt.oc).attr('id', r.id);
                                    if (data.rslt.cy && $(data.rslt.oc).children('UL').length) {
                                        data.inst.refresh(data.inst._get_parent(data.rslt.oc));
                                    }
                                }
                            }
                        });
                    });
                }).bind('select_node.jstree', function (e, data) {
                    if (!$el.attr('data-editable')) {
                        return;
                    }
                    var id  = data.rslt.obj.attr('id').replace('node_', '');
                    var url = Routing.generate(prefixRoute + '_categorytree_edit', { id: id });
                    if ('#' + url === Backbone.history.location.hash || preventFirst) {
                        preventFirst = false;

                        return;
                    }
                    loadingMask.show();
                    $.ajax({
                        async: true,
                        type: 'GET',
                        url: url + '?content=form',
                        success: function (data) {
                            if (data) {
                                $('#category-form').html(data);
                                Backbone.history.navigate('#' + url, {trigger: false});
                                UI($('#category-form'));
                                loadingMask.hide();
                            }
                        },
                        error: function (jqXHR) {
                            OroError.dispatch(null, jqXHR);
                            loadingMask.hide();
                        }
                    });
                }).bind('loaded.jstree', function (event, data) {
                    if (event.namespace === 'jstree') {
                        data.inst.get_tree_select().select2({ width: '100%' });
                    }
                }).bind('create.jstree', function (e, data) {
                    $.jstree._focused().lock();
                    var id       = data.rslt.parent.attr('id').replace('node_', '');
                    var url      = Routing.generate(prefixRoute + '_categorytree_create', { parent: id });
                    var position = data.rslt.position;
                    var label    = data.rslt.name;

                    url = url + '?label=' + label + '&position=' + position;
                    loadingMask.show();
                    $.ajax({
                        async: true,
                        type: 'GET',
                        url: url + '&content=form',
                        success: function (data) {
                            if (data) {
                                $('#category-form').html(data);
                                Backbone.history.navigate('#' + url, {trigger: false});
                                loadingMask.hide();
                            }
                        },
                        error: function (jqXHR) {
                            OroError.dispatch(null, jqXHR);
                            loadingMask.hide();
                        }
                    });
                });
            };

            this.init();
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 554 */
/* unknown exports provided */
/* all exports used */
/*!*************************************************************************************!*\
  !*** ./src/Pim/Bundle/ImportExportBundle/Resources/public/js/job-execution-view.js ***!
  \*************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! backbone */ 6), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! oro/translator */ 2)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, Backbone, _, __) {
        'use strict';
        var interval;
        var loading = false;

        var JobExecution = Backbone.Model.extend({
            path: null,
            initialize: function (params) {
                if (!_.has(params, 'path')) {
                    throw new Error('A "path" parameter is required');
                }
                this.path = params.path;
                Backbone.Model.prototype.initialize.apply(this, arguments);
            },
            url: function () {
                return this.path;
            }
        });

        var JobExecutionView = Backbone.View.extend({
            showLabel: __('job_execution.summary.display_item'),
            hideLabel: __('job_execution.summary.hide_item'),

            initialize: function (params) {
                this.showLabel            = params.showLabel || this.showLabel;
                this.hideLabel            = params.hideLabel || this.hideLabel;
                this.loadingImageSelector = params.loadingImageSelector;

                this.listenTo(this.model, 'change', this.render);
                this.model.bind('request', this.ajaxStart, this);
                this.model.bind('sync', this.ajaxComplete, this);
                this.model.bind('error', this.ajaxError, this);
            },

            ajaxStart: function () {
                loading = true;
                $(this.loadingImageSelector).removeClass('transparent');
            },

            ajaxComplete: function (model, resp) {
                $(this.loadingImageSelector).addClass('transparent');
                if (!resp.jobExecution.isRunning) {
                    clearInterval(interval);
                    interval = null;
                }
                loading = false;
            },

            ajaxError: function (model, resp, options) {
                $(this.loadingImageSelector).addClass('transparent');
                clearInterval(interval);
                interval = null;
                this.$el.html(
                    '<tr><td colspan="5"><span class="AknBadge AknBadge--important">' +
                        options.xhr.statusText +
                    '</span></td></tr>'
                );
                loading = false;
            },

            events: {
                'click a.data': 'toggleData'
            },

            toggleData: function (event) {
                event.preventDefault();

                var $link        = $(event.currentTarget);
                var displayLabel = $link.data('display-label');
                var hideLabel    = $link.data('hide-label');

                $link.siblings('table').toggleClass('hide');
                $link.text($link.text().trim() === displayLabel ? hideLabel : displayLabel);
            },

            template: _.template($('#job-execution-summary').html()),

            render: function () {
                this.$el.html(
                    this.template(
                        _.extend(
                            {
                                showLabel: this.showLabel,
                                hideLabel: this.hideLabel
                            },
                            this.model.toJSON()
                        )
                    )
                );

                return this;
            }
        });

        var JobExecutionStatusView = Backbone.View.extend({
            statusLabel: 'Status',
            initialize: function (params) {
                this.statusLabel = params.statusLabel || this.statusLabel;

                this.listenTo(this.model, 'change', this.render);
            },

            template: _.template($('#job-execution-status').html()),

            render: function () {
                this.$el.html(
                    this.template(
                        _.extend(
                            {
                                statusLabel: this.statusLabel
                            },
                            this.model.toJSON()
                        )
                    )
                );

                return this;
            }
        });

        var JobExecutionButtonsView = Backbone.View.extend({
            downloadFileRoute: null,
            executionId: null,

            initialize: function (params) {
                if (!_.has(params, 'downloadFileRoute')) {
                    throw new Error('A "downloadFileRoute" parameter is required');
                }
                if (!_.has(params, 'executionId')) {
                    throw new Error('A "executionId" parameter is required');
                }

                this.downloadFileRoute = params.downloadFileRoute;
                this.executionId       = params.executionId;

                this.listenTo(this.model, 'change', this.render);
            },

            template: _.template($('#job-execution-buttons').html()),

            render: function () {
                this.$el.html(
                    this.template(
                        _.extend(
                            {
                                downloadFileRoute: this.downloadFileRoute,
                                executionId: this.executionId
                            },
                            this.model.toJSON()
                        )
                    )
                );

                return this;
            }
        });

        var JobExecutionLogButtonView = Backbone.View.extend({
            downloadLogRoute: null,
            executionId: null,
            downloadLabel: 'Download log',

            initialize: function (params) {
                if (!_.has(params, 'downloadLogRoute')) {
                    throw new Error('A "downloadLogRoute" parameter is required');
                }
                if (!_.has(params, 'executionId')) {
                    throw new Error('A "executionId" parameter is required');
                }

                this.downloadLogRoute = params.downloadLogRoute;
                this.executionId      = params.executionId;
                this.downloadLabel    = params.downloadLabel || this.downloadLabel;

                this.listenTo(this.model, 'change', this.render);
            },

            template: _.template($('#job-execution-log-button').html()),

            render: function () {
                this.$el.html(
                    this.template(
                        _.extend(
                            {
                                downloadLogRoute: this.downloadLogRoute,
                                executionId: this.executionId,
                                downloadLabel: this.downloadLabel
                            },
                            this.model.toJSON()
                        )
                    )
                );

                return this;
            }
        });

        return {
            init: function (params) {
                if (!_.has(params, 'loadingImageSelector')) {
                    throw new Error('A "loadingImageSelector" parameter is required');
                }
                if (!_.has(params, 'refreshButtonSelector')) {
                    throw new Error('A "refreshButtonSelector" parameter is required');
                }

                var jobExecution = new JobExecution(params);
                loading = true;
                jobExecution.fetch();

                params.model = jobExecution;

                new JobExecutionView(_.extend(params, {el: params.jobExecutionSelector}));
                new JobExecutionStatusView(_.extend(params, {el: params.jobExecutionStatusSelector}));
                new JobExecutionButtonsView(_.extend(params, {el: params.jobExecutionButtonsSelector}));
                new JobExecutionLogButtonView(_.extend(params, {el: params.jobExecutionLogButtonSelector}));

                var displayRefreshLink = function () {
                    $(params.loadingImageSelector).hide();
                    $(params.refreshButtonSelector).removeClass('transparent');
                };

                interval = setInterval(function () {
                    if (!loading) {
                        jobExecution.fetch();
                    }
                }, 1000);

                // Clear interval when changing page to prevent continuing to sync object on other pages
                Backbone.Router.prototype.on('route', function () {
                    clearInterval(interval);
                });

                setTimeout(function () {
                    if (null !== interval) {
                        clearInterval(interval);
                        displayRefreshLink();
                    }
                }, 120000);
            }
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 555 */
/* unknown exports provided */
/* all exports used */
/*!******************************************************************************************!*\
  !*** ./src/Pim/Bundle/NavigationBundle/Resources/public/js/navigation/favorites/view.js ***!
  \******************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global define */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backbone */ 6), __webpack_require__(/*! oro/app */ 33), __webpack_require__(/*! oro/mediator */ 8), __webpack_require__(/*! oro/error */ 85),
    __webpack_require__(/*! oro/navigation/abstract-view */ 459), __webpack_require__(/*! oro/navigation/model */ 452), __webpack_require__(/*! oro/navigation/collection */ 460)], __WEBPACK_AMD_DEFINE_RESULT__ = function(_, Backbone, app, mediator, error,
     AbstractView, NavigationModel, NavigationCollection) {
    'use strict';

    /**
     * @export  oro/navigation/favorites/view
     * @class   oro.navigation.favorites.View
     * @extends oro.navigation.AbstractView
     */
    return AbstractView.extend({
        options: {
            el: '.favorite-button',
            tabTitle: 'Favorites',
            tabIcon: 'icon-star-empty',
            tabId: 'favorite'
        },

        events: {
            'click': 'toggleItem'
        },

        initialize: function() {
            AbstractView.prototype.initialize.apply(this, arguments);
            if (!this.options.collection) {
                /** @type {oro.navigation.Collection} */
                this.options.collection = new NavigationCollection();
            }

            this.listenTo(this.getCollection(), 'add', this.addItemToTab);
            this.listenTo(this.getCollection(), 'reset', this.addAll);
            this.listenTo(this.getCollection(), 'all', this.render);

            this.$icon = this.$('i');

            this.registerTab();
            this.cleanupTab();
            /**
             * Render links in favorites menu after hash navigation request is completed
             */
            mediator.bind(
                "hash_navigation_request:complete",
                function() {
                    this.render();
                },
                this
            );
        },

        activate: function() {
            this.$icon.closest('.AknIconButton').addClass('AknIconButton--gold');
        },

        inactivate: function() {
            this.$icon.closest('.AknIconButton').removeClass('AknIconButton--gold');
        },

        toggleItem: function(e) {
            var self = this;
            var current = this.getItemForCurrentPage();
            if (current.length) {
                _.each(current, function(item) {
                    item.destroy({
                        wait: false, // This option affects correct disabling of favorites icon
                        error: function(model, xhr, options) {
                            if (xhr.status == 404 && !app.debug) {
                                // Suppress error if it's 404 response and not debug mode
                                self.inactivate();
                            } else {
                                error.dispatch(model, xhr, options);
                            }
                        }
                    });
                });
            } else {
                var itemData = this.getNewItemData(Backbone.$(e.currentTarget));
                itemData.type = 'favorite';
                itemData.position = this.getCollection().length;
                /** @type {oro.navigation.Model} */
                var currentItem = new NavigationModel(itemData);
                this.getCollection().unshift(currentItem);
                currentItem.save();
            }
        },

        addAll: function(items) {
            items.each(function(item) {
                this.addItemToTab(item);
            }, this);
        },

        render: function() {
            this.checkTabContent();
            if (this.getItemForCurrentPage().length) {
                this.activate();
            } else {
                this.inactivate();
            }
            /**
             * Backbone event. Fired when tab is changed
             * @event tab:changed
             */
            mediator.trigger("tab:changed", this.options.tabId);
            return this;
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 556 */
/* unknown exports provided */
/* all exports used */
/*!***************************************************************************************!*\
  !*** ./src/Pim/Bundle/NavigationBundle/Resources/public/js/navigation/pinbar/view.js ***!
  \***************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backbone */ 6), __webpack_require__(/*! oro/mediator */ 8), __webpack_require__(/*! oro/navigation/abstract-view */ 459),
    __webpack_require__(/*! oro/navigation/pinbar/item-view */ 497), __webpack_require__(/*! oro/navigation/pinbar/collection */ 496), __webpack_require__(/*! oro/navigation/pinbar/model */ 461)], __WEBPACK_AMD_DEFINE_RESULT__ = function($, _, Backbone, mediator, AbstractView,
    PinbarItemView, PinbarCollection, PinbarModel) {
    'use strict';

    /**
     * @export  oro/navigation/pinbar/view
     * @class   oro.navigation.pinbar.View
     * @extends oro.navigation.AbstractView
     */
    return AbstractView.extend({
        options: {
            maxItems: 10,
            tabTitle: 'Pinbar',
            tabIcon: 'icon-folder-close',
            el: '.pin-bar',
            listBar: '.list-bar',
            minimizeButton: '.minimize-button',
            defaultUrl: '/',
            tabId: 'pinbar',
            collection: null
        },

        requireCleanup: true,
        massAdd: false,

        templates: {
            noItemsMessage: _.template($("#template-no-pins-message").html())
        },

        initialize: function() {
            AbstractView.prototype.initialize.apply(this, arguments);
            this.$listBar = this.getBackboneElement(this.options.listBar);
            this.$minimizeButton = Backbone.$(this.options.minimizeButton);
            this.$icon = this.$minimizeButton.find('i');

            if (!this.options.collection) {
                this.options.collection = new PinbarCollection();
            }

            this.listenTo(this.options.collection, 'add', function(item) {this.setItemPosition(item)});
            this.listenTo(this.options.collection, 'remove', this.onPageClose);
            this.listenTo(this.options.collection, 'reset', this.addAll);
            this.listenTo(this.options.collection, 'all', this.render);

            this.listenTo(this.options.collection, 'positionChange', this.renderItem);
            this.listenTo(this.options.collection, 'stateChange', this.handleItemStateChange);
            this.listenTo(this.options.collection, 'urlChange', this.renderItem);

            /**
             * Changing pinbar state after grid is loaded
             */
            mediator.bind(
                "grid_load:complete",
                this.updatePinbarState,
                this
            );

            /**
             * Change pinbar icon state after hash navigation request is completed
             */
            mediator.bind(
                "route_complete",
                this.checkPinbarIcon,
                this
            );

            this.$minimizeButton.click(_.bind(this.changePageState, this));

            this.registerTab();
            this.cleanup();
            this.render();
        },

        resetCollection: function() {
            this.options.collection.reset.apply(this.options.collection, arguments);
        },

        /**
         * Get backbone DOM element
         *
         * @param el
         * @return {*}
         */
        getBackboneElement: function(el) {
            return el instanceof Backbone.$ ? el : this.$(el);
        },

        /**
         * Handle item minimize/maximize state change
         *
         * @param item
         */
        handleItemStateChange: function(item) {
            if (!this.massAdd) {
                var url = null,
                    changeLocation = item.get('maximized');
                if (changeLocation) {
                    url = item.get('url');
                }
                if (url != this.getCurrentPageItemData().url) {
                    if (changeLocation) {
                        Backbone.history.navigate('#/' + url);
                    }
                    item.save(
                        null,
                        {
                            wait: true,
                            success: _.bind(function () {
                                this.checkPinbarIcon();
                            }, this)
                        }
                    );
                }
            }
        },

        checkPinbarIcon: function() {
            if (this.getItemForCurrentPage().length) {
                this.activate();
            } else {
                this.inactivate();
            }
        },

        /**
         * Handle page close
         */
        onPageClose: function(item) {
            this.checkPinbarIcon();
            this.reorder();
        },

        /**
         * Handle minimize/maximize page.
         *
         * @param e
         */
        changePageState: function(e) {
            var item = this.getItemForCurrentPage(true);
            if (item.length) {
                this.closePage(item);
            } else {
                this.minimizePage(e);
            }
        },

        /**
         * Handle minimize page.
         *
         * @param e
         */
        minimizePage: function(e) {
            mediator.trigger('pinbar_item_minimized');
            this.updatePinbarState();
            var pinnedItem = this.getItemForCurrentPage(true);
            if (pinnedItem.length) {
                _.each(pinnedItem, function(item) {
                    item.set('maximized', false);
                }, this);
            } else {
                var newItem = this.getNewItemData(Backbone.$(e.currentTarget));
                var currentItem = new PinbarModel(newItem);
                this.options.collection.unshift(currentItem);
                this.handleItemStateChange(currentItem);
            }
        },

        /**
         *  Update current page item state to use new url
         */
        updatePinbarState: function() {
            var pinnedItem = this.getItemForCurrentPage(true);
            if (pinnedItem.length) {
                var hashUrl = Backbone.history.getFragment();
                _.each(pinnedItem, function(item) {
                    if (item.get('url') !== hashUrl) {
                        item.set('url', hashUrl);
                        item.save();
                    }
                }, this);
            }
        },

        /**
         * Handle pinbar close
         *
         * @param item
         */
        closePage: function(item) {
            _.each(item, function(item) {
                item.set('remove', true);
            });
        },

        /**
         * Mass add items
         */
        addAll: function() {
            this.massAdd = true;
            this.markCurrentPageMaximized();
            this.options.collection.each(this.setItemPosition, this);
            this.massAdd = false;
        },

        /**
         * Mark current page as maximized to be able to minimize.
         */
        markCurrentPageMaximized: function()
        {
            var currentPageItems = this.getItemForCurrentPage(true);
            if (currentPageItems.length) {
                _.each(currentPageItems, function(item) {
                    item.set('maximized', new Date().toISOString());
                });
            }
        },

        /**
         * Set item position if given or reorder items.
         *
         * @param {oro.navigation.pinbar.Model} item
         * @param {number} position
         */
        setItemPosition: function(item, position) {
            if (_.isUndefined(position)) {
                this.reorder();
            } else {
                item.set({position: position});
            }
        },

        /**
         * Change position property of model based on current order
         */
        reorder: function() {
            this.options.collection.each(function(item, position) {
                item.set({position: position});
            });
        },

        activate: function() {
            this.$icon.closest('.AknIconButton').addClass('AknIconButton--gold');
        },

        inactivate: function() {
            this.$icon.closest('.AknIconButton').removeClass('AknIconButton--gold');
        },

        /**
         * Choose container and add item to it.
         *
         * @param {oro.navigation.pinbar.Model} item
         */
        renderItem: function(item) {
            var position = item.get('position');
            var type = position >= this.options.maxItems ? 'tab': 'list';

            if (item.get('display_type') != type) {
                this.cleanup();
                item.set('display_type', type);

                var view = new PinbarItemView({
                    type: type,
                    model: item
                });

                if (type == 'tab') {
                    this.addItemToTab(view, !this.massAdd);
                    /**
                     * Backbone event. Fired when tab is changed
                     * @event tab:changed
                     */
                    mediator.trigger("tab:changed", this.options.tabId);
                } else {
                    var rowEl = view.render().el;
                    if (this.massAdd || position > 0) {
                        this.$listBar.append(rowEl);
                    } else {
                        this.$listBar.prepend(rowEl);
                    }
                }
            }
        },

        /**
         * Checks if pinbar tab in 3 dots menu is used
         *
         * @return {Boolean}
         */
        needPinbarTab: function() {
            return (this.options.collection.length > this.options.maxItems);
        },

        /**
         * Clean up all pinbar items from menus
         */
        cleanup: function()
        {
            if (this.requireCleanup) {
                this.$listBar.empty();
                this.cleanupTab();
                this.requireCleanup = false;
            }
        },

        /**
         * Renders pinbar empty message if no items
         * Show/hide tabs section in ... menu on each event
         */
        render: function() {
            if (!this.massAdd) {
                if (this.options.collection.length == 0) {
                    this.requireCleanup = true;
                    this.$listBar.html(this.templates.noItemsMessage());
                    /**
                     * Backbone event. Fired when pinbar help link is shown
                     * @event pinbar_help:shown
                     */
                    mediator.trigger("pinbar_help:shown");
                }

                this.checkTabContent();
                /**
                 * Backbone event. Fired when tab is changed
                 * @event tab:changed
                 */
                mediator.trigger("tab:changed", this.options.tabId);
            }
        }
    });
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 557 */
/* unknown exports provided */
/* all exports used */
/*!*****************************************************************************!*\
  !*** ./src/Pim/Bundle/NavigationBundle/Resources/public/lib/url/url.min.js ***!
  \*****************************************************************************/
/***/ (function(module, exports) {

;var Url=(function(){"use strict";var j={protocol:'protocol',host:'hostname',port:'port',path:'pathname',query:'search',hash:'hash'},parse=function(a,b){var d=document,link=d.createElement('a'),b=b||d.location.href,auth=b.match(/\/\/(.*?)(?::(.*?))?@/)||[];link.href=b;for(var i in j){a[i]=link[j[i]]||''}a.protocol=a.protocol.replace(/:$/,'');a.query=a.query.replace(/^\?/,'');a.hash=a.hash.replace(/^#/,'');a.user=auth[1]||'';a.pass=auth[2]||'';parseQs(a)},decode=function(s){s=s.replace(/\+/g,' ');s=s.replace(/%([EF][0-9A-F])%([89AB][0-9A-F])%([89AB][0-9A-F])/g,function(a,b,c,d){var e=parseInt(b,16)-0xE0,n2=parseInt(c,16)-0x80;if(e==0&&n2<32){return a}var f=parseInt(d,16)-0x80,n=(e<<12)+(n2<<6)+f;if(n>0xFFFF){return a}return String.fromCharCode(n)});s=s.replace(/%([CD][0-9A-F])%([89AB][0-9A-F])/g,function(a,b,c){var d=parseInt(b,16)-0xC0;if(d<2){return a}var e=parseInt(c,16)-0x80;return String.fromCharCode((d<<6)+e)});s=s.replace(/%([0-7][0-9A-F])/g,function(a,b){return String.fromCharCode(parseInt(b,16))});return s},parseQs=function(g){var h=g.query;g.query=new(function(c){var d=/([^=&]+)(=([^&]*))?/g,match;while((match=d.exec(c))){var f=decodeURIComponent(match[1].replace(/\+/g,' ')),value=match[3]?decode(match[3]):'';if(this[f]!==undefined){if(!(this[f]instanceof Array)){this[f]=[this[f]]}this[f].push(value)}else{this[f]=value}}this.toString=function(){var s='',e=encodeURIComponent;for(var i in this){if(this[i]instanceof Function){continue}if(this[i]instanceof Array){var a=this[i].length;if(a){for(var b=0;b<a;b++){s+=s?'&':'';s+=e(i)+'='+e(this[i][b])}}else{s+=(s?'&':'')+e(i)+'='}}else{s+=s?'&':'';s+=e(i)+'='+e(this[i])}}return s}})(h)};return function(a){this.toString=function(){return((this.protocol&&(this.protocol+'://'))+(this.user&&(this.user+(this.pass&&(':'+this.pass))+'@'))+(this.host&&this.host)+(this.port&&(':'+this.port))+(this.path&&this.path)+(this.query.toString()&&('?'+this.query))+(this.hash&&('#'+this.hash)))};parse(this,a)}}());


/***/ }),
/* 558 */
/* unknown exports provided */
/* all exports used */
/*!********************************************************************************!*\
  !*** ./src/Pim/Bundle/NotificationBundle/Resources/public/js/notifications.js ***!
  \********************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! backbone */ 6),
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! pim/notification-list */ 499),
        __webpack_require__(/*! pim/indicator */ 498),
        __webpack_require__(/*! pim/template/notification/notification */ 482),
        __webpack_require__(/*! pim/template/notification/notification-footer */ 480)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (Backbone, $, _, Routing, NotificationList, Indicator, notificationTpl, notificationFooterTpl) {
        'use strict';

        var Notifications = Backbone.View.extend({
            el: '#header-notification-widget',

            options: {
                imgUrl:                 '',
                loadingText:            null,
                noNotificationsMessage: null,
                markAsReadMessage:      null,
                indicatorBaseClass:     'AknBell-count',
                indicatorEmptyClass:    'AknBell-count--hidden',
                refreshInterval:        30000
            },

            freezeCount: false,

            refreshTimeout: null,

            refreshLocked: false,

            template: _.template(notificationTpl),

            footerTemplate: _.template(notificationFooterTpl),

            events: {
                'click a.dropdown-toggle':   'onOpen',
                'click button.mark-as-read': 'markAllAsRead'
            },

            markAllAsRead: function (e) {
                e.stopPropagation();
                e.preventDefault();

                $.ajax({
                    type: 'POST',
                    url: Routing.generate('pim_notification_notification_mark_viewed'),
                    async: true
                });

                this.collection.trigger('mark_as_read', null);
                _.each(this.collection.models, function (model) {
                    model.set('viewed', true);
                });
            },

            initialize: function (opts) {
                this.options = _.extend({}, this.options, opts);
                this.collection = new NotificationList();
                this.indicator  = new Indicator({
                    el: this.$('.AknBell-countContainer'),
                    value: 0,
                    className: this.options.indicatorBaseClass,
                    emptyClass: this.options.indicatorEmptyClass
                });

                this.collection.on('load:unreadCount', function (count, reset) {
                    this.scheduleRefresh();
                    if (this.freezeCount) {
                        this.freezeCount = false;

                        return;
                    }
                    if (this.indicator.get('value') !== count) {
                        this.indicator.set('value', count);
                        if (reset) {
                            this.collection.hasMore = true;
                            this.collection.reset();
                            this.renderFooter();
                        }
                    }
                }, this);

                this.collection.on('mark_as_read', function (id) {
                    var value = null === id ? 0 : this.indicator.get('value') - 1;
                    this.indicator.set('value', value);
                    if (0 === value) {
                        this.renderFooter();
                    }
                    if (null !== id) {
                        this.freezeCount = true;
                    }
                }, this);

                this.collection.on('loading:start loading:finish remove', this.renderFooter, this);

                this.render();

                this.scheduleRefresh();
            },

            scheduleRefresh: function () {
                if (this.refreshLocked) {
                    return;
                }
                if (null !== this.refreshTimeout) {
                    clearTimeout(this.refreshTimeout);
                }

                this.refreshTimeout = setTimeout(_.bind(function () {
                    this.refreshLocked = true;
                    $.getJSON(Routing.generate('pim_notification_notification_count_unread'))
                        .then(_.bind(function (count) {
                            this.refreshLocked = false;
                            this.collection.trigger('load:unreadCount', count, true);
                        }, this));
                }, this), this.options.refreshInterval);
            },

            onOpen: function () {
                if (!this.collection.length) {
                    this.collection.loadNotifications();
                }
            },

            render: function () {
                this.setElement($('#header-notification-widget'));
                this.$el.html(this.template());
                this.collection.setElement(this.$('ul'));
                this.indicator.setElement(this.$('.AknBell-countContainer'));
                this.renderFooter();
            },

            renderFooter: function () {
                this.$('p').remove();

                this.$('ul').append(
                    this.footerTemplate({
                        options:          this.options,
                        loading:          this.collection.loading,
                        hasNotifications: this.collection.length > 0,
                        hasMore:          this.collection.hasMore,
                        hasUnread:        this.indicator.get('value') > 0
                    })
                );
            }
        });

        var notifications;

        return {
            init: function (options) {
                if (notifications) {
                    notifications.render();
                } else {
                    notifications = new Notifications(options);
                }
                if (_.has(options, 'unreadCount')) {
                    notifications.collection.trigger('load:unreadCount', options.unreadCount, true);
                }
            }
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 559 */
/* unknown exports provided */
/* all exports used */
/*!**************************************************************************************************************!*\
  !*** ./src/Pim/Bundle/ReferenceDataBundle/Resources/public/js/product/field/reference-multi-select-field.js ***!
  \**************************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! pim/multi-select-field */ 492),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! pim/fetcher-registry */ 4)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (_, MultiselectField, Routing, FetcherRegistry) {
        return MultiselectField.extend({
            fieldType: 'reference-multi-select',
            getTemplateContext: function () {
                return MultiselectField.prototype.getTemplateContext.apply(this, arguments)
                    .then(function (templateContext) {
                        templateContext.userCanAddOption = false;

                        return templateContext;
                    });
            },
            getChoiceUrl: function () {
                return FetcherRegistry.getFetcher('reference-data-configuration').fetchAll()
                    .then(_.bind(function (config) {
                        return Routing.generate(
                            'pim_ui_ajaxentity_list',
                            {
                                'class': config[this.attribute.reference_data_name].class,
                                'dataLocale': this.context.locale,
                                'collectionId': this.attribute.id,
                                'options': {'type': 'code'}
                            }
                        );
                    }, this));
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 560 */
/* unknown exports provided */
/* all exports used */
/*!***************************************************************************************************************!*\
  !*** ./src/Pim/Bundle/ReferenceDataBundle/Resources/public/js/product/field/reference-simple-select-field.js ***!
  \***************************************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! pim/simple-select-field */ 493),
        __webpack_require__(/*! routing */ 7),
        __webpack_require__(/*! pim/fetcher-registry */ 4)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (_, SimpleselectField, Routing, FetcherRegistry) {
        return SimpleselectField.extend({
            fieldType: 'reference-simple-select',
            getTemplateContext: function () {
                return SimpleselectField.prototype.getTemplateContext.apply(this, arguments)
                    .then(function (templateContext) {
                        templateContext.userCanAddOption = false;

                        return templateContext;
                    });
            },
            getChoiceUrl: function () {
                return FetcherRegistry.getFetcher('reference-data-configuration').fetchAll()
                    .then(_.bind(function (config) {
                        return Routing.generate(
                            'pim_ui_ajaxentity_list',
                            {
                                'class': config[this.attribute.reference_data_name].class,
                                'dataLocale': this.context.locale,
                                'collectionId': this.attribute.id,
                                'options': {'type': 'code'}
                            }
                        );
                    }, this));
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 561 */
/* unknown exports provided */
/* all exports used */
/*!*******************************************************************!*\
  !*** ./src/Pim/Bundle/UIBundle/Resources/public/js/form/state.js ***!
  \*******************************************************************/
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function($) {var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global confirm */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backbone */ 6), __webpack_require__(/*! oro/mediator */ 8), __webpack_require__(/*! oro/translator */ 2)], __WEBPACK_AMD_DEFINE_RESULT__ = function (_, Backbone, mediator, __) {
        'use strict';

        var $ = Backbone.$;

        var formState = function () {
            this.initialize.apply(this, arguments);
        };

        _.extend(formState.prototype, {
            UNLOAD_EVENT: 'beforeunload.configFormState',
            LOAD_EVENT: 'ready.configFormState',
            FORM_SELECTOR: '.system-configuration-container form:first',
            CONFIRMATION_MESSAGE: __('You have unsaved changes, are you sure that you want to leave?'),

            data: null,

            initialize: function () {
                mediator.once('hash_navigation_request:start', this._onDestroyHandler, this);

                $(window).on(this.LOAD_EVENT, _.bind(this._collectHandler, this));
                this._collectHandler();

                $(window).on(this.UNLOAD_EVENT, _.bind(function () {
                    if (this.isChanged()) {
                        return this.CONFIRMATION_MESSAGE;
                    }
                }, this));
                mediator.on('hash_navigation_click', this._confirmHashChange, this);
            },

            /**
             * Check is form changed
             *
             * @returns {boolean}
             */
            isChanged: function () {
                if (!_.isNull(this.data)) {
                    return this.data !== this.getState();
                }

                return false;
            },

            /**
             * Collect form state
             *
             * @returns {*}
             */
            getState: function () {
                var form = $(this.FORM_SELECTOR);

                if (form.length) {
                    return JSON.stringify(
                        _.reject(
                            $(this.FORM_SELECTOR).serializeArray(),
                            function (el) {
                                return el.name === 'input_action';
                            }
                        )
                    );
                }

                return false;
            },

            /**
             * Hash change event handler
             *
             * @param event
             * @private
             */
            _confirmHashChange: function (event) {
                if (this.isChanged()) {
                    event.stoppedProcess = !confirm(this.CONFIRMATION_MESSAGE);
                }
            },

            /**
             * Collecting event handler
             *
             * @private
             */
            _collectHandler: function () {
                this.data = this.getState();
            },

            /**
             * Destroys event handlers
             *
             * @private
             */
            _onDestroyHandler: function () {
                if (_.isNull(this.data)) {
                    // data was not collected disable listener
                    mediator.off('hash_navigation_request:complete', this._collectHandler, this);
                } else {
                    this.data = null;
                }
                mediator.off('hash_navigation_click', this._confirmHashChange, this);
                $(window).off(this.UNLOAD_EVENT);
                $(document).off(this.LOAD_EVENT);
            }
        });

        return formState;
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));

/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(/*! jquery */ 1)))

/***/ }),
/* 562 */
/* unknown exports provided */
/* all exports used */
/*!******************************************************************************************!*\
  !*** ./src/Pim/Bundle/UIBundle/Resources/public/js/form/system/group/loading-message.js ***!
  \******************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

"use strict";
var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;

!(__WEBPACK_AMD_DEFINE_ARRAY__ = [
        __webpack_require__(/*! underscore */ 0),
        __webpack_require__(/*! jquery */ 1),
        __webpack_require__(/*! pim/form */ 3),
        __webpack_require__(/*! pim/template/system/group/loading-message */ 483),
        __webpack_require__(/*! bootstrap.bootstrapswitch */ 43)
    ], __WEBPACK_AMD_DEFINE_RESULT__ = function (
        _,
        $,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            events: {
                'change input[type="checkbox"]': 'updateBoolean',
                'change textarea': 'updateText'
            },
            isGroup: true,
            label: _.__('oro_config.form.config.group.loading_message.title'),
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    'loading_message_enabled': this.getFormData().pim_ui___loading_message_enabled.value,
                    'loading_messages': this.getFormData().pim_ui___loading_messages.value
                }));

                this.$el.find('.switch').bootstrapSwitch();

                this.delegateEvents();

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Update model after value change
             *
             * @param {Event}
             */
            updateBoolean: function (event) {
                var data = this.getFormData();
                data.pim_ui___loading_message_enabled.value = $(event.target).prop('checked') ? '1' : '0';
                this.setData(data);
            },

            /**
             * Update model after value change
             *
             * @param {Event}
             */
            updateText: function (event) {
                var data = this.getFormData();
                data.pim_ui___loading_messages.value = $(event.target).val();
                this.setData(data);
            }
        });
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 563 */
/* unknown exports provided */
/* all exports used */
/*!*********************************************************************!*\
  !*** ./src/Pim/Bundle/UIBundle/Resources/public/js/jquery-setup.js ***!
  \*********************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($) {
    'use strict';
    $.ajaxSetup({
        headers: {
            'X-CSRF-Header': 1
        }
    });
    // $.expr[':'].parents = function (a, i, m) {
    //     return $(a).parents(m[3]).length < 1;
    // };
    // used to indicate app's activity, such as AJAX request or redirection, etc.
    $.isActive = $.proxy(function (flag) {
        if ($.type(flag) !== 'undefined') {
            this.active = flag;
        }

        return $.active || this.active;
    }, {active: false});

    return $;
}.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 564 */
/* unknown exports provided */
/* all exports used */
/*!*******************************************************************************!*\
  !*** ./src/Pim/Bundle/UIBundle/Resources/public/js/pim-formupdatelistener.js ***!
  \*******************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/* global console */
!(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1), __webpack_require__(/*! backbone */ 6), __webpack_require__(/*! pim/dialog */ 14), __webpack_require__(/*! pim/router */ 13)], __WEBPACK_AMD_DEFINE_RESULT__ = function ($, Backbone, Dialog, router) {
        'use strict';

        return function ($form) {
            this.updated = false;
            var message = $form.attr('data-updated-message');
            if (!message) {
                console.warn('FormUpdateListener: message not provided.');

                return;
            }
            var title = $form.attr('data-updated-title');
            var self  = this;

            var formUpdated = function (e) {
                var $target = $(e.target);
                if ($target.parents('div.filter-box').length ||
                    $target.parents('ul.icons-holder').length ||
                    $target.hasClass('exclude')) {

                    return;
                }
                self.updated = true;
                $('#entity-updated').show().css('opacity', 1);

                $form.off('change', formUpdated);
                $(document).off('click', '#' + $form.attr('id') + ' ins.jstree-checkbox', formUpdated);

                $form.find('button[type="submit"]').on('click', function () {
                    self.updated = false;
                });

                $(window).on('beforeunload', function () {
                    if (self.updated) {
                        return message;
                    }
                });
            };

            var linkClicked = function (e) {
                e.stopImmediatePropagation();
                e.preventDefault();
                var url      = $(this).attr('href');
                var doAction = function () {
                    router.redirect(url);
                };
                if (!self.updated) {
                    doAction();
                } else {
                    Dialog.confirm(message, title, doAction);
                }

                return false;
            };

            $form.on('change', formUpdated);
            $(document).on('click', '#' + $form.attr('id') + ' ins.jstree-checkbox', formUpdated);
            $form.on('refresh', function () {
                self.updated = false;
                $('#entity-updated').css('opacity', 0).hide();
            });

            $('a[href^="/"]:not(".no-hash")').off('click').on('click', linkClicked);
            $form.on('click', 'a[href^="/"]:not(".no-hash")', linkClicked);

            Backbone.Router.prototype.on('route', function () {
                $('a[href^="/"]:not(".no-hash")').off('click', linkClicked);
                $(window).off('beforeunload');
            });
        };
    }.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));


/***/ }),
/* 565 */
/* unknown exports provided */
/* all exports used */
/*!**********************************************************************************!*\
  !*** ./src/Pim/Bundle/UIBundle/Resources/public/lib/backbone.bootstrap-modal.js ***!
  \**********************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(_, Backbone) {var __WEBPACK_AMD_DEFINE_RESULT__;/**
 * Bootstrap Modal wrapper for use with Backbone.
 *
 * Takes care of instantiation, manages multiple modals,
 * adds several options and removes the element from the DOM when closed
 *
 * @author Charles Davison <charlie@powmedia.co.uk>
 *
 * Events:
 * shown: Fired when the modal has finished animating in
 * hidden: Fired when the modal has finished animating out
 * cancel: The user dismissed the modal
 * ok: The user clicked OK
 */
(function($, _, Backbone) {

  //Set custom template settings
  var _interpolateBackup = _.templateSettings;
  _.templateSettings = {
    interpolate: /\{\{(.+?)\}\}/g,
    evaluate: /<%([\s\S]+?)%>/g
  }

  var template = _.template('\
    <% if (title) { %>\
      <div class="modal-header">\
        <% if (allowCancel) { %>\
          <a class="close">×</a>\
        <% } %>\
        <h3>{{title}}</h3>\
      </div>\
    <% } %>\
    <div class="modal-body">{{content}}</div>\
    <div class="AknButtonList AknButtonList--right modal-footer">\
      <% if (allowCancel) { %>\
        <% if (cancelText) { %>\
          <a href="#" title="{{cancelText}}" class="AknButtonList-item AknButton AknButton--withIcon AknButton--grey cancel icons-holder-text">\
            <i class="AknButton-icon icon-chevron-left"></i>\
            {{cancelText}}\
          </a>\
        <% } %>\
      <% } %>\
      <a href="#" title="{{okText}}" class="AknButtonList-item AknButton AknButton--withIcon AknButton--apply ok icons-holder-text">\
        <i class="AknButton-icon icon-ok"></i>\
        {{okText}}\
      </a>\
    </div>\
  ');

  //Reset to users' template settings
  _.templateSettings = _interpolateBackup;


  var Modal = Backbone.View.extend({

    className: 'modal',

    events: {
      'click .close': function(event) {
        event.preventDefault();

        this.trigger('cancel');

        if (this.options.content && this.options.content.trigger) {
          this.options.content.trigger('cancel', this);
        }
      },
      'click .cancel': function(event) {
        event.preventDefault();

        this.trigger('cancel');

        if (this.options.content && this.options.content.trigger) {
          this.options.content.trigger('cancel', this);
        }
      },
      'click .ok': function(event) {
        event.preventDefault();

        this.trigger('ok');

        if (this.options.content && this.options.content.trigger) {
          this.options.content.trigger('ok', this);
        }

        if (this.options.okCloses) {
          this.close();
        }
      }
    },

    /**
     * Creates an instance of a Bootstrap Modal
     *
     * @see http://twitter.github.com/bootstrap/javascript.html#modals
     *
     * @param {Object} options
     * @param {String|View} [options.content] Modal content. Default: none
     * @param {String} [options.title]        Title. Default: none
     * @param {String} [options.okText]       Text for the OK button. Default: 'OK'
     * @param {String} [options.cancelText]   Text for the cancel button. Default: 'Cancel'. If passed a falsey value, the button will be removed
     * @param {Boolean} [options.allowCancel  Whether the modal can be closed, other than by pressing OK. Default: true
     * @param {Boolean} [options.escape]      Whether the 'esc' key can dismiss the modal. Default: true, but false if options.cancellable is true
     * @param {Boolean} [options.animate]     Whether to animate in/out. Default: false
     * @param {Function} [options.template]   Compiled underscore template to override the default one
     */
    initialize: function(options) {
      this.options = _.extend({
        title: null,
        okText: 'OK',
        focusOk: true,
        okCloses: true,
        cancelText: 'Cancel',
        allowCancel: true,
        escape: true,
        animate: false,
        template: template
      }, options);
    },

    /**
     * Creates the DOM element
     *
     * @api private
     */
    render: function() {
      var $el = this.$el,
          options = this.options,
          content = options.content;

      //Create the modal container
      $el.html(options.template(options));

      var $content = this.$content = $el.find('.modal-body')

      //Insert the main content if it's a view
      if (content.$el) {
        content.render();
        $el.find('.modal-body').html(content.$el);
      }

      if (options.animate) $el.addClass('fade');

      this.isRendered = true;

      return this;
    },

    /**
     * Renders and shows the modal
     *
     * @param {Function} [cb]     Optional callback that runs only when OK is pressed.
     */
    open: function(cb) {
      if (!this.isRendered) this.render();
      this.delegateEvents();

      var self = this,
          $el = this.$el;

      //Create it
      $el.modal(_.extend({
        keyboard: this.options.allowCancel,
        backdrop: this.options.allowCancel ? true : 'static'
      }, this.options.modalOptions));

      //Focus OK button
      $el.one('shown', function() {
        if (self.options.focusOk) {
          $el.find('.btn.ok').focus();
        }

        if (self.options.content && self.options.content.trigger) {
          self.options.content.trigger('shown', self);
        }

        self.trigger('shown');
      });

      //Adjust the modal and backdrop z-index; for dealing with multiple modals
      var numModals = Modal.count,
          $backdrop = $('.modal-backdrop:eq('+numModals+')'),
          backdropIndex = parseInt($backdrop.css('z-index'), 10),
          elIndex = parseInt($backdrop.css('z-index'), 10) + 1;

      $backdrop.css('z-index', backdropIndex + numModals);
      this.$el.css('z-index', elIndex + numModals);

      if (this.options.allowCancel) {
        $backdrop.one('click', function() {
          if (self.options.content && self.options.content.trigger) {
            self.options.content.trigger('cancel', self);
          }

          self.trigger('cancel');
        });

        $(document).one('keyup.dismiss.modal', function (e) {
          e.which == 27 && self.trigger('cancel');

          if (self.options.content && self.options.content.trigger) {
            e.which == 27 && self.options.content.trigger('shown', self);
          }
        });
      }

      this.once('cancel', function() {
        self.close();
      });

      Modal.count++;

      //Run callback on OK if provided
      if (cb) {
        self.on('ok', cb);
      }

      return this;
    },

    /**
     * Closes the modal
     */
    close: function() {
      var self = this,
          $el = this.$el;

      //Check if the modal should stay open
      if (this._preventClose) {
        this._preventClose = false;
        return;
      }

      $el.one('hidden', function onHidden(e) {
        // Ignore events propagated from interior objects, like bootstrap tooltips
        if(e.target !== e.currentTarget){
          return $el.one('hidden', onHidden);
        }
        self.remove();

        if (self.options.content && self.options.content.trigger) {
          self.options.content.trigger('hidden', self);
        }

        self.trigger('hidden');
      });

      $el.modal('hide');

      Modal.count--;
    },

    /**
     * Stop the modal from closing.
     * Can be called from within a 'close' or 'ok' event listener.
     */
    preventClose: function() {
      this._preventClose = true;
    }
  }, {
    //STATICS

    //The number of modals on display
    count: 0
  });


  //EXPORTS
  //CommonJS
  if ("function" == 'function' && typeof module !== 'undefined' && exports) {
    module.exports = Modal;
  }

  //AMD / RequireJS
  if (true) {
    return !(__WEBPACK_AMD_DEFINE_RESULT__ = function() {
      Backbone.BootstrapModal = Modal;
    }.call(exports, __webpack_require__, exports, module),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__))
  }

  //Regular; add to Backbone.Bootstrap.Modal
  else {
    Backbone.BootstrapModal = Modal;
  }

})(jQuery, _, Backbone);

/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(/*! underscore */ 0), __webpack_require__(/*! backbone */ 6)))

/***/ }),
/* 566 */
/* unknown exports provided */
/* all exports used */
/*!***********************************************************************!*\
  !*** ./src/Pim/Bundle/UIBundle/Resources/public/lib/base64/base64.js ***!
  \***********************************************************************/
/***/ (function(module, exports) {

function base64_encode (data) {
    // http://kevin.vanzonneveld.net
    // +   original by: Tyler Akins (http://rumkin.com)
    // +   improved by: Bayron Guevara
    // +   improved by: Thunder.m
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Pellentesque Malesuada
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Rafał Kukawski (http://kukawski.pl)
    // *     example 1: base64_encode('Kevin van Zonneveld');
    // *     returns 1: 'S2V2aW4gdmFuIFpvbm5ldmVsZA=='
    // mozilla has this native
    // - but breaks in 2.0.0.12!
    //if (typeof this.window['btoa'] == 'function') {
    //    return btoa(data);
    //}
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
        ac = 0,
        enc = "",
        tmp_arr = [];

    if (!data) {
        return data;
    }

    do { // pack three octets into four hexets
        o1 = data.charCodeAt(i++);
        o2 = data.charCodeAt(i++);
        o3 = data.charCodeAt(i++);

        bits = o1 << 16 | o2 << 8 | o3;

        h1 = bits >> 18 & 0x3f;
        h2 = bits >> 12 & 0x3f;
        h3 = bits >> 6 & 0x3f;
        h4 = bits & 0x3f;

        // use hexets to index into b64, and append result to encoded string
        tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
    } while (i < data.length);

    enc = tmp_arr.join('');

    var r = data.length % 3;

    return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);
}

function base64_decode (data) {
    // http://kevin.vanzonneveld.net
    // +   original by: Tyler Akins (http://rumkin.com)
    // +   improved by: Thunder.m
    // +      input by: Aman Gupta
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   bugfixed by: Pellentesque Malesuada
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==');
    // *     returns 1: 'Kevin van Zonneveld'
    // mozilla has this native
    // - but breaks in 2.0.0.12!
    //if (typeof this.window['atob'] == 'function') {
    //    return atob(data);
    //}
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
        ac = 0,
        dec = "",
        tmp_arr = [];

    if (!data) {
        return data;
    }

    data += '';

    do { // unpack four hexets into three octets using index points in b64
        h1 = b64.indexOf(data.charAt(i++));
        h2 = b64.indexOf(data.charAt(i++));
        h3 = b64.indexOf(data.charAt(i++));
        h4 = b64.indexOf(data.charAt(i++));

        bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

        o1 = bits >> 16 & 0xff;
        o2 = bits >> 8 & 0xff;
        o3 = bits & 0xff;

        if (h3 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1);
        } else if (h4 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1, o2);
        } else {
            tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
        }
    } while (i < data.length);

    dec = tmp_arr.join('');

    return dec;
}

/***/ }),
/* 567 */
/* unknown exports provided */
/* all exports used */
/*!*********************************************************************************************!*\
  !*** ./src/Pim/Bundle/UIBundle/Resources/public/lib/dropzonejs/dist/dropzone-amd-module.js ***!
  \*********************************************************************************************/
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;// Uses AMD or browser globals to create a jQuery plugin.
(function (factory) {
  if (true) {
      // AMD. Register as an anonymous module.
      !(__WEBPACK_AMD_DEFINE_ARRAY__ = [__webpack_require__(/*! jquery */ 1)], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
  } else {
      // Browser globals
      factory(jQuery);
  }
} (function (jQuery) {
    var module = { exports: { } }; // Fake component


/*
 *
 * More info at [www.dropzonejs.com](http://www.dropzonejs.com)
 *
 * Copyright (c) 2012, Matias Meno
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */

(function() {
  var Dropzone, Emitter, camelize, contentLoaded, detectVerticalSquash, drawImageIOSFix, noop, without,
    __slice = [].slice,
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  noop = function() {};

  Emitter = (function() {
    function Emitter() {}

    Emitter.prototype.addEventListener = Emitter.prototype.on;

    Emitter.prototype.on = function(event, fn) {
      this._callbacks = this._callbacks || {};
      if (!this._callbacks[event]) {
        this._callbacks[event] = [];
      }
      this._callbacks[event].push(fn);
      return this;
    };

    Emitter.prototype.emit = function() {
      var args, callback, callbacks, event, _i, _len;
      event = arguments[0], args = 2 <= arguments.length ? __slice.call(arguments, 1) : [];
      this._callbacks = this._callbacks || {};
      callbacks = this._callbacks[event];
      if (callbacks) {
        for (_i = 0, _len = callbacks.length; _i < _len; _i++) {
          callback = callbacks[_i];
          callback.apply(this, args);
        }
      }
      return this;
    };

    Emitter.prototype.removeListener = Emitter.prototype.off;

    Emitter.prototype.removeAllListeners = Emitter.prototype.off;

    Emitter.prototype.removeEventListener = Emitter.prototype.off;

    Emitter.prototype.off = function(event, fn) {
      var callback, callbacks, i, _i, _len;
      if (!this._callbacks || arguments.length === 0) {
        this._callbacks = {};
        return this;
      }
      callbacks = this._callbacks[event];
      if (!callbacks) {
        return this;
      }
      if (arguments.length === 1) {
        delete this._callbacks[event];
        return this;
      }
      for (i = _i = 0, _len = callbacks.length; _i < _len; i = ++_i) {
        callback = callbacks[i];
        if (callback === fn) {
          callbacks.splice(i, 1);
          break;
        }
      }
      return this;
    };

    return Emitter;

  })();

  Dropzone = (function(_super) {
    var extend, resolveOption;

    __extends(Dropzone, _super);

    Dropzone.prototype.Emitter = Emitter;


    /*
    This is a list of all available events you can register on a dropzone object.
    
    You can register an event handler like this:
    
        dropzone.on("dragEnter", function() { });
     */

    Dropzone.prototype.events = ["drop", "dragstart", "dragend", "dragenter", "dragover", "dragleave", "addedfile", "removedfile", "thumbnail", "error", "errormultiple", "processing", "processingmultiple", "uploadprogress", "totaluploadprogress", "sending", "sendingmultiple", "success", "successmultiple", "canceled", "canceledmultiple", "complete", "completemultiple", "reset", "maxfilesexceeded", "maxfilesreached", "queuecomplete"];

    Dropzone.prototype.defaultOptions = {
      url: null,
      method: "post",
      withCredentials: false,
      parallelUploads: 2,
      uploadMultiple: false,
      maxFilesize: 256,
      paramName: "file",
      createImageThumbnails: true,
      maxThumbnailFilesize: 10,
      thumbnailWidth: 120,
      thumbnailHeight: 120,
      filesizeBase: 1000,
      maxFiles: null,
      filesizeBase: 1000,
      params: {},
      clickable: true,
      ignoreHiddenFiles: true,
      acceptedFiles: null,
      acceptedMimeTypes: null,
      autoProcessQueue: true,
      autoQueue: true,
      addRemoveLinks: false,
      previewsContainer: null,
      capture: null,
      dictDefaultMessage: "Drop files here to upload",
      dictFallbackMessage: "Your browser does not support drag'n'drop file uploads.",
      dictFallbackText: "Please use the fallback form below to upload your files like in the olden days.",
      dictFileTooBig: "File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB.",
      dictInvalidFileType: "You can't upload files of this type.",
      dictResponseError: "Server responded with {{statusCode}} code.",
      dictCancelUpload: "Cancel upload",
      dictCancelUploadConfirmation: "Are you sure you want to cancel this upload?",
      dictRemoveFile: "Remove file",
      dictRemoveFileConfirmation: null,
      dictMaxFilesExceeded: "You can not upload any more files.",
      accept: function(file, done) {
        return done();
      },
      init: function() {
        return noop;
      },
      forceFallback: false,
      fallback: function() {
        var child, messageElement, span, _i, _len, _ref;
        this.element.className = "" + this.element.className + " dz-browser-not-supported";
        _ref = this.element.getElementsByTagName("div");
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          child = _ref[_i];
          if (/(^| )dz-message($| )/.test(child.className)) {
            messageElement = child;
            child.className = "dz-message";
            continue;
          }
        }
        if (!messageElement) {
          messageElement = Dropzone.createElement("<div class=\"dz-message\"><span></span></div>");
          this.element.appendChild(messageElement);
        }
        span = messageElement.getElementsByTagName("span")[0];
        if (span) {
          span.textContent = this.options.dictFallbackMessage;
        }
        return this.element.appendChild(this.getFallbackForm());
      },
      resize: function(file) {
        var info, srcRatio, trgRatio;
        info = {
          srcX: 0,
          srcY: 0,
          srcWidth: file.width,
          srcHeight: file.height
        };
        srcRatio = file.width / file.height;
        info.optWidth = this.options.thumbnailWidth;
        info.optHeight = this.options.thumbnailHeight;
        if ((info.optWidth == null) && (info.optHeight == null)) {
          info.optWidth = info.srcWidth;
          info.optHeight = info.srcHeight;
        } else if (info.optWidth == null) {
          info.optWidth = srcRatio * info.optHeight;
        } else if (info.optHeight == null) {
          info.optHeight = (1 / srcRatio) * info.optWidth;
        }
        trgRatio = info.optWidth / info.optHeight;
        if (file.height < info.optHeight || file.width < info.optWidth) {
          info.trgHeight = info.srcHeight;
          info.trgWidth = info.srcWidth;
        } else {
          if (srcRatio > trgRatio) {
            info.srcHeight = file.height;
            info.srcWidth = info.srcHeight * trgRatio;
          } else {
            info.srcWidth = file.width;
            info.srcHeight = info.srcWidth / trgRatio;
          }
        }
        info.srcX = (file.width - info.srcWidth) / 2;
        info.srcY = (file.height - info.srcHeight) / 2;
        return info;
      },

      /*
      Those functions register themselves to the events on init and handle all
      the user interface specific stuff. Overwriting them won't break the upload
      but can break the way it's displayed.
      You can overwrite them if you don't like the default behavior. If you just
      want to add an additional event handler, register it on the dropzone object
      and don't overwrite those options.
       */
      drop: function(e) {
        return this.element.classList.remove("dz-drag-hover");
      },
      dragstart: noop,
      dragend: function(e) {
        return this.element.classList.remove("dz-drag-hover");
      },
      dragenter: function(e) {
        return this.element.classList.add("dz-drag-hover");
      },
      dragover: function(e) {
        return this.element.classList.add("dz-drag-hover");
      },
      dragleave: function(e) {
        return this.element.classList.remove("dz-drag-hover");
      },
      paste: noop,
      reset: function() {
        return this.element.classList.remove("dz-started");
      },
      addedfile: function(file) {
        var node, removeFileEvent, removeLink, _i, _j, _k, _len, _len1, _len2, _ref, _ref1, _ref2, _results;
        if (this.element === this.previewsContainer) {
          this.element.classList.add("dz-started");
        }
        if (this.previewsContainer) {
          file.previewElement = Dropzone.createElement(this.options.previewTemplate.trim());
          file.previewTemplate = file.previewElement;
          this.previewsContainer.appendChild(file.previewElement);
          _ref = file.previewElement.querySelectorAll("[data-dz-name]");
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            node = _ref[_i];
            node.textContent = file.name;
          }
          _ref1 = file.previewElement.querySelectorAll("[data-dz-size]");
          for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
            node = _ref1[_j];
            node.innerHTML = this.filesize(file.size);
          }
          if (this.options.addRemoveLinks) {
            file._removeLink = Dropzone.createElement("<a class=\"dz-remove\" href=\"javascript:undefined;\" data-dz-remove>" + this.options.dictRemoveFile + "</a>");
            file.previewElement.appendChild(file._removeLink);
          }
          removeFileEvent = (function(_this) {
            return function(e) {
              e.preventDefault();
              e.stopPropagation();
              if (file.status === Dropzone.UPLOADING) {
                return Dropzone.confirm(_this.options.dictCancelUploadConfirmation, function() {
                  return _this.removeFile(file);
                });
              } else {
                if (_this.options.dictRemoveFileConfirmation) {
                  return Dropzone.confirm(_this.options.dictRemoveFileConfirmation, function() {
                    return _this.removeFile(file);
                  });
                } else {
                  return _this.removeFile(file);
                }
              }
            };
          })(this);
          _ref2 = file.previewElement.querySelectorAll("[data-dz-remove]");
          _results = [];
          for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
            removeLink = _ref2[_k];
            _results.push(removeLink.addEventListener("click", removeFileEvent));
          }
          return _results;
        }
      },
      removedfile: function(file) {
        var _ref;
        if (file.previewElement) {
          if ((_ref = file.previewElement) != null) {
            _ref.parentNode.removeChild(file.previewElement);
          }
        }
        return this._updateMaxFilesReachedClass();
      },
      thumbnail: function(file, dataUrl) {
        var thumbnailElement, _i, _len, _ref;
        if (file.previewElement) {
          file.previewElement.classList.remove("dz-file-preview");
          _ref = file.previewElement.querySelectorAll("[data-dz-thumbnail]");
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            thumbnailElement = _ref[_i];
            thumbnailElement.alt = file.name;
            thumbnailElement.src = dataUrl;
          }
          return setTimeout(((function(_this) {
            return function() {
              return file.previewElement.classList.add("dz-image-preview");
            };
          })(this)), 1);
        }
      },
      error: function(file, message) {
        var node, _i, _len, _ref, _results;
        if (file.previewElement) {
          file.previewElement.classList.add("dz-error");
          if (typeof message !== "String" && message.error) {
            message = message.error;
          }
          _ref = file.previewElement.querySelectorAll("[data-dz-errormessage]");
          _results = [];
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            node = _ref[_i];
            _results.push(node.textContent = message);
          }
          return _results;
        }
      },
      errormultiple: noop,
      processing: function(file) {
        if (file.previewElement) {
          file.previewElement.classList.add("dz-processing");
          if (file._removeLink) {
            return file._removeLink.textContent = this.options.dictCancelUpload;
          }
        }
      },
      processingmultiple: noop,
      uploadprogress: function(file, progress, bytesSent) {
        var node, _i, _len, _ref, _results;
        if (file.previewElement) {
          _ref = file.previewElement.querySelectorAll("[data-dz-uploadprogress]");
          _results = [];
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            node = _ref[_i];
            if (node.nodeName === 'PROGRESS') {
              _results.push(node.value = progress);
            } else {
              _results.push(node.style.width = "" + progress + "%");
            }
          }
          return _results;
        }
      },
      totaluploadprogress: noop,
      sending: noop,
      sendingmultiple: noop,
      success: function(file) {
        if (file.previewElement) {
          return file.previewElement.classList.add("dz-success");
        }
      },
      successmultiple: noop,
      canceled: function(file) {
        return this.emit("error", file, "Upload canceled.");
      },
      canceledmultiple: noop,
      complete: function(file) {
        if (file._removeLink) {
          file._removeLink.textContent = this.options.dictRemoveFile;
        }
        if (file.previewElement) {
          return file.previewElement.classList.add("dz-complete");
        }
      },
      completemultiple: noop,
      maxfilesexceeded: noop,
      maxfilesreached: noop,
      queuecomplete: noop,
      previewTemplate: "<div class=\"dz-preview dz-file-preview\">\n  <div class=\"dz-image\"><img data-dz-thumbnail /></div>\n  <div class=\"dz-details\">\n    <div class=\"dz-size\"><span data-dz-size></span></div>\n    <div class=\"dz-filename\"><span data-dz-name></span></div>\n  </div>\n  <div class=\"dz-progress\"><span class=\"dz-upload\" data-dz-uploadprogress></span></div>\n  <div class=\"dz-error-message\"><span data-dz-errormessage></span></div>\n  <div class=\"dz-success-mark\">\n    <svg width=\"54px\" height=\"54px\" viewBox=\"0 0 54 54\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:sketch=\"http://www.bohemiancoding.com/sketch/ns\">\n      <title>Check</title>\n      <defs></defs>\n      <g id=\"Page-1\" stroke=\"none\" stroke-width=\"1\" fill=\"none\" fill-rule=\"evenodd\" sketch:type=\"MSPage\">\n        <path d=\"M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z\" id=\"Oval-2\" stroke-opacity=\"0.198794158\" stroke=\"#747474\" fill-opacity=\"0.816519475\" fill=\"#FFFFFF\" sketch:type=\"MSShapeGroup\"></path>\n      </g>\n    </svg>\n  </div>\n  <div class=\"dz-error-mark\">\n    <svg width=\"54px\" height=\"54px\" viewBox=\"0 0 54 54\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:sketch=\"http://www.bohemiancoding.com/sketch/ns\">\n      <title>Error</title>\n      <defs></defs>\n      <g id=\"Page-1\" stroke=\"none\" stroke-width=\"1\" fill=\"none\" fill-rule=\"evenodd\" sketch:type=\"MSPage\">\n        <g id=\"Check-+-Oval-2\" sketch:type=\"MSLayerGroup\" stroke=\"#747474\" stroke-opacity=\"0.198794158\" fill=\"#FFFFFF\" fill-opacity=\"0.816519475\">\n          <path d=\"M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z\" id=\"Oval-2\" sketch:type=\"MSShapeGroup\"></path>\n        </g>\n      </g>\n    </svg>\n  </div>\n</div>"
    };

    extend = function() {
      var key, object, objects, target, val, _i, _len;
      target = arguments[0], objects = 2 <= arguments.length ? __slice.call(arguments, 1) : [];
      for (_i = 0, _len = objects.length; _i < _len; _i++) {
        object = objects[_i];
        for (key in object) {
          val = object[key];
          target[key] = val;
        }
      }
      return target;
    };

    function Dropzone(element, options) {
      var elementOptions, fallback, _ref;
      this.element = element;
      this.version = Dropzone.version;
      this.defaultOptions.previewTemplate = this.defaultOptions.previewTemplate.replace(/\n*/g, "");
      this.clickableElements = [];
      this.listeners = [];
      this.files = [];
      if (typeof this.element === "string") {
        this.element = document.querySelector(this.element);
      }
      if (!(this.element && (this.element.nodeType != null))) {
        throw new Error("Invalid dropzone element.");
      }
      //if (this.element.dropzone) {
      //  throw new Error("Dropzone already attached.");
      //}
      Dropzone.instances.push(this);
      this.element.myDropzone = this;
      elementOptions = (_ref = Dropzone.optionsForElement(this.element)) != null ? _ref : {};
      this.options = extend({}, this.defaultOptions, elementOptions, options != null ? options : {});
      if (this.options.forceFallback || !Dropzone.isBrowserSupported()) {
        return this.options.fallback.call(this);
      }
      if (this.options.url == null) {
        this.options.url = this.element.getAttribute("action");
      }
      if (!this.options.url) {
        throw new Error("No URL provided.");
      }
      if (this.options.acceptedFiles && this.options.acceptedMimeTypes) {
        throw new Error("You can't provide both 'acceptedFiles' and 'acceptedMimeTypes'. 'acceptedMimeTypes' is deprecated.");
      }
      if (this.options.acceptedMimeTypes) {
        this.options.acceptedFiles = this.options.acceptedMimeTypes;
        delete this.options.acceptedMimeTypes;
      }
      this.options.method = this.options.method.toUpperCase();
      if ((fallback = this.getExistingFallback()) && fallback.parentNode) {
        fallback.parentNode.removeChild(fallback);
      }
      if (this.options.previewsContainer !== false) {
        if (this.options.previewsContainer) {
          this.previewsContainer = Dropzone.getElement(this.options.previewsContainer, "previewsContainer");
        } else {
          this.previewsContainer = this.element;
        }
      }
      if (this.options.clickable) {
        if (this.options.clickable === true) {
          this.clickableElements = [this.element];
        } else {
          this.clickableElements = Dropzone.getElements(this.options.clickable, "clickable");
        }
      }
      this.init();
    }

    Dropzone.prototype.getAcceptedFiles = function() {
      var file, _i, _len, _ref, _results;
      _ref = this.files;
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        file = _ref[_i];
        if (file.accepted) {
          _results.push(file);
        }
      }
      return _results;
    };

    Dropzone.prototype.getRejectedFiles = function() {
      var file, _i, _len, _ref, _results;
      _ref = this.files;
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        file = _ref[_i];
        if (!file.accepted) {
          _results.push(file);
        }
      }
      return _results;
    };

    Dropzone.prototype.getFilesWithStatus = function(status) {
      var file, _i, _len, _ref, _results;
      _ref = this.files;
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        file = _ref[_i];
        if (file.status === status) {
          _results.push(file);
        }
      }
      return _results;
    };

    Dropzone.prototype.getQueuedFiles = function() {
      return this.getFilesWithStatus(Dropzone.QUEUED);
    };

    Dropzone.prototype.getUploadingFiles = function() {
      return this.getFilesWithStatus(Dropzone.UPLOADING);
    };

    Dropzone.prototype.getActiveFiles = function() {
      var file, _i, _len, _ref, _results;
      _ref = this.files;
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        file = _ref[_i];
        if (file.status === Dropzone.UPLOADING || file.status === Dropzone.QUEUED) {
          _results.push(file);
        }
      }
      return _results;
    };

    Dropzone.prototype.init = function() {
      var eventName, noPropagation, setupHiddenFileInput, _i, _len, _ref, _ref1;
      if (this.element.tagName === "form") {
        this.element.setAttribute("enctype", "multipart/form-data");
      }
      if (this.element.classList.contains("dropzone") && !this.element.querySelector(".dz-message")) {
        this.element.appendChild(Dropzone.createElement("<div class=\"dz-default dz-message\"><span>" + this.options.dictDefaultMessage + "</span></div>"));
      }
      if (this.clickableElements.length) {
        setupHiddenFileInput = (function(_this) {
          return function() {
            if (_this.hiddenFileInput) {
              document.body.removeChild(_this.hiddenFileInput);
            }
            _this.hiddenFileInput = document.createElement("input");
            _this.hiddenFileInput.setAttribute("type", "file");
            if ((_this.options.maxFiles == null) || _this.options.maxFiles > 1) {
              _this.hiddenFileInput.setAttribute("multiple", "multiple");
            }
            _this.hiddenFileInput.className = "dz-hidden-input";
            if (_this.options.acceptedFiles != null) {
              _this.hiddenFileInput.setAttribute("accept", _this.options.acceptedFiles);
            }
            if (_this.options.capture != null) {
              _this.hiddenFileInput.setAttribute("capture", _this.options.capture);
            }
            _this.hiddenFileInput.style.visibility = "hidden";
            _this.hiddenFileInput.style.position = "absolute";
            _this.hiddenFileInput.style.top = "0";
            _this.hiddenFileInput.style.left = "0";
            _this.hiddenFileInput.style.height = "0";
            _this.hiddenFileInput.style.width = "0";
            document.body.appendChild(_this.hiddenFileInput);
            return _this.hiddenFileInput.addEventListener("change", function() {
              var file, files, _i, _len;
              files = _this.hiddenFileInput.files;
              if (files.length) {
                for (_i = 0, _len = files.length; _i < _len; _i++) {
                  file = files[_i];
                  _this.addFile(file);
                }
              }
              return setupHiddenFileInput();
            });
          };
        })(this);
        setupHiddenFileInput();
      }
      this.URL = (_ref = window.URL) != null ? _ref : window.webkitURL;
      _ref1 = this.events;
      for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
        eventName = _ref1[_i];
        this.on(eventName, this.options[eventName]);
      }
      this.on("uploadprogress", (function(_this) {
        return function() {
          return _this.updateTotalUploadProgress();
        };
      })(this));
      this.on("removedfile", (function(_this) {
        return function() {
          return _this.updateTotalUploadProgress();
        };
      })(this));
      this.on("canceled", (function(_this) {
        return function(file) {
          return _this.emit("complete", file);
        };
      })(this));
      this.on("complete", (function(_this) {
        return function(file) {
          if (_this.getUploadingFiles().length === 0 && _this.getQueuedFiles().length === 0) {
            return setTimeout((function() {
              return _this.emit("queuecomplete");
            }), 0);
          }
        };
      })(this));
      noPropagation = function(e) {
        e.stopPropagation();
        if (e.preventDefault) {
          return e.preventDefault();
        } else {
          return e.returnValue = false;
        }
      };
      this.listeners = [
        {
          element: this.element,
          events: {
            "dragstart": (function(_this) {
              return function(e) {
                return _this.emit("dragstart", e);
              };
            })(this),
            "dragenter": (function(_this) {
              return function(e) {
                noPropagation(e);
                return _this.emit("dragenter", e);
              };
            })(this),
            "dragover": (function(_this) {
              return function(e) {
                var efct;
                try {
                  efct = e.dataTransfer.effectAllowed;
                } catch (_error) {}
                e.dataTransfer.dropEffect = 'move' === efct || 'linkMove' === efct ? 'move' : 'copy';
                noPropagation(e);
                return _this.emit("dragover", e);
              };
            })(this),
            "dragleave": (function(_this) {
              return function(e) {
                return _this.emit("dragleave", e);
              };
            })(this),
            "drop": (function(_this) {
              return function(e) {
                noPropagation(e);
                return _this.drop(e);
              };
            })(this),
            "dragend": (function(_this) {
              return function(e) {
                return _this.emit("dragend", e);
              };
            })(this)
          }
        }
      ];
      this.clickableElements.forEach((function(_this) {
        return function(clickableElement) {
          return _this.listeners.push({
            element: clickableElement,
            events: {
              "click": function(evt) {
                if ((clickableElement !== _this.element) || (evt.target === _this.element || Dropzone.elementInside(evt.target, _this.element.querySelector(".dz-message")))) {
                  return _this.hiddenFileInput.click();
                }
              }
            }
          });
        };
      })(this));
      this.enable();
      return this.options.init.call(this);
    };

    Dropzone.prototype.destroy = function() {
      var _ref;
      this.disable();
      this.removeAllFiles(true);
      if ((_ref = this.hiddenFileInput) != null ? _ref.parentNode : void 0) {
        this.hiddenFileInput.parentNode.removeChild(this.hiddenFileInput);
        this.hiddenFileInput = null;
      }
      delete this.element.myDropzone;
      return Dropzone.instances.splice(Dropzone.instances.indexOf(this), 1);
    };

    Dropzone.prototype.updateTotalUploadProgress = function() {
      var activeFiles, file, totalBytes, totalBytesSent, totalUploadProgress, _i, _len, _ref;
      totalBytesSent = 0;
      totalBytes = 0;
      activeFiles = this.getActiveFiles();
      if (activeFiles.length) {
        _ref = this.getActiveFiles();
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          file = _ref[_i];
          totalBytesSent += file.upload.bytesSent;
          totalBytes += file.upload.total;
        }
        totalUploadProgress = 100 * totalBytesSent / totalBytes;
      } else {
        totalUploadProgress = 100;
      }
      return this.emit("totaluploadprogress", totalUploadProgress, totalBytes, totalBytesSent);
    };

    Dropzone.prototype._getParamName = function(n) {
      if (typeof this.options.paramName === "function") {
        return this.options.paramName(n);
      } else {
        return "" + this.options.paramName + (this.options.uploadMultiple ? "[" + n + "]" : "");
      }
    };

    Dropzone.prototype.getFallbackForm = function() {
      var existingFallback, fields, fieldsString, form;
      if (existingFallback = this.getExistingFallback()) {
        return existingFallback;
      }
      fieldsString = "<div class=\"dz-fallback\">";
      if (this.options.dictFallbackText) {
        fieldsString += "<p>" + this.options.dictFallbackText + "</p>";
      }
      fieldsString += "<input type=\"file\" name=\"" + (this._getParamName(0)) + "\" " + (this.options.uploadMultiple ? 'multiple="multiple"' : void 0) + " /><input type=\"submit\" value=\"Upload!\"></div>";
      fields = Dropzone.createElement(fieldsString);
      if (this.element.tagName !== "FORM") {
        form = Dropzone.createElement("<form action=\"" + this.options.url + "\" enctype=\"multipart/form-data\" method=\"" + this.options.method + "\"></form>");
        form.appendChild(fields);
      } else {
        this.element.setAttribute("enctype", "multipart/form-data");
        this.element.setAttribute("method", this.options.method);
      }
      return form != null ? form : fields;
    };

    Dropzone.prototype.getExistingFallback = function() {
      var fallback, getFallback, tagName, _i, _len, _ref;
      getFallback = function(elements) {
        var el, _i, _len;
        for (_i = 0, _len = elements.length; _i < _len; _i++) {
          el = elements[_i];
          if (/(^| )fallback($| )/.test(el.className)) {
            return el;
          }
        }
      };
      _ref = ["div", "form"];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        tagName = _ref[_i];
        if (fallback = getFallback(this.element.getElementsByTagName(tagName))) {
          return fallback;
        }
      }
    };

    Dropzone.prototype.setupEventListeners = function() {
      var elementListeners, event, listener, _i, _len, _ref, _results;
      _ref = this.listeners;
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        elementListeners = _ref[_i];
        _results.push((function() {
          var _ref1, _results1;
          _ref1 = elementListeners.events;
          _results1 = [];
          for (event in _ref1) {
            listener = _ref1[event];
            _results1.push(elementListeners.element.addEventListener(event, listener, false));
          }
          return _results1;
        })());
      }
      return _results;
    };

    Dropzone.prototype.removeEventListeners = function() {
      var elementListeners, event, listener, _i, _len, _ref, _results;
      _ref = this.listeners;
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        elementListeners = _ref[_i];
        _results.push((function() {
          var _ref1, _results1;
          _ref1 = elementListeners.events;
          _results1 = [];
          for (event in _ref1) {
            listener = _ref1[event];
            _results1.push(elementListeners.element.removeEventListener(event, listener, false));
          }
          return _results1;
        })());
      }
      return _results;
    };

    Dropzone.prototype.disable = function() {
      var file, _i, _len, _ref, _results;
      this.clickableElements.forEach(function(element) {
        return element.classList.remove("dz-clickable");
      });
      this.removeEventListeners();
      _ref = this.files;
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        file = _ref[_i];
        _results.push(this.cancelUpload(file));
      }
      return _results;
    };

    Dropzone.prototype.enable = function() {
      this.clickableElements.forEach(function(element) {
        return element.classList.add("dz-clickable");
      });
      return this.setupEventListeners();
    };

    Dropzone.prototype.filesize = function(size) {
      var cutoff, i, selectedSize, selectedUnit, unit, units, _i, _len;
      units = ['TB', 'GB', 'MB', 'KB', 'b'];
      selectedSize = selectedUnit = null;
      for (i = _i = 0, _len = units.length; _i < _len; i = ++_i) {
        unit = units[i];
        cutoff = Math.pow(this.options.filesizeBase, 4 - i) / 10;
        if (size >= cutoff) {
          selectedSize = size / Math.pow(this.options.filesizeBase, 4 - i);
          selectedUnit = unit;
          break;
        }
      }
      selectedSize = Math.round(10 * selectedSize) / 10;
      return "<strong>" + selectedSize + "</strong> " + selectedUnit;
    };

    Dropzone.prototype._updateMaxFilesReachedClass = function() {
      if ((this.options.maxFiles != null) && this.getAcceptedFiles().length >= this.options.maxFiles) {
        if (this.getAcceptedFiles().length === this.options.maxFiles) {
          this.emit('maxfilesreached', this.files);
        }
        return this.element.classList.add("dz-max-files-reached");
      } else {
        return this.element.classList.remove("dz-max-files-reached");
      }
    };

    Dropzone.prototype.drop = function(e) {
      var files, items;
      if (!e.dataTransfer) {
        return;
      }
      this.emit("drop", e);
      files = e.dataTransfer.files;
      if (files.length) {
        items = e.dataTransfer.items;
        if (items && items.length && (items[0].webkitGetAsEntry != null)) {
          this._addFilesFromItems(items);
        } else {
          this.handleFiles(files);
        }
      }
    };

    Dropzone.prototype.paste = function(e) {
      var items, _ref;
      if ((e != null ? (_ref = e.clipboardData) != null ? _ref.items : void 0 : void 0) == null) {
        return;
      }
      this.emit("paste", e);
      items = e.clipboardData.items;
      if (items.length) {
        return this._addFilesFromItems(items);
      }
    };

    Dropzone.prototype.handleFiles = function(files) {
      var file, _i, _len, _results;
      _results = [];
      for (_i = 0, _len = files.length; _i < _len; _i++) {
        file = files[_i];
        _results.push(this.addFile(file));
      }
      return _results;
    };

    Dropzone.prototype._addFilesFromItems = function(items) {
      var entry, item, _i, _len, _results;
      _results = [];
      for (_i = 0, _len = items.length; _i < _len; _i++) {
        item = items[_i];
        if ((item.webkitGetAsEntry != null) && (entry = item.webkitGetAsEntry())) {
          if (entry.isFile) {
            _results.push(this.addFile(item.getAsFile()));
          } else if (entry.isDirectory) {
            _results.push(this._addFilesFromDirectory(entry, entry.name));
          } else {
            _results.push(void 0);
          }
        } else if (item.getAsFile != null) {
          if ((item.kind == null) || item.kind === "file") {
            _results.push(this.addFile(item.getAsFile()));
          } else {
            _results.push(void 0);
          }
        } else {
          _results.push(void 0);
        }
      }
      return _results;
    };

    Dropzone.prototype._addFilesFromDirectory = function(directory, path) {
      var dirReader, entriesReader;
      dirReader = directory.createReader();
      entriesReader = (function(_this) {
        return function(entries) {
          var entry, _i, _len;
          for (_i = 0, _len = entries.length; _i < _len; _i++) {
            entry = entries[_i];
            if (entry.isFile) {
              entry.file(function(file) {
                if (_this.options.ignoreHiddenFiles && file.name.substring(0, 1) === '.') {
                  return;
                }
                file.fullPath = "" + path + "/" + file.name;
                return _this.addFile(file);
              });
            } else if (entry.isDirectory) {
              _this._addFilesFromDirectory(entry, "" + path + "/" + entry.name);
            }
          }
        };
      })(this);
      return dirReader.readEntries(entriesReader, function(error) {
        return typeof console !== "undefined" && console !== null ? typeof console.log === "function" ? console.log(error) : void 0 : void 0;
      });
    };

    Dropzone.prototype.accept = function(file, done) {
      if (file.size > this.options.maxFilesize * 1024 * 1024) {
        return done(this.options.dictFileTooBig.replace("{{filesize}}", Math.round(file.size / 1024 / 10.24) / 100).replace("{{maxFilesize}}", this.options.maxFilesize));
      } else if (!Dropzone.isValidFile(file, this.options.acceptedFiles)) {
        return done(this.options.dictInvalidFileType);
      } else if ((this.options.maxFiles != null) && this.getAcceptedFiles().length >= this.options.maxFiles) {
        done(this.options.dictMaxFilesExceeded.replace("{{maxFiles}}", this.options.maxFiles));
        return this.emit("maxfilesexceeded", file);
      } else {
        return this.options.accept.call(this, file, done);
      }
    };

    Dropzone.prototype.addFile = function(file) {
      file.upload = {
        progress: 0,
        total: file.size,
        bytesSent: 0
      };
      this.files.push(file);
      file.status = Dropzone.ADDED;
      this.emit("addedfile", file);
      this._enqueueThumbnail(file);
      return this.accept(file, (function(_this) {
        return function(error) {
          if (error) {
            file.accepted = false;
            _this._errorProcessing([file], error);
          } else {
            file.accepted = true;
            if (_this.options.autoQueue) {
              _this.enqueueFile(file);
            }
          }
          return _this._updateMaxFilesReachedClass();
        };
      })(this));
    };

    Dropzone.prototype.enqueueFiles = function(files) {
      var file, _i, _len;
      for (_i = 0, _len = files.length; _i < _len; _i++) {
        file = files[_i];
        this.enqueueFile(file);
      }
      return null;
    };

    Dropzone.prototype.enqueueFile = function(file) {
      if (file.status === Dropzone.ADDED && file.accepted === true) {
        file.status = Dropzone.QUEUED;
        if (this.options.autoProcessQueue) {
          return setTimeout(((function(_this) {
            return function() {
              return _this.processQueue();
            };
          })(this)), 0);
        }
      } else {
        throw new Error("This file can't be queued because it has already been processed or was rejected.");
      }
    };

    Dropzone.prototype._thumbnailQueue = [];

    Dropzone.prototype._processingThumbnail = false;

    Dropzone.prototype._enqueueThumbnail = function(file) {
      if (this.options.createImageThumbnails && file.type.match(/image.*/) && file.size <= this.options.maxThumbnailFilesize * 1024 * 1024) {
        this._thumbnailQueue.push(file);
        return setTimeout(((function(_this) {
          return function() {
            return _this._processThumbnailQueue();
          };
        })(this)), 0);
      }
    };

    Dropzone.prototype._processThumbnailQueue = function() {
      if (this._processingThumbnail || this._thumbnailQueue.length === 0) {
        return;
      }
      this._processingThumbnail = true;
      return this.createThumbnail(this._thumbnailQueue.shift(), (function(_this) {
        return function() {
          _this._processingThumbnail = false;
          return _this._processThumbnailQueue();
        };
      })(this));
    };

    Dropzone.prototype.removeFile = function(file) {
      if (file.status === Dropzone.UPLOADING) {
        this.cancelUpload(file);
      }
      this.files = without(this.files, file);
      this.emit("removedfile", file);
      if (this.files.length === 0) {
        return this.emit("reset");
      }
    };

    Dropzone.prototype.removeAllFiles = function(cancelIfNecessary) {
      var file, _i, _len, _ref;
      if (cancelIfNecessary == null) {
        cancelIfNecessary = false;
      }
      _ref = this.files.slice();
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        file = _ref[_i];
        if (file.status !== Dropzone.UPLOADING || cancelIfNecessary) {
          this.removeFile(file);
        }
      }
      return null;
    };

    Dropzone.prototype.createThumbnail = function(file, callback) {
      var fileReader;
      fileReader = new FileReader;
      fileReader.onload = (function(_this) {
        return function() {
          if (file.type === "image/svg+xml") {
            _this.emit("thumbnail", file, fileReader.result);
            if (callback != null) {
              callback();
            }
            return;
          }
          return _this.createThumbnailFromUrl(file, fileReader.result, callback);
        };
      })(this);
      return fileReader.readAsDataURL(file);
    };

    Dropzone.prototype.createThumbnailFromUrl = function(file, imageUrl, callback) {
      var img;
      img = document.createElement("img");
      img.onload = (function(_this) {
        return function() {
          var canvas, ctx, resizeInfo, thumbnail, _ref, _ref1, _ref2, _ref3;
          file.width = img.width;
          file.height = img.height;
          resizeInfo = _this.options.resize.call(_this, file);
          if (resizeInfo.trgWidth == null) {
            resizeInfo.trgWidth = resizeInfo.optWidth;
          }
          if (resizeInfo.trgHeight == null) {
            resizeInfo.trgHeight = resizeInfo.optHeight;
          }
          canvas = document.createElement("canvas");
          ctx = canvas.getContext("2d");
          canvas.width = resizeInfo.trgWidth;
          canvas.height = resizeInfo.trgHeight;
          drawImageIOSFix(ctx, img, (_ref = resizeInfo.srcX) != null ? _ref : 0, (_ref1 = resizeInfo.srcY) != null ? _ref1 : 0, resizeInfo.srcWidth, resizeInfo.srcHeight, (_ref2 = resizeInfo.trgX) != null ? _ref2 : 0, (_ref3 = resizeInfo.trgY) != null ? _ref3 : 0, resizeInfo.trgWidth, resizeInfo.trgHeight);
          thumbnail = canvas.toDataURL("image/png");
          _this.emit("thumbnail", file, thumbnail);
          if (callback != null) {
            return callback();
          }
        };
      })(this);
      if (callback != null) {
        img.onerror = callback;
      }
      return img.src = imageUrl;
    };

    Dropzone.prototype.processQueue = function() {
      var i, parallelUploads, processingLength, queuedFiles;
      parallelUploads = this.options.parallelUploads;
      processingLength = this.getUploadingFiles().length;
      i = processingLength;
      if (processingLength >= parallelUploads) {
        return;
      }
      queuedFiles = this.getQueuedFiles();
      if (!(queuedFiles.length > 0)) {
        return;
      }
      if (this.options.uploadMultiple) {
        return this.processFiles(queuedFiles.slice(0, parallelUploads - processingLength));
      } else {
        while (i < parallelUploads) {
          if (!queuedFiles.length) {
            return;
          }
          this.processFile(queuedFiles.shift());
          i++;
        }
      }
    };

    Dropzone.prototype.processFile = function(file) {
      return this.processFiles([file]);
    };

    Dropzone.prototype.processFiles = function(files) {
      var file, _i, _len;
      for (_i = 0, _len = files.length; _i < _len; _i++) {
        file = files[_i];
        file.processing = true;
        file.status = Dropzone.UPLOADING;
        this.emit("processing", file);
      }
      if (this.options.uploadMultiple) {
        this.emit("processingmultiple", files);
      }
      return this.uploadFiles(files);
    };

    Dropzone.prototype._getFilesWithXhr = function(xhr) {
      var file, files;
      return files = (function() {
        var _i, _len, _ref, _results;
        _ref = this.files;
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          file = _ref[_i];
          if (file.xhr === xhr) {
            _results.push(file);
          }
        }
        return _results;
      }).call(this);
    };

    Dropzone.prototype.cancelUpload = function(file) {
      var groupedFile, groupedFiles, _i, _j, _len, _len1, _ref;
      if (file.status === Dropzone.UPLOADING) {
        groupedFiles = this._getFilesWithXhr(file.xhr);
        for (_i = 0, _len = groupedFiles.length; _i < _len; _i++) {
          groupedFile = groupedFiles[_i];
          groupedFile.status = Dropzone.CANCELED;
        }
        file.xhr.abort();
        for (_j = 0, _len1 = groupedFiles.length; _j < _len1; _j++) {
          groupedFile = groupedFiles[_j];
          this.emit("canceled", groupedFile);
        }
        if (this.options.uploadMultiple) {
          this.emit("canceledmultiple", groupedFiles);
        }
      } else if ((_ref = file.status) === Dropzone.ADDED || _ref === Dropzone.QUEUED) {
        file.status = Dropzone.CANCELED;
        this.emit("canceled", file);
        if (this.options.uploadMultiple) {
          this.emit("canceledmultiple", [file]);
        }
      }
      if (this.options.autoProcessQueue) {
        return this.processQueue();
      }
    };

    resolveOption = function() {
      var args, option;
      option = arguments[0], args = 2 <= arguments.length ? __slice.call(arguments, 1) : [];
      if (typeof option === 'function') {
        return option.apply(this, args);
      }
      return option;
    };

    Dropzone.prototype.uploadFile = function(file) {
      return this.uploadFiles([file]);
    };

    Dropzone.prototype.uploadFiles = function(files) {
      var file, formData, handleError, headerName, headerValue, headers, i, input, inputName, inputType, key, method, option, progressObj, response, updateProgress, url, value, xhr, _i, _j, _k, _l, _len, _len1, _len2, _len3, _m, _ref, _ref1, _ref2, _ref3, _ref4, _ref5;
      xhr = new XMLHttpRequest();
      for (_i = 0, _len = files.length; _i < _len; _i++) {
        file = files[_i];
        file.xhr = xhr;
      }
      method = resolveOption(this.options.method, files);
      url = resolveOption(this.options.url, files);
      xhr.open(method, url, true);
      xhr.withCredentials = !!this.options.withCredentials;
      response = null;
      handleError = (function(_this) {
        return function() {
          var _j, _len1, _results;
          _results = [];
          for (_j = 0, _len1 = files.length; _j < _len1; _j++) {
            file = files[_j];
            _results.push(_this._errorProcessing(files, response || _this.options.dictResponseError.replace("{{statusCode}}", xhr.status), xhr));
          }
          return _results;
        };
      })(this);
      updateProgress = (function(_this) {
        return function(e) {
          var allFilesFinished, progress, _j, _k, _l, _len1, _len2, _len3, _results;
          if (e != null) {
            progress = 100 * e.loaded / e.total;
            for (_j = 0, _len1 = files.length; _j < _len1; _j++) {
              file = files[_j];
              file.upload = {
                progress: progress,
                total: e.total,
                bytesSent: e.loaded
              };
            }
          } else {
            allFilesFinished = true;
            progress = 100;
            for (_k = 0, _len2 = files.length; _k < _len2; _k++) {
              file = files[_k];
              if (!(file.upload.progress === 100 && file.upload.bytesSent === file.upload.total)) {
                allFilesFinished = false;
              }
              file.upload.progress = progress;
              file.upload.bytesSent = file.upload.total;
            }
            if (allFilesFinished) {
              return;
            }
          }
          _results = [];
          for (_l = 0, _len3 = files.length; _l < _len3; _l++) {
            file = files[_l];
            _results.push(_this.emit("uploadprogress", file, progress, file.upload.bytesSent));
          }
          return _results;
        };
      })(this);
      xhr.onload = (function(_this) {
        return function(e) {
          var _ref;
          if (files[0].status === Dropzone.CANCELED) {
            return;
          }
          if (xhr.readyState !== 4) {
            return;
          }
          response = xhr.responseText;
          if (xhr.getResponseHeader("content-type") && ~xhr.getResponseHeader("content-type").indexOf("application/json")) {
            try {
              response = JSON.parse(response);
            } catch (_error) {
              e = _error;
              response = "Invalid JSON response from server.";
            }
          }
          updateProgress();
          if (!((200 <= (_ref = xhr.status) && _ref < 300))) {
            return handleError();
          } else {
            return _this._finished(files, response, e);
          }
        };
      })(this);
      xhr.onerror = (function(_this) {
        return function() {
          if (files[0].status === Dropzone.CANCELED) {
            return;
          }
          return handleError();
        };
      })(this);
      progressObj = (_ref = xhr.upload) != null ? _ref : xhr;
      progressObj.onprogress = updateProgress;
      headers = {
        "Accept": "application/json",
        "Cache-Control": "no-cache",
        "X-Requested-With": "XMLHttpRequest"
      };
      if (this.options.headers) {
        extend(headers, this.options.headers);
      }
      for (headerName in headers) {
        headerValue = headers[headerName];
        xhr.setRequestHeader(headerName, headerValue);
      }
      formData = new FormData();
      if (this.options.params) {
        _ref1 = this.options.params;
        for (key in _ref1) {
          value = _ref1[key];
          formData.append(key, value);
        }
      }
      for (_j = 0, _len1 = files.length; _j < _len1; _j++) {
        file = files[_j];
        this.emit("sending", file, xhr, formData);
      }
      if (this.options.uploadMultiple) {
        this.emit("sendingmultiple", files, xhr, formData);
      }
      if (this.element.tagName === "FORM") {
        _ref2 = this.element.querySelectorAll("input, textarea, select, button");
        for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
          input = _ref2[_k];
          inputName = input.getAttribute("name");
          inputType = input.getAttribute("type");
          if (input.tagName === "SELECT" && input.hasAttribute("multiple")) {
            _ref3 = input.options;
            for (_l = 0, _len3 = _ref3.length; _l < _len3; _l++) {
              option = _ref3[_l];
              if (option.selected) {
                formData.append(inputName, option.value);
              }
            }
          } else if (!inputType || ((_ref4 = inputType.toLowerCase()) !== "checkbox" && _ref4 !== "radio") || input.checked) {
            formData.append(inputName, input.value);
          }
        }
      }
      for (i = _m = 0, _ref5 = files.length - 1; 0 <= _ref5 ? _m <= _ref5 : _m >= _ref5; i = 0 <= _ref5 ? ++_m : --_m) {
        formData.append(this._getParamName(i), files[i], files[i].name);
      }
      return xhr.send(formData);
    };

    Dropzone.prototype._finished = function(files, responseText, e) {
      var file, _i, _len;
      for (_i = 0, _len = files.length; _i < _len; _i++) {
        file = files[_i];
        file.status = Dropzone.SUCCESS;
        this.emit("success", file, responseText, e);
        this.emit("complete", file);
      }
      if (this.options.uploadMultiple) {
        this.emit("successmultiple", files, responseText, e);
        this.emit("completemultiple", files);
      }
      if (this.options.autoProcessQueue) {
        return this.processQueue();
      }
    };

    Dropzone.prototype._errorProcessing = function(files, message, xhr) {
      var file, _i, _len;
      for (_i = 0, _len = files.length; _i < _len; _i++) {
        file = files[_i];
        file.status = Dropzone.ERROR;
        this.emit("error", file, message, xhr);
        this.emit("complete", file);
      }
      if (this.options.uploadMultiple) {
        this.emit("errormultiple", files, message, xhr);
        this.emit("completemultiple", files);
      }
      if (this.options.autoProcessQueue) {
        return this.processQueue();
      }
    };

    return Dropzone;

  })(Emitter);

  Dropzone.version = "4.0.1";

  Dropzone.options = {};

  Dropzone.optionsForElement = function(element) {
    if (element.getAttribute("id")) {
      return Dropzone.options[camelize(element.getAttribute("id"))];
    } else {
      return void 0;
    }
  };

  Dropzone.instances = [];

  Dropzone.forElement = function(element) {
    if (typeof element === "string") {
      element = document.querySelector(element);
    }
    if ((element != null ? element.myDropzone : void 0) == null) {
      throw new Error("No Dropzone found for given element. This is probably because you're trying to access it before Dropzone had the time to initialize. Use the `init` option to setup any additional observers on your Dropzone.");
    }
    return element.myDropzone;
  };

  Dropzone.autoDiscover = true;

  Dropzone.discover = function() {
    var checkElements, dropzone, dropzones, _i, _len, _results;
    if (document.querySelectorAll) {
      dropzones = document.querySelectorAll(".dropzone");
    } else {
      dropzones = [];
      checkElements = function(elements) {
        var el, _i, _len, _results;
        _results = [];
        for (_i = 0, _len = elements.length; _i < _len; _i++) {
          el = elements[_i];
          if (/(^| )dropzone($| )/.test(el.className)) {
            _results.push(dropzones.push(el));
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      };
      checkElements(document.getElementsByTagName("div"));
      checkElements(document.getElementsByTagName("form"));
    }
    _results = [];
    for (_i = 0, _len = dropzones.length; _i < _len; _i++) {
      dropzone = dropzones[_i];
      if (Dropzone.optionsForElement(dropzone) !== false) {
        _results.push(new Dropzone(dropzone));
      } else {
        _results.push(void 0);
      }
    }
    return _results;
  };

  Dropzone.blacklistedBrowsers = [/opera.*Macintosh.*version\/12/i];

  Dropzone.isBrowserSupported = function() {
    var capableBrowser, regex, _i, _len, _ref;
    capableBrowser = true;
    if (window.File && window.FileReader && window.FileList && window.Blob && window.FormData && document.querySelector) {
      if (!("classList" in document.createElement("a"))) {
        capableBrowser = false;
      } else {
        _ref = Dropzone.blacklistedBrowsers;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          regex = _ref[_i];
          if (regex.test(navigator.userAgent)) {
            capableBrowser = false;
            continue;
          }
        }
      }
    } else {
      capableBrowser = false;
    }
    return capableBrowser;
  };

  without = function(list, rejectedItem) {
    var item, _i, _len, _results;
    _results = [];
    for (_i = 0, _len = list.length; _i < _len; _i++) {
      item = list[_i];
      if (item !== rejectedItem) {
        _results.push(item);
      }
    }
    return _results;
  };

  camelize = function(str) {
    return str.replace(/[\-_](\w)/g, function(match) {
      return match.charAt(1).toUpperCase();
    });
  };

  Dropzone.createElement = function(string) {
    var div;
    div = document.createElement("div");
    div.innerHTML = string;
    return div.childNodes[0];
  };

  Dropzone.elementInside = function(element, container) {
    if (element === container) {
      return true;
    }
    while (element = element.parentNode) {
      if (element === container) {
        return true;
      }
    }
    return false;
  };

  Dropzone.getElement = function(el, name) {
    var element;
    if (typeof el === "string") {
      element = document.querySelector(el);
    } else if (el.nodeType != null) {
      element = el;
    }
    if (element == null) {
      throw new Error("Invalid `" + name + "` option provided. Please provide a CSS selector or a plain HTML element.");
    }
    return element;
  };

  Dropzone.getElements = function(els, name) {
    var e, el, elements, _i, _j, _len, _len1, _ref;
    if (els instanceof Array) {
      elements = [];
      try {
        for (_i = 0, _len = els.length; _i < _len; _i++) {
          el = els[_i];
          elements.push(this.getElement(el, name));
        }
      } catch (_error) {
        e = _error;
        elements = null;
      }
    } else if (typeof els === "string") {
      elements = [];
      _ref = document.querySelectorAll(els);
      for (_j = 0, _len1 = _ref.length; _j < _len1; _j++) {
        el = _ref[_j];
        elements.push(el);
      }
    } else if (els.nodeType != null) {
      elements = [els];
    }
    if (!((elements != null) && elements.length)) {
      throw new Error("Invalid `" + name + "` option provided. Please provide a CSS selector, a plain HTML element or a list of those.");
    }
    return elements;
  };

  Dropzone.confirm = function(question, accepted, rejected) {
    if (window.confirm(question)) {
      return accepted();
    } else if (rejected != null) {
      return rejected();
    }
  };

  Dropzone.isValidFile = function(file, acceptedFiles) {
    var baseMimeType, mimeType, validType, _i, _len;
    if (!acceptedFiles) {
      return true;
    }
    acceptedFiles = acceptedFiles.split(",");
    mimeType = file.type;
    baseMimeType = mimeType.replace(/\/.*$/, "");
    for (_i = 0, _len = acceptedFiles.length; _i < _len; _i++) {
      validType = acceptedFiles[_i];
      validType = validType.trim();
      if (validType.charAt(0) === ".") {
        if (file.name.toLowerCase().indexOf(validType.toLowerCase(), file.name.length - validType.length) !== -1) {
          return true;
        }
      } else if (/\/\*$/.test(validType)) {
        if (baseMimeType === validType.replace(/\/.*$/, "")) {
          return true;
        }
      } else {
        if (mimeType === validType) {
          return true;
        }
      }
    }
    return false;
  };

  if (typeof jQuery !== "undefined" && jQuery !== null) {
    jQuery.fn.myDropzone = function(options) {
      return this.each(function() {
        return new Dropzone(this, options);
      });
    };
  }

  if (typeof module !== "undefined" && module !== null) {
    module.exports = Dropzone;
  } else {
    window.Dropzone = Dropzone;
  }

  Dropzone.ADDED = "added";

  Dropzone.QUEUED = "queued";

  Dropzone.ACCEPTED = Dropzone.QUEUED;

  Dropzone.UPLOADING = "uploading";

  Dropzone.PROCESSING = Dropzone.UPLOADING;

  Dropzone.CANCELED = "canceled";

  Dropzone.ERROR = "error";

  Dropzone.SUCCESS = "success";


  /*
  
  Bugfix for iOS 6 and 7
  Source: http://stackoverflow.com/questions/11929099/html5-canvas-drawimage-ratio-bug-ios
  based on the work of https://github.com/stomita/ios-imagefile-megapixel
   */

  detectVerticalSquash = function(img) {
    var alpha, canvas, ctx, data, ey, ih, iw, py, ratio, sy;
    iw = img.naturalWidth;
    ih = img.naturalHeight;
    canvas = document.createElement("canvas");
    canvas.width = 1;
    canvas.height = ih;
    ctx = canvas.getContext("2d");
    ctx.drawImage(img, 0, 0);
    data = ctx.getImageData(0, 0, 1, ih).data;
    sy = 0;
    ey = ih;
    py = ih;
    while (py > sy) {
      alpha = data[(py - 1) * 4 + 3];
      if (alpha === 0) {
        ey = py;
      } else {
        sy = py;
      }
      py = (ey + sy) >> 1;
    }
    ratio = py / ih;
    if (ratio === 0) {
      return 1;
    } else {
      return ratio;
    }
  };

  drawImageIOSFix = function(ctx, img, sx, sy, sw, sh, dx, dy, dw, dh) {
    var vertSquashRatio;
    vertSquashRatio = detectVerticalSquash(img);
    return ctx.drawImage(img, sx, sy, sw, sh, dx, dy, dw, dh / vertSquashRatio);
  };


  /*
   * contentloaded.js
   *
   * Author: Diego Perini (diego.perini at gmail.com)
   * Summary: cross-browser wrapper for DOMContentLoaded
   * Updated: 20101020
   * License: MIT
   * Version: 1.2
   *
   * URL:
   * http://javascript.nwbox.com/ContentLoaded/
   * http://javascript.nwbox.com/ContentLoaded/MIT-LICENSE
   */

  contentLoaded = function(win, fn) {
    var add, doc, done, init, poll, pre, rem, root, top;
    done = false;
    top = true;
    doc = win.document;
    root = doc.documentElement;
    add = (doc.addEventListener ? "addEventListener" : "attachEvent");
    rem = (doc.addEventListener ? "removeEventListener" : "detachEvent");
    pre = (doc.addEventListener ? "" : "on");
    init = function(e) {
      if (e.type === "readystatechange" && doc.readyState !== "complete") {
        return;
      }
      (e.type === "load" ? win : doc)[rem](pre + e.type, init, false);
      if (!done && (done = true)) {
        return fn.call(win, e.type || e);
      }
    };
    poll = function() {
      var e;
      try {
        root.doScroll("left");
      } catch (_error) {
        e = _error;
        setTimeout(poll, 50);
        return;
      }
      return init("poll");
    };
    if (doc.readyState !== "complete") {
      if (doc.createEventObject && root.doScroll) {
        try {
          top = !win.frameElement;
        } catch (_error) {}
        if (top) {
          poll();
        }
      }
      doc[add](pre + "DOMContentLoaded", init, false);
      doc[add](pre + "readystatechange", init, false);
      return win[add](pre + "load", init, false);
    }
  };

  Dropzone._autoDiscoverFunction = function() {
    if (Dropzone.autoDiscover) {
      return Dropzone.discover();
    }
  };

  contentLoaded(window, Dropzone._autoDiscoverFunction);

}).call(this);

    return module.exports;
}));


/***/ }),
/* 568 */
/* unknown exports provided */
/* all exports used */
/*!*******************************************************************************!*\
  !*** ./src/Pim/Bundle/UIBundle/Resources/public/lib/jstree/jquery.hotkeys.js ***!
  \*******************************************************************************/
/***/ (function(module, exports) {

/*
 * jQuery Hotkeys Plugin
 * Copyright 2010, John Resig
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * Based upon the plugin by Tzury Bar Yochay:
 * http://github.com/tzuryby/hotkeys
 *
 * Original idea by:
 * Binny V A, http://www.openjs.com/scripts/events/keyboard_shortcuts/
*/

/*
 * One small change is: now keys are passed by object { keys: '...' }
 * Might be useful, when you want to pass some other data to your handler
 */

(function(jQuery){
	
	jQuery.hotkeys = {
		version: "0.8",

		specialKeys: {
			8: "backspace", 9: "tab", 10: "return", 13: "return", 16: "shift", 17: "ctrl", 18: "alt", 19: "pause",
			20: "capslock", 27: "esc", 32: "space", 33: "pageup", 34: "pagedown", 35: "end", 36: "home",
			37: "left", 38: "up", 39: "right", 40: "down", 45: "insert", 46: "del", 
			96: "0", 97: "1", 98: "2", 99: "3", 100: "4", 101: "5", 102: "6", 103: "7",
			104: "8", 105: "9", 106: "*", 107: "+", 109: "-", 110: ".", 111 : "/", 
			112: "f1", 113: "f2", 114: "f3", 115: "f4", 116: "f5", 117: "f6", 118: "f7", 119: "f8", 
			120: "f9", 121: "f10", 122: "f11", 123: "f12", 144: "numlock", 145: "scroll", 186: ";", 191: "/",
			220: "\\", 222: "'", 224: "meta"
		},
	
		shiftNums: {
			"`": "~", "1": "!", "2": "@", "3": "#", "4": "$", "5": "%", "6": "^", "7": "&", 
			"8": "*", "9": "(", "0": ")", "-": "_", "=": "+", ";": ": ", "'": "\"", ",": "<", 
			".": ">",  "/": "?",  "\\": "|"
		}
	};

	function keyHandler( handleObj ) {
		if ( typeof handleObj.data === "string" ) {
			handleObj.data = { keys: handleObj.data };
		}

		// Only care when a possible input has been specified
		if ( !handleObj.data || !handleObj.data.keys || typeof handleObj.data.keys !== "string" ) {
			return;
		}

		var origHandler = handleObj.handler,
			keys = handleObj.data.keys.toLowerCase().split(" "),
			textAcceptingInputTypes = ["text", "password", "number", "email", "url", "range", "date", "month", "week", "time", "datetime", "datetime-local", "search", "color", "tel"];
	
		handleObj.handler = function( event ) {
			// Don't fire in text-accepting inputs that we didn't directly bind to
			if ( this !== event.target && (/textarea|select/i.test( event.target.nodeName ) ||
				jQuery.inArray(event.target.type, textAcceptingInputTypes) > -1 ) ) {
				return;
			}

			var special = jQuery.hotkeys.specialKeys[ event.keyCode ],
				// character codes are available only in keypress
				character = event.type === "keypress" && String.fromCharCode( event.which ).toLowerCase(),
				modif = "", possible = {};

			// check combinations (alt|ctrl|shift+anything)
			if ( event.altKey && special !== "alt" ) {
				modif += "alt+";
			}

			if ( event.ctrlKey && special !== "ctrl" ) {
				modif += "ctrl+";
			}
			
			// TODO: Need to make sure this works consistently across platforms
			if ( event.metaKey && !event.ctrlKey && special !== "meta" ) {
				modif += "meta+";
			}

			if ( event.shiftKey && special !== "shift" ) {
				modif += "shift+";
			}

			if ( special ) {
				possible[ modif + special ] = true;
			}

			if ( character ) {
				possible[ modif + character ] = true;
				possible[ modif + jQuery.hotkeys.shiftNums[ character ] ] = true;

				// "$" can be triggered as "Shift+4" or "Shift+$" or just "$"
				if ( modif === "shift+" ) {
					possible[ jQuery.hotkeys.shiftNums[ character ] ] = true;
				}
			}

			for ( var i = 0, l = keys.length; i < l; i++ ) {
				if ( possible[ keys[i] ] ) {
					return origHandler.apply( this, arguments );
				}
			}
		};
	}

	jQuery.each([ "keydown", "keyup", "keypress" ], function() {
		jQuery.event.special[ this ] = { add: keyHandler };
	});

})( this.jQuery );


/***/ })
]));