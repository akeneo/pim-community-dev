import React, {memo, useCallback} from 'react';
import styled from 'styled-components';
import {Checkbox, getColor} from 'akeneo-design-system';
import {NormalizedItemRecord} from 'akeneoreferenceentity/domain/model/record/record';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';
import {denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
import {getLabel} from 'pimui/js/i18n';
import Completeness from 'akeneoreferenceentity/domain/model/record/completeness';
import {CompletenessBadge} from 'akeneoreferenceentity/application/component/app/completeness';

const CheckboxCell = styled.td`
  padding: 0 10px;
  background: ${getColor('white')};
  min-width: 40px;
`;

type CommonRowProps = {
  record: NormalizedItemRecord;
  locale: string;
  placeholder?: boolean;
  canSelectRecord?: boolean;
  onRedirectToRecord?: (record: NormalizedItemRecord) => void;
  onSelectionChange?: (recordCode: string, newValue: boolean) => void;
  isItemSelected: (recordCode: string) => boolean;
};

const CommonRow = memo(
  ({
    record,
    locale,
    placeholder = false,
    canSelectRecord = false,
    isItemSelected,
    onRedirectToRecord,
    onSelectionChange,
  }: CommonRowProps) => {
    const handleSelect = useCallback(
      (newValue: boolean) => {
        onSelectionChange?.(record.code, newValue);
      },
      [onSelectionChange, record]
    );

    if (true === placeholder) {
      return (
        <tr>
          <td className="AknGrid-bodyCell AknGrid-bodyCell--image">
            <div className="AknGrid-bodyCellContainer AknLoadingPlaceHolder" />
          </td>
          <td className="AknGrid-bodyCell" colSpan={3}>
            <div className="AknGrid-bodyCellContainer AknLoadingPlaceHolder" />
          </td>
        </tr>
      );
    }

    const label = getLabel(record.labels, locale, record.code);

    return (
      <tr
        className="AknGrid-bodyRow"
        data-identifier={record.identifier}
        onClick={event => {
          if (undefined !== onRedirectToRecord) {
            event.preventDefault();
            onRedirectToRecord(record);
          } else {
            handleSelect(!isItemSelected(record.code));
          }

          return false;
        }}
      >
        <CheckboxCell>
          {canSelectRecord && <Checkbox checked={isItemSelected(record.code)} onChange={handleSelect} />}
        </CheckboxCell>
        <td className="AknGrid-bodyCell AknGrid-bodyCell--image">
          <img
            className="AknGrid-image AknLoadingPlaceHolder"
            width="44"
            height="44"
            src={getImageShowUrl(denormalizeFile(record.image), 'thumbnail_small')}
          />
        </td>
        <td className="AknGrid-bodyCell" title={label}>
          {label}
        </td>
        <td className="AknGrid-bodyCell AknGrid-bodyCell--identifier" title={record.code}>
          {record.code}
        </td>
        <td className="AknGrid-bodyCell">
          <CompletenessBadge completeness={Completeness.createFromNormalized(record.completeness)} />
        </td>
      </tr>
    );
  }
);

type CommonRowsProps = {
  records: NormalizedItemRecord[];
  locale: string;
  placeholder: boolean;
  recordCount: number;
  canSelectRecord?: boolean;
  onRedirectToRecord?: (record: NormalizedItemRecord) => void;
  onSelectionChange?: (recordCode: string, newValue: boolean) => void;
  isItemSelected: (recordCode: string) => boolean;
};

const CommonRows = memo(
  ({
    records,
    locale,
    placeholder,
    recordCount,
    isItemSelected,
    canSelectRecord = false,
    onRedirectToRecord,
    onSelectionChange,
  }: CommonRowsProps) => {
    if (placeholder) {
      const record = {
        identifier: '',
        reference_entity_identifier: '',
        code: '',
        labels: {},
        image: null,
        values: [],
        completeness: {
          complete: 0,
          required: 0,
        },
      };

      const placeholderCount = recordCount < 30 ? recordCount : 30;

      return (
        <>
          {[...Array(placeholderCount)].map((_, key) => (
            <CommonRow
              placeholder={placeholder}
              key={key}
              record={record}
              locale={locale}
              isItemSelected={() => false}
              onRedirectToRecord={() => {}}
            />
          ))}
        </>
      );
    }

    return (
      <>
        {records.map(record => (
          <CommonRow
            placeholder={false}
            key={record.identifier}
            record={record}
            locale={locale}
            onRedirectToRecord={onRedirectToRecord}
            isItemSelected={isItemSelected}
            onSelectionChange={onSelectionChange}
            canSelectRecord={canSelectRecord}
          />
        ))}
      </>
    );
  }
);

export {CommonRows};
