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
  onChange: ({scope, locale}: {scope?: ChannelCode; locale?: LocaleCode}) => void;
};

const ScopeAndLocaleSelector: React.FC<Props> = ({attributeCode, locale = null, scope = null, onChange}) => {
  const translate = useTranslate();
  const {data, isLoading, error} = useGetAttributeByCode(attributeCode);
  const {data: scopes} = useGetScopes();

  const selectedScope = useMemo(
    () => (data?.scopable ? scopes?.find(({code}: Channel) => code === scope) : undefined),
    [data, scope, scopes]
  );

  const handleScopeChange = useCallback(
    (scope: ChannelCode) => {
      onChange({scope});
    },
    [onChange]
  );

  const handleLocaleChange = useCallback(
    (locale: LocaleCode) => {
      onChange({locale});
    },
    [onChange]
  );

  if (error) {
    return (
      <Table.Cell colSpan={2}>
        <Helper level={'error'}>{translate('pim_error.general')}</Helper>
      </Table.Cell>
    );
  }

  return isLoading ? (
    <Table.Cell colSpan={2}>
      <Styled.ConditionLineSkeleton>This is Loading</Styled.ConditionLineSkeleton>
    </Table.Cell>
  ) : (
    <>
      {data?.scopable && (
        <Table.Cell>
          <ScopeSelector value={scope} onChange={handleScopeChange} />
        </Table.Cell>
      )}
      {data?.localizable && (
        <Table.Cell>
          <LocaleSelector
            value={locale}
            onChange={handleLocaleChange}
            scopable={data?.scopable}
            scope={selectedScope}
          />
        </Table.Cell>
      )}
      {((data?.localizable && !data?.scopable) || (data?.scopable && !data?.localizable)) && <Table.Cell />}
      {!data?.localizable && !data?.scopable && <Table.Cell colSpan={2} />}
    </>
  );
};

export {ScopeAndLocaleSelector};
