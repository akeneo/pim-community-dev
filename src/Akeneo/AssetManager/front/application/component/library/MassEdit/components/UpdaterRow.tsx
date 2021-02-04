import React from 'react';
import {CloseIcon, IconButton, Table} from 'akeneo-design-system';
import {ValidationError} from 'akeneoassetmanager/platform/model/validation-error';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {getLabel} from 'pimui/js/i18n';
import {Updater} from '../model/updater';
import {getFieldView} from '../../../../configuration/value';
import {useConfig} from '../../../../hooks/useConfig';
import EditionValue from '../../../../../domain/model/asset/edition-value';

type UpdaterRowProps = {
  updater: Updater;
  locale: string;
  onRemove: (updater: Updater) => void;
  onChange: (updater: Updater) => void;
  readOnly: boolean;
  errors: ValidationError[];
};

const UpdaterRow = ({updater, locale, readOnly, errors, onChange, onRemove}: UpdaterRowProps) => {
  const translate = useTranslate();
  const config = useConfig('value');
  const InputView = getFieldView(config)(updater);
  const handleChange = (editionValue: EditionValue) => {
    onChange({...updater, data: editionValue.data})
  }

  return (
    <Table.Row>
      <Table.Cell>{getLabel(updater.attribute.labels, locale, updater.attribute.code)}</Table.Cell>
      <Table.Cell>
        <InputView
          canEditData={!readOnly}
          channel={updater.channel}
          locale={updater.channel}
          onChange={handleChange}
          onSubmit={() => {}}
          value={updater}
        />
        {errors.map(error => JSON.stringify(error)).join(', ')}
      </Table.Cell>
      <Table.Cell>
        {updater.channel}
        {updater.locale}
        {updater.action}
      </Table.Cell>
      <Table.Cell>
        <IconButton
          level="tertiary"
          icon={<CloseIcon />}
          ghost="borderless"
          title={translate('pim_common.remove')}
          onClick={() => onRemove(updater)}
        />
      </Table.Cell>
    </Table.Row>
  );
};

export {UpdaterRow}
