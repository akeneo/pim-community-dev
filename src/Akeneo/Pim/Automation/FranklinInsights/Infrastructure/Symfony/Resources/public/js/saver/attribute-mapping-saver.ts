/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const BaseSaver = require('pim/form/common/save-form');

class AttributeMappingSaver extends BaseSaver {
  public configure() {
    this.listenTo(this.getRoot(), 'family_mapping_saved', () => {
      this.hideLoadingMask();
    });
    return super.configure();
  }

  public save() {
    this.showLoadingMask();
    this.getRoot().trigger('save_family_mapping');
  }
}

export = AttributeMappingSaver;
