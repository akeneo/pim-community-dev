import * as React from 'react';
import LocaleReference from 'akeneoenrichedentity/domain/model/locale-reference';
import ChannelReference from 'akeneoenrichedentity/domain/model/channel-reference';
import Value from 'akeneoenrichedentity/domain/model/record/value';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import {getErrorsView} from 'akeneoenrichedentity/application/component/app/validation-error';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import {getDataView} from 'akeneoenrichedentity/application/configuration/data';

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
    .sort(
      (firstValue: Value, secondValue: Value) => firstValue.getAttribute().order - secondValue.getAttribute().order
    );

  return visibleValues.map((value: Value) => {
    const DataView = getDataView(value);
    return (
      <div
        key={value
          .getAttribute()
          .getIdentifier()
          .stringValue()}
        className="AknFieldContainer"
        data-code={value.getAttribute().getCode()}
      >
        <div className="AknFieldContainer-header">
          <label
            title={value.getAttribute().getLabel(locale.stringValue())}
            className="AknFieldContainer-label"
            htmlFor={`pim_enriched_entity.record.enrich.${value.getAttribute().getCode()}`}
          >
            {value.getAttribute().getLabel(locale.stringValue())}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <DataView value={value} onChange={onValueChange} />
        </div>
        {getErrorsView(errors, `values.${value.getAttribute().getCode()}`)}
      </div>
    );
  });
};
