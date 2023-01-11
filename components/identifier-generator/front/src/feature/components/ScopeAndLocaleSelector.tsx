import React, {useCallback, useMemo} from 'react';
import {Channel, ChannelCode, LocaleCode, useTranslate} from '@akeneo-pim-community/shared';
import {ScopeSelector} from './ScopeSelector';
import {useGetAttributeByCode} from '../hooks/useGetAttributeByCode';
import {Helper, Table} from 'akeneo-design-system';
import {LocaleSelector} from './LocaleSelector';
import {useGetScopes} from '../hooks';
import {Styled} from './Styled';

type Props = {
  attributeCode: string;
  locale?: LocaleCode | null;
  scope?: ChannelCode | null;
  onChange: ({scope, locale}: {scope?: string | null; locale?: string | null}) => void;
};

const ScopeAndLocaleSelector: React.FC<Props> = ({attributeCode, locale = null, scope = null, onChange}) => {
  const translate = useTranslate();
  const {data, isLoading, error} = useGetAttributeByCode(attributeCode);
  const handleScopeChange = useCallback(
    (scope: ChannelCode | null) => {
      onChange({scope});
    },
    [onChange]
  );
  const {data: scopes} = useGetScopes();

  const selectedScope = useMemo(
    () => (data?.scopable ? scopes?.find(({code}: Channel) => code === scope) : undefined),
    [data, scope, scopes]
  );

  const handleLocaleChange = useCallback(
    (locale: LocaleCode | null) => {
      onChange({locale});
    },
    [onChange]
  );

  if (error) {
    return <Helper level={'error'}>{translate('pim_error.general')}</Helper>;
  }

  return isLoading ? (
    <Styled.ConditionLineSkeleton>This is Loading</Styled.ConditionLineSkeleton>
  ) : (
    <>
      {data?.scopable && (
        <Table.Cell colSpan={data?.localizable ? 1 : 2}>
          <ScopeSelector value={scope} onChange={handleScopeChange} />
        </Table.Cell>
      )}
      {data?.localizable && (
        <Table.Cell colSpan={data?.scopable ? 1 : 2}>
          <LocaleSelector
            value={locale}
            onChange={handleLocaleChange}
            scopable={data?.scopable}
            scope={selectedScope}
          />
        </Table.Cell>
      )}
      {!data?.localizable && !data?.scopable && <Table.Cell colSpan={2} />}
    </>
  );
};

export {ScopeAndLocaleSelector};
