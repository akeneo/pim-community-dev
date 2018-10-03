const BaseState = require('pim/form/common/state');

/**
 * State module for the mapping screen.
 * The goal of this module is to not detect state as changed if the value is changed from null to ''.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class State extends BaseState {
  /**
   * {@inheritdoc}
   */
  public hasModelChanged(): boolean {
    return JSON.stringify(this.emptyToNullValues(JSON.parse(this.state))) !==
      JSON.stringify(this.emptyToNullValues(this.getFormData()));
  }

  /**
   * Transform '' values to null
   *
   * @param object: any
   *
   * @returns any
   */
  public emptyToNullValues(object: any): any {
    return Object.keys(object).reduce((accumulator: any, identifier: string) => {
      accumulator[identifier] = object[identifier] === '' ? null : object[identifier];

      return accumulator;
    }, {});
  }
}

export = State;
