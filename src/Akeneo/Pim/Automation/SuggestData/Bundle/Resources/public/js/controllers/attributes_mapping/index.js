'use strict';

/**
 * Attribute mapping index controller
 * This controller will load the first mapping, and do a redirect to the edit page.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    ['pim/controller/front', 'pim/router'],
    function (BaseController, Router) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderForm: function (route) {
                $.getJSON(Routing.generate('akeneo_sugggest_data_family_mapping_index', {limit: 1}))
                    .then((data) => {
                        const firstFamily = data[0];

                        return Router.redirectToRoute('akeneo_suggest_data_family_mapping_edit', {
                            identifier: firstFamily.code
                        });
                    }
                )
            }
        });
    }
);
