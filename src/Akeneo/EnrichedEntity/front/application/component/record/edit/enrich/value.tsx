import * as React from 'react';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import ChannelReference from 'akeneoreferenceentity/domain/model/channel-reference';
import Value from 'akeneoreferenceentity/domain/model/record/value';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import Record from 'akeneoreferenceentity/domain/model/record/record';
import {getDataView} from 'akeneoreferenceentity/application/configuration/value';
import {getErrorsView} from 'akeneoreferenceentity/application/component/record/edit/validaton-error';

export default (
  record: Record,
  channel: ChannelReference,
  locale: LocaleReference,
  errors: ValidationError[],
  onValueChange: (value: Value) => void,
  onFieldSubmit: () => void
) => {
  const visibleValues = record
    .getValueCollection()
    .getValuesForChannelAndLocale(channel, locale)
    .sort((firstValue: Value, secondValue: Value) => firstValue.attribute.order - secondValue.attribute.order);

  return visibleValues.map((value: Value) => {
    const DataView = getDataView(value);

    return (
      <div
        key={value.attribute.getIdentifier().stringValue()}
        className="AknFieldContainer"
        data-code={value.attribute.getCode().stringValue()}
      >
        <div className="AknFieldContainer-header AknFieldContainer-header--light AknFieldContainer-header AknFieldContainer-header--light--small">
          <label
            title={value.attribute.getLabel(locale.stringValue())}
            className="AknFieldContainer-label"
            htmlFor={`pim_reference_entity.record.enrich.${value.attribute.getCode().stringValue()}`}
          >
            {value.attribute.getLabel(locale.stringValue())}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <DataView value={value} onChange={onValueChange} onSubmit={onFieldSubmit} />
        </div>
        {getErrorsView(errors, value)}
      </div>
    );
  });
};
