/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const BaseSaver = require('pim/saver/entity-saver');
const Routing = require('routing');

/**
 * Attribute Options Mapping Saver
 *
 * As the URL to save an Attribute Options Mapping needs both Family code and Franklin Attribute code, we need
 * to do custom methods to generate this URL.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class Saver extends BaseSaver {
  /**
   * Set the catalog Family code
   *
   * @param { string } familyCode
   * @return Saver
   */
  public static setFamilyCode(familyCode: string): Saver {
    this.familyCode = familyCode;

    return this;
  }

  /**
   * Set the Franklin Attribute code
   *
   * @param { string } franklinAttributeCode
   * @return Saver
   */
  public static setFranklinAttributeCode(franklinAttributeCode: string): Saver {
    this.franklinAttributeCode = franklinAttributeCode;

    return this;
  }

  /**
   * {@inheritdoc}
   */
  protected static getUrl(): string {
    return Routing.generate(this.url, { identifier: this.familyCode, attributeCode: this.franklinAttributeCode });
  }
  private static familyCode: string;
  private static franklinAttributeCode: string;
}

export = Saver;
