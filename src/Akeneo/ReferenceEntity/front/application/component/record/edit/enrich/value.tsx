import * as React from 'react';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import ChannelReference from 'akeneoreferenceentity/domain/model/channel-reference';
import Value from 'akeneoreferenceentity/domain/model/record/value';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import Record from 'akeneoreferenceentity/domain/model/record/record';
import {getDataFieldView} from 'akeneoreferenceentity/application/configuration/value';
import {getErrorsView} from 'akeneoreferenceentity/application/component/record/edit/validaton-error';
import __ from 'akeneoreferenceentity/tools/translator';

class ErrorBoundary extends React.Component<{fieldName: string}, {hasError: boolean, error: Error|null}> {
  constructor(props: {fieldName: string}) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error: Error) {
    return { hasError: true, error };
  }

  componentDidCatch(error: Error | null) {
    this.setState({ hasError: true, error: error || new Error('An error occured during the rendering of the field view') });
  }

  render() {
    if (this.state.hasError && null !== this.state.error) {
      return <div>{__('pim_reference_entity.record.error.value', {fieldName: this.props.fieldName})}</div>;
    }

    return this.props.children;
  }
}

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
            {value.attribute.getLabel(locale.stringValue())}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <ErrorBoundary fieldName={value.attribute.getLabel(locale.stringValue())}>
            <DataView value={value} onChange={onValueChange} onSubmit={onFieldSubmit} />
          </ErrorBoundary>
        </div>
        {getErrorsView(errors, value)}
      </div>
    );
  });
};
