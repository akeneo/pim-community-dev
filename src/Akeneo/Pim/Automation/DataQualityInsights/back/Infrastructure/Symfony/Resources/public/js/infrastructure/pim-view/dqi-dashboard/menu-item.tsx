/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const BaseItem = require('pim/menu/item');

/**
 * @author Anais Baune Lemaire <anais.lemaire@akeneo.com>
 */
class MenuItem extends BaseItem {
  public render(): any {
      return BaseItem.prototype.render.apply(this);

  }
}

export = MenuItem;
