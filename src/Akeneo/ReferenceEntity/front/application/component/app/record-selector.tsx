import * as React from 'react';
import * as ReactDOM from 'react-dom';
import * as $ from 'jquery';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
const routing = require('routing');
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import recordFetcher, {RecordResult} from 'akeneoreferenceentity/infrastructure/fetcher/record';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import ChannelReference from 'akeneoreferenceentity/domain/model/channel-reference';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';
import {denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
import {getLabel} from 'pimui/js/i18n';

const renderRow = (label: string, record: NormalizedRecord, withLink: boolean) => {
  return `
  <img width="34" height="34" src="${getImageShowUrl(denormalizeFile(record.image), 'thumbnail_small')}"/>
  <span class="select2-result-label-main">${label}</span>
  <span class="select2-result-label-hint">${record.code}</span>
  ${
    withLink
      ? `<a
      class="select2-result-label-link AknIconButton AknIconButton--small AknIconButton--link"
      data-reference-entity-identifier="${record.reference_entity_identifier}"
      data-record-code="${record.code}"
      target="_blank"
      href="#${routing.generate('akeneo_reference_entities_record_edit', {
        referenceEntityIdentifier: record.reference_entity_identifier,
        recordCode: record.code,
        tab: 'enrich',
      })}"></a>`
      : ''
  }`;
};

export type RecordSelectorProps = {
  value: RecordCode[] | RecordCode | null;
  referenceEntityIdentifier: ReferenceEntityIdentifier;
  multiple?: boolean;
  readOnly?: boolean;
  locale: LocaleReference;
  channel: ChannelReference;
  placeholder: string;
  onChange: (value: RecordCode[] | RecordCode | null) => void;
};

type Select2Item = {id: string; text: string; original: NormalizedRecord};

export default class RecordSelector extends React.Component<RecordSelectorProps & any> {
  PAGE_SIZE = 200;
  static defaultProps = {
    multiple: false,
    readOnly: false,
  };
  private el: any;

  formatItem(normalizedRecord: NormalizedRecord): Select2Item {
    return {
      id: normalizedRecord.code,
      text: getLabel(normalizedRecord.labels, this.props.locale.stringValue(), normalizedRecord.code),
      original: normalizedRecord,
    };
  }

  getSelectedRecordCode(value: null | RecordCode[] | RecordCode, multiple: boolean) {
    if (multiple) {
      return (value as RecordCode[]).map((recordCode: RecordCode) => recordCode.stringValue());
    } else {
      return null === value ? [] : [(value as RecordCode).stringValue()];
    }
  }

  componentDidMount() {
    this.el = $(ReactDOM.findDOMNode(this) as Element);

    if (undefined !== this.el.select2) {
      this.el.select2({
        allowClear: true,
        placeholder: this.props.placeholder,
        placeholderOption: '',
        multiple: this.props.multiple,
        dropdownCssClass: this.props.multiple ? 'record-selector-multi-dropdown' : 'record-selector-dropdown',
        ajax: {
          url: routing.generate('akeneo_reference_entities_record_index_rest', {
            referenceEntityIdentifier: this.props.referenceEntityIdentifier.stringValue(),
          }),
          quietMillis: 250,
          cache: true,
          type: 'PUT',
          params: {contentType: 'application/json;charset=utf-8'},
          data: (term: string, page: number): string => {
            const selectedRecords = this.getSelectedRecordCode(this.props.value, this.props.multiple as boolean);
            const searchQuery = {
              channel: this.props.channel.stringValue(),
              locale: this.props.locale.stringValue(),
              size: this.PAGE_SIZE,
              page: page - 1,
              filters: [
                {
                  field: 'reference_entity',
                  operator: '=',
                  value: this.props.referenceEntityIdentifier.stringValue(),
                },
                {
                  field: 'code_label',
                  operator: '=',
                  value: term,
                },
                {
                  field: 'code',
                  operator: 'NOT IN',
                  value: selectedRecords,
                },
              ],
            };

            return JSON.stringify(searchQuery);
          },
          results: (result: {items: NormalizedRecord[]; matchesCount: number}) => {
            const items = result.items.map(this.formatItem.bind(this));

            return {
              more: this.PAGE_SIZE === items.length,
              results: items,
            };
          },
        },
        initSelection: async (element: any, callback: (item: Select2Item | Select2Item[]) => void) => {
          if (this.props.multiple) {
            const initialValues = element.val().split(',');
            const initQuery = {
              channel: this.props.channel.stringValue(),
              locale: this.props.locale.stringValue(),
              size: 200,
              page: 0,
              filters: [
                {
                  field: 'reference_entity',
                  operator: '=',
                  value: this.props.referenceEntityIdentifier.stringValue(),
                },
                {
                  field: 'code',
                  operator: 'IN',
                  value: initialValues,
                },
              ],
            };

            const result = await recordFetcher.search(initQuery);

            callback(result.items.map(this.formatItem.bind(this)));
          } else {
            const initialValue = element.val();
            recordFetcher
              .fetch(this.props.referenceEntityIdentifier, RecordCode.create(initialValue))
              .then((recordResult: RecordResult) => {
                callback(this.formatItem(recordResult.record.normalize()));
              });
          }
        },
        formatSelection: (record: Select2Item, container: any) => {
          if (Array.isArray(record) && 0 === record.length) {
            return;
          }
          container.addClass('select2-search-choice-value').append($(renderRow(record.text, record.original, false)));
        },
        formatResult: (record: Select2Item, container: any) => {
          container.addClass('select2-search-choice-value').append($(renderRow(record.text, record.original, true)));
        },
      });

      this.el.on('change', (event: any) => {
        const newValue = this.props.multiple
          ? event.val.map((recordCode: string) => RecordCode.create(recordCode))
          : '' === event.val
          ? null
          : RecordCode.create(event.val);
        this.props.onChange(newValue);
      });

      // Prevent the onSelect event to apply it even when the options are null
      const select2 = this.el.data('select2');
      select2.onSelect = (function(fn) {
        return function(_data: any, options: any) {
          if (null === options || 'A' !== options.target.nodeName) {
            //@ts-ignore Dirty but comming from select2...
            fn.apply(this, arguments);
          }
        };
      })(select2.onSelect);
    } else {
      this.el.prop('type', 'text');
    }
  }

  componentWillUnmount() {
    this.el.off('change');
  }

  componentDidUpdate(prevProps: RecordSelectorProps) {
    if (this.props.value !== prevProps.value) {
      this.el.val(this.normalizeValue(this.props.value)).trigger('change.select2');
    }
  }

  normalizeValue(value: RecordCode[] | RecordCode | null): string {
    if (null === value) {
      return '';
    }

    return this.props.multiple
      ? (value as RecordCode[]).map((recordCode: RecordCode) => recordCode.stringValue()).join(',')
      : (value as RecordCode).stringValue();
  }

  render(): JSX.Element | JSX.Element[] {
    const {referenceEntityIdentifier, ...props} = this.props;
    const className = `record-selector ${this.props.readOnly ? 'record-selector--disabled' : ''}`;

    return (
      <React.Fragment>
        <input
          className={className}
          {...props}
          type="hidden"
          value={this.normalizeValue(this.props.value)}
          disabled={this.props.readOnly}
          onChange={(event: any) => {
            const newValue = this.props.multiple
              ? event.target.value.split(',').map((recordCode: string) => RecordCode.create(recordCode))
              : '' === event.target.value
              ? null
              : RecordCode.create(event.target.value);
            this.props.onChange(newValue);
          }}
        />
        {!props.multiple && null !== this.props.value ? (
          <a
            className="AknFieldContainer-inputLink AknIconButton AknIconButton--small AknIconButton--link"
            href={`#${routing.generate('akeneo_reference_entities_record_edit', {
              referenceEntityIdentifier: referenceEntityIdentifier.stringValue(),
              recordCode: this.props.value.stringValue(),
              tab: 'enrich',
            })}`}
            target="_blank"
          />
        ) : null}
      </React.Fragment>
    );
  }
}
