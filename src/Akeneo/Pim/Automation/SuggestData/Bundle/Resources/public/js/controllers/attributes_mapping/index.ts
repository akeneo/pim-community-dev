import * as $ from 'jquery'

const BaseController = require('pim/controller/front');
const Router = require('pim/router');
const Routing = require('routing');

interface Families {
    [index: number]: { code: string; enabled: boolean; labels: Object };
}

/**
 * Attribute mapping index controller
 * This controller will load the first mapping, and do a redirect to the edit page.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IndexAttributeMappingController extends BaseController {
    public renderForm(): Object {
        return $.getJSON(Routing.generate('akeneo_sugggest_data_family_mapping_index', {limit: 1}))
            .then((data: Families) => {
                const firstFamily = data[0];

                Router.redirectToRoute('akeneo_suggest_data_family_mapping_edit', {
                    identifier: firstFamily.code
                });
            })
    }
}

export = IndexAttributeMappingController
