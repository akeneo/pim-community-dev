const BaseSaver = require('pim/saver/entity-saver');
const Routing = require('routing');

class Saver extends BaseSaver {
  private static family: string;
  private static pimAiAttributeCode: string;

  /**
   * {@inheritdoc}
   */
  protected static getUrl(): string {
    return Routing.generate(this.url, { identifier: this.family, attributeCode: this.pimAiAttributeCode });
  }

  /**
   * TODO
   *
   * @param family
   */
  static setFamilyCode(family: string): Saver {
    this.family = family;

    return this;
  }

  /**
   * TODO
   *
   * @param pimAiAttributeCode
   */
  static setPimAiAttributeCode(pimAiAttributeCode: string): Saver {
    this.pimAiAttributeCode = pimAiAttributeCode;

    return this;
  }
}

export = Saver
