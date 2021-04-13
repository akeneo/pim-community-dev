import React, {memo} from 'react';
import styled from 'styled-components';
import {DeleteIcon, EditIcon, IconButton, ViewIcon} from 'akeneo-design-system';
import {useRoute, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {NormalizedRecord} from 'akeneoreferenceentity/domain/model/record/record';
import RecordCode from 'akeneoreferenceentity/domain/model/record/code';
import {getLabel} from 'pimui/js/i18n';

const Buttons = styled.div`
  display: flex;
  gap: 10px;
`;

type ActionRowProps = {
  record: NormalizedRecord;
  locale: string;
  placeholder?: boolean;
  rights: {
    record: {
      edit: boolean;
      delete: boolean;
    };
  };
  onDeleteRecord: (recordCode: RecordCode, label: string) => void;
};

const ActionRow = memo(({record, locale, placeholder = false, rights, onDeleteRecord}: ActionRowProps) => {
  const translate = useTranslate();
  const recordRoute = useRoute('akeneo_reference_entities_record_edit', {
    referenceEntityIdentifier: record.reference_entity_identifier,
    recordCode: record.code,
    tab: 'enrich',
  });

  if (true === placeholder) {
    return (
      <tr>
        <td className="AknGrid-bodyCell">
          <div className="AknGrid-bodyCellContainer AknLoadingPlaceHolder" />
        </td>
      </tr>
    );
  }

  const label = getLabel(record.labels, locale, record.code);

  return (
    <tr className="AknGrid-bodyRow" data-identifier={record.identifier}>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--action">
        <Buttons>
          <IconButton
            level="tertiary"
            ghost="borderless"
            title={translate(`pim_reference_entity.record.button.${rights.record.edit ? 'edit' : 'view'}`)}
            icon={rights.record.edit ? <EditIcon /> : <ViewIcon />}
            href={`#${recordRoute}`}
            data-identifier={record.identifier}
          />
          {rights.record.delete && (
            <IconButton
              icon={<DeleteIcon />}
              level="tertiary"
              ghost="borderless"
              title={translate('pim_reference_entity.record.button.delete')}
              data-identifier={record.identifier}
              onClick={() => onDeleteRecord(RecordCode.create(record.code), label)}
            />
          )}
        </Buttons>
      </td>
    </tr>
  );
});

type ActionRowsProps = {
  records: NormalizedRecord[];
  locale: string;
  placeholder: boolean;
  onDeleteRecord: (recordCode: RecordCode, label: string) => void;
  recordCount: number;
  rights: {
    record: {
      edit: boolean;
      delete: boolean;
    };
  };
};

const ActionRows = memo(({records, locale, placeholder, onDeleteRecord, recordCount, rights}: ActionRowsProps) => {
  if (placeholder) {
    const record = {
      identifier: '',
      reference_entity_identifier: '',
      code: '',
      labels: {},
      image: null,
      values: [],
    };

    const placeholderCount = recordCount < 30 ? recordCount : 30;

    return (
      <>
        {[...Array(placeholderCount)].map((_, key) => (
          <ActionRow
            placeholder={true}
            key={key}
            record={record}
            locale={locale}
            onDeleteRecord={() => {}}
            rights={rights}
          />
        ))}
      </>
    );
  }

  return (
    <>
      {records.map(record => (
        <ActionRow
          placeholder={false}
          key={record.identifier}
          record={record}
          locale={locale}
          onDeleteRecord={onDeleteRecord}
          rights={rights}
        />
      ))}
    </>
  );
});

export {ActionRows};
