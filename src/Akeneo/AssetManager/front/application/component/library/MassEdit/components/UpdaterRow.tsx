import React from 'react';
import {CloseIcon, IconButton, Table} from 'akeneo-design-system';
import {ValidationError} from 'akeneoassetmanager/platform/model/validation-error';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {getLabel} from 'pimui/js/i18n';
import {Updater} from '../model/updater';

type UpdaterRowProps = {
  updater: Updater;
  locale: string;
  onRemove: (updater: Updater) => void;
  onChange: (updater: Updater) => void;
  readOnly: boolean;
  errors: ValidationError[];
};
const UpdaterRow = ({updater, locale, readOnly, errors, onRemove}: UpdaterRowProps) => {
  const translate = useTranslate();

  return (
    <Table.Row>
      <Table.Cell>{getLabel(updater.attribute.labels, locale, updater.attribute.code)}</Table.Cell>
      <Table.Cell>
        {JSON.stringify(updater.data)}
        {readOnly ? 'readonly' : 'editable'}
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
