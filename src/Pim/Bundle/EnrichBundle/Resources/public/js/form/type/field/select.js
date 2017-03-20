'use strict';

/**
 * Select field type
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'text!pim/template/form/type/field/input'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        FetcherRegistry,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            requiredLabel: __('pim_enrich.form.required'),
            fieldPrefix: null,
            config: {
                isRequired: false,
                isDisabled: false,
                allowMultiple: false,
                label: null
            },
            error: null,
            name: null,
            value: null,
            selections: null,
            events: {
                'change input': 'updateModel'
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
            },

            updateModel: function (event) {
            }
        });
    }
);
