import * as React from 'react';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import ChannelReference from 'akeneoreferenceentity/domain/model/channel-reference';
import Value from 'akeneoreferenceentity/domain/model/record/value';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import Record from 'akeneoreferenceentity/domain/model/record/record';
import {getDataFieldView} from 'akeneoreferenceentity/application/configuration/value';
import {getErrorsView} from 'akeneoreferenceentity/application/component/record/edit/validaton-error';
import __ from 'akeneoreferenceentity/tools/translator';
import ErrorBoundary from 'akeneoreferenceentity/application/component/app/error-boundary';
import Flag from 'akeneoreferenceentity/tools/component/flag';
import {createLocaleFromCode} from 'akeneoreferenceentity/domain/model/locale';

export default (
  record: Record,
  channel: ChannelReference,
  locale: LocaleReference,
  errors: ValidationError[],
  onValueChange: (value: Value) => void,
  onFieldSubmit: () => void,
  rights: {
    record: {
      edit: boolean;
      delete: boolean;
    };
  }
) => {
  const visibleValues = record
    .getValueCollection()
    .getValuesForChannelAndLocale(channel, locale)
    .sort((firstValue: Value, secondValue: Value) => firstValue.attribute.order - secondValue.attribute.order);

  return visibleValues.map((value: Value) => {
    const DataView = getDataFieldView(value);

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
            <span
              className={`AknBadge AknBadge--small AknBadge--highlight AknBadge--floating ${
                value.attribute.isRequired && value.data.isEmpty() ? '' : 'AknBadge--hidden'
              }`}
            />
            {value.attribute.getLabel(locale.stringValue())}
          </label>
          <span className="AknFieldContainer-fieldInfo">
            <span>
              <span>{value.attribute.valuePerChannel ? value.channel.stringValue() : null}</span>
              &nbsp;
              <span>
                {value.attribute.valuePerLocale ? (
                  <Flag locale={createLocaleFromCode(value.locale.stringValue())} displayLanguage={true} />
                ) : null}
              </span>
            </span>
          </span>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <ErrorBoundary
            errorMessage={__('pim_reference_entity.record.error.value', {
              fieldName: value.attribute.getLabel(locale.stringValue()),
            })}
          >
            <DataView
              value={value}
              onChange={onValueChange}
              onSubmit={onFieldSubmit}
              channel={channel}
              locale={locale}
              rights={rights}
            />
          </ErrorBoundary>
        </div>
        {getErrorsView(errors, value)}
      </div>
    );
  });
};
