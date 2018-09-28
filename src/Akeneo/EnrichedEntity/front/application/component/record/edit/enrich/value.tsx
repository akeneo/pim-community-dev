import * as React from 'react';
import LocaleReference from 'akeneoenrichedentity/domain/model/locale-reference';
import ChannelReference from 'akeneoenrichedentity/domain/model/channel-reference';
import Value from 'akeneoenrichedentity/domain/model/record/value';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import {getDataView} from 'akeneoenrichedentity/application/configuration/value';
import {getErrorsView} from 'akeneoenrichedentity/application/component/record/edit/validaton-error';

export default (
  record: Record,
  channel: ChannelReference,
  locale: LocaleReference,
  errors: ValidationError[],
  onValueChange: (value: Value) => void
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
        <div className="AknFieldContainer-header AknFieldContainer-header--small">
          <label
            title={value.attribute.getLabel(locale.stringValue())}
            className="AknFieldContainer-label"
            htmlFor={`pim_enriched_entity.record.enrich.${value.attribute.getCode().stringValue()}`}
          >
            {value.attribute.getLabel(locale.stringValue())}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <DataView value={value} onChange={onValueChange} />
        </div>
        {getErrorsView(errors, value)}
      </div>
    );
  });
};
