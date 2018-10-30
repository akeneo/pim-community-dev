import * as React from 'react';
import * as ReactDOM from 'react-dom';
import * as $ from 'jquery';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
const routing = require('routing');
import Record, {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import recordFetcher from 'akeneoreferenceentity/infrastructure/fetcher/record';
import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
import ChannelReference from 'akeneoreferenceentity/domain/model/channel-reference';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';
import {denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
import {getLabel} from 'pimui/js/i18n';

export interface RecordSelectorProps {
  value: RecordCode[] | RecordCode | null;
  referenceEntityIdentifier: ReferenceEntityIdentifier;
  multiple?: boolean;
  locale: LocaleReference;
  channel: ChannelReference;
  placeholder: string;
  onChange: (value: RecordCode[] | RecordCode) => void;
}

type Select2Item = {id: string; text: string; original: NormalizedRecord};

export default class RecordSelector extends React.Component<RecordSelectorProps> {
  PAGE_SIZE = 200;
  static defaultProps = {
    multiple: false,
  };
  private el: any;

  formatItem(normalizedRecord: NormalizedRecord): Select2Item {
    return {
      id: normalizedRecord.code,
      text: getLabel(normalizedRecord.labels, this.props.locale.stringValue(), normalizedRecord.code),
      original: normalizedRecord,
    };
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
            return JSON.stringify({
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
                  field: 'search',
                  operator: '=',
                  value: term,
                },
              ],
            });
          },
          results: (result: {items: NormalizedRecord[]; total: number}) => {
            let selectedRecords: string[];
            if (this.props.multiple) {
              selectedRecords = (this.props.value as RecordCode[]).map((recordCode: RecordCode) =>
                recordCode.stringValue()
              );
            } else {
              selectedRecords = null === this.props.value ? [] : [(this.props.value as RecordCode).stringValue()];
            }

            const items = result.items
              .filter((normalizedRecord: NormalizedRecord) => !selectedRecords.includes(normalizedRecord.code))
              .map(this.formatItem.bind(this));

            return {
              more: this.PAGE_SIZE === items.length,
              results: items,
            };
          },
        },
        initSelection: async (element: any, callback: (item: Select2Item | Select2Item[]) => void) => {
          if (this.props.multiple) {
            const initialValues = element.val().split(',');
            const records = await Promise.all(
              initialValues.map(
                (recordCode: string): Promise<Record> => {
                  return recordFetcher.fetch(
                    this.props.referenceEntityIdentifier,
                    RecordCode.create(recordCode)
                  ) as Promise<Record>;
                }
              )
            );

            const normalizedRecords: NormalizedRecord[] = records.map((record: Record) => record.normalize());

            callback(normalizedRecords.map(this.formatItem.bind(this)));
          } else {
            const initialValue = element.val();
            recordFetcher
              .fetch(this.props.referenceEntityIdentifier, RecordCode.create(initialValue))
              .then((record: Record) => {
                callback(this.formatItem(record.normalize()));
              });
          }
        },
        formatSelection: (record: Select2Item, container: any) => {
          if (Array.isArray(record) && 0 === record.length) {
            return;
          }
          container
            .addClass('select2-search-choice-value')
            .append(
              $(
                `<img width="34" height="34" src="${getImageShowUrl(
                  denormalizeFile(record.original.image),
                  'thumbnail_small'
                )}"/><span>${record.text}</span>`
              )
            );
        },
        formatResult: (record: Select2Item, container: any) => {
          container
            .addClass('select2-search-choice-value')
            .append(
              $(
                `<img width="34" height="34" src="${getImageShowUrl(
                  denormalizeFile(record.original.image),
                  'thumbnail_small'
                )}"/><span>${record.text}</span>`
              )
            );
        },
      });
      this.el.on('change', (event: any) => {
        const newValue = this.props.multiple
          ? event.val.map((recordCode: string) => RecordCode.create(recordCode))
          : RecordCode.create(event.val);
        this.props.onChange(newValue);
      });
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
    return <input className="record-selector" type="hidden" value={this.normalizeValue(this.props.value)} />;
  }
}
