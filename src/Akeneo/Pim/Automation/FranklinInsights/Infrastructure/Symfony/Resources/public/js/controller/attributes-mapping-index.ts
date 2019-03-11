/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as $ from 'jquery';

const __ = require('oro/translator');
const BaseController = require('pim/controller/front');
const FetcherRegistry = require('pim/fetcher-registry');
const Router = require('pim/router');

interface Family {
  code: string;
}

/**
 * Attribute mapping index controller
 * This controller will load the first mapping, and do a redirect to the edit page.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IndexAttributeMappingController extends BaseController {
  public renderForm(): object {
    return FetcherRegistry.getFetcher('attributes-mapping-by-family')
      .fetchAll()
      .then((families: Family[]) => {
        if (0 === Object.keys(families).length) {
          return $.Deferred().reject({
            status: 404,
            statusText: __('akeneo_franklin_insights.entity.attributes_mapping.module.index.error'),
          });
        }

        const familyCode = families.map((family) => family.code).sort()[0];

        Router.redirectToRoute('akeneo_franklin_insights_attributes_mapping_edit', {familyCode});

        return undefined;
      });
  }
}

export = IndexAttributeMappingController;
