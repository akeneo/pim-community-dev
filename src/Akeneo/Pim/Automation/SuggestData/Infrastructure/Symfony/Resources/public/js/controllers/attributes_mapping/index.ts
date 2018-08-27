const BaseController = require('pim/controller/front');
const FetcherRegistry = require('pim/fetcher-registry');
const Router = require('pim/router');

interface Families {
  [index: string]: Object;
}

/**
 * Attribute mapping index controller
 * This controller will load the first mapping, and do a redirect to the edit page.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IndexAttributeMappingController extends BaseController {
  public renderForm(): Object {
    return FetcherRegistry.getFetcher('family')
      .fetchAll()
      .then((families: Families) => {
        let firstFamilyCode = Object.keys(families).sort()[0];

        Router.redirectToRoute('akeneo_suggest_data_attributes_mapping_edit', {familyCode: firstFamilyCode});
      });
  }
}

export = IndexAttributeMappingController
