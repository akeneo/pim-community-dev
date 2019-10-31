/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {AttributesMapping} from '../../model/attributes-mapping';

const BaseState = require('pim/form/common/state');

/**
 * State module for the attribute mapping screen.
 */
class State extends BaseState {
  public hasModelChanged(): boolean {
    const {hasUnsavedChanges} = this.getFormData() as AttributesMapping;

    return hasUnsavedChanges;
  }
}

export = State;
