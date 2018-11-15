import * as React from 'react';
import * as ReactDOM from 'react-dom';
import RecordSelector from 'akeneoreferenceentity/application/component/app/record-selector';
import {createIdentifier as createReferenceEntityIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import RecordCode, {createCode as createRecordCode} from 'akeneoreferenceentity/domain/model/record/code';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import ChannelReference from 'akeneoreferenceentity/domain/model/channel-reference';
import __ from 'akeneoreferenceentity/tools/translator';

const Field = require('pim/field');
const UserContext = require('pim/user-context');

/**
 * Reference entity field for attribute form
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityField extends (Field as {new (config: any): any}) {
  constructor(config: any) {
    super(config);

    this.fieldType = 'akeneo-reference-entity-field';
  }

  renderInput(templateContext: any) {
    const container = document.createElement('div');
    let valueData = null;
    if (null !== templateContext.value.data) {
      valueData = createRecordCode(templateContext.value.data);
    }

    ReactDOM.render(
      <RecordSelector
        referenceEntityIdentifier={createReferenceEntityIdentifier(templateContext.attribute.reference_data_name)}
        value={valueData}
        locale={LocaleReference.create(UserContext.get('catalog_default_locale'))}
        channel={ChannelReference.create(UserContext.get('catalog_default_scope'))}
        multiple={false}
        placeholder={__('pim_reference_entity.record.selector.no_value')}
        onChange={(recordCode: RecordCode) => {
          this.errors = [];
          this.setCurrentValue('' === recordCode.stringValue() ? null : recordCode.stringValue());
          this.render();
        }}
      />,
      container
    );
    return container;
  }
}

module.exports = ReferenceEntityField;
