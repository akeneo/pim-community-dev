import {EventsHash} from 'backbone';

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const SimpleView = require('pim/common/simple-view');
const Router = require('pim/router');

/**
 * This module is used to render the description header; it inherits from simple-view and just redirects to the
 * right route on click event.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class DescriptionHeader extends SimpleView {
  /**
   * {@inheritdoc}
   */
  public events(): EventsHash {
    return {
      'click .link': this.redirect,
    };
  }

  private redirect(): boolean {
    Router.redirectToRoute('akeneo_franklin_insights_identifiers_mapping_edit');

    return false;
  }
}

export = DescriptionHeader;
