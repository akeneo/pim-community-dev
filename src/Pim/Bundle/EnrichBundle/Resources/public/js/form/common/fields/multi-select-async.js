/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define(
    [
        'jquery',
        'pim/form/common/fields/simple-select-async'
    ],
    function (
        $,
        SimpleSelectAsync
    ) {
        return SimpleSelectAsync.extend({
            /**
             * {@inheritdoc}
             */
            getSelect2Options() {
                const parent = SimpleSelectAsync.prototype.getSelect2Options.apply(this, arguments);
                parent.multiple = true;

                return parent;
            },

            /**
             * {@inheritdoc}
             */
            select2InitSelection(element, callback) {
                const strValues = $(element).val();
                const values = strValues.split(',');
                if (values.length > 0) {
                    $.ajax({
                        url: this.choiceUrl,
                        data: { options: { identifiers: strValues } },
                        type: this.choiceVerb
                    }).then(response => {
                        let selecteds = Object.values(response.results).filter((item) => {
                            return values.indexOf(item.code) > -1;
                        });

                        if (selecteds.length === 0) {
                            selecteds = Object.values(response.results).filter((item) => {
                                return values.indexOf(item.id) > -1;
                            });
                        } else {
                            selecteds = selecteds.map((selected) => {
                                return this.convertBackendItem(selected);
                            });
                        }
                        callback(selecteds);
                    });
                }
            }
        });
    });
