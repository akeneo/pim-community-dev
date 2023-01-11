import React from 'react';
import {Helper, SelectInput, SkeletonPlaceholder, Table} from 'akeneo-design-system';
import {ChannelCode, LabelCollection, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {useGetScopes} from '../hooks';

type ScopeSelectorProps = {
  value: ChannelCode | null;
  onChange: (code: string | null) => void;
};

const ScopeSelector: React.FC<ScopeSelectorProps> = ({value, onChange}) => {
  const translate = useTranslate();
  const currentCatalogLocale = useUserContext().get('catalogLocale');
  const {data: options, isLoading, error} = useGetScopes();

  const getLabel = (labels: LabelCollection, code: string) => labels[currentCatalogLocale] || `[${code}]`;

  if (error) {
    return <Helper level={'error'}>{translate('pim_error.general')}</Helper>;
  }

  return isLoading ? (
    <Table.Row>
      <Table.Cell>
        <SkeletonPlaceholder>This is a loading channel</SkeletonPlaceholder>
      </Table.Cell>
    </Table.Row>
  ) : (
    <SelectInput
      value={value}
      emptyResultLabel={translate('pim_common.no_result')}
      openLabel={translate('pim_common.channel')}
      onChange={onChange}
      placeholder={translate('pim_common.channel')}
    >
      {options?.map(({code, labels}) => (
        <SelectInput.Option value={code} key={code}>
          {getLabel(labels, code)}
        </SelectInput.Option>
      ))}
    </SelectInput>
  );
};

export {ScopeSelector};
