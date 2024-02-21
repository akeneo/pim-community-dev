import React from 'react';
import {Helper, SelectInput, SkeletonPlaceholder} from 'akeneo-design-system';
import {ChannelCode, LabelCollection, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {useGetScopes} from '../hooks';
import {Styled} from './Styled';
import {useIdentifierGeneratorAclContext} from '../context';

type ScopeSelectorProps = {
  value: ChannelCode | null;
  onChange: (code: ChannelCode) => void;
  isHorizontal?: boolean;
  readOnly: boolean;
};

const ScopeSelector: React.FC<ScopeSelectorProps> = ({value, onChange, isHorizontal = true, readOnly}) => {
  const translate = useTranslate();
  const currentCatalogLocale = useUserContext().get('catalogLocale');
  const {data: options, isLoading, error} = useGetScopes();
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();

  const getLabel = (labels: LabelCollection, code: string) => labels[currentCatalogLocale] || `[${code}]`;

  if (error) {
    return <Helper level={'error'}>{translate('pim_error.general')}</Helper>;
  }

  return isLoading ? (
    <SkeletonPlaceholder>This is a loading channel</SkeletonPlaceholder>
  ) : (
    <Styled.SelectCondition
      value={value}
      emptyResultLabel={translate('pim_common.no_result')}
      openLabel={translate('pim_common.channel')}
      onChange={onChange}
      placeholder={translate('pim_common.channel')}
      clearable={false}
      readOnly={!identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted || readOnly}
      isHorizontal={isHorizontal}
    >
      {options?.map(({code, labels}) => (
        <SelectInput.Option value={code} key={code}>
          {getLabel(labels, code)}
        </SelectInput.Option>
      ))}
    </Styled.SelectCondition>
  );
};

export {ScopeSelector};
