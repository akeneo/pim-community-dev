import React, {useCallback, useMemo} from 'react';
import {Channel, ChannelCode, LocaleCode, useTranslate} from '@akeneo-pim-community/shared';
import {ScopeSelector} from './ScopeSelector';
import {useGetAttributeByCode} from '../hooks/useGetAttributeByCode';
import {Helper} from 'akeneo-design-system';
import {LocaleSelector} from './LocaleSelector';
import {useGetScopes} from '../hooks';
import {Styled} from './Styled';

type Props = {
  attributeCode: string;
  locale?: LocaleCode | null;
  scope?: ChannelCode | null;
  onChange: ({scope, locale}: {scope?: ChannelCode; locale?: LocaleCode}) => void;
  isHorizontal?: boolean;
};

const ScopeAndLocaleSelector: React.FC<Props> = ({
  attributeCode,
  locale = null,
  scope = null,
  onChange,
  isHorizontal = true,
}) => {
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
    return <Helper level={'error'}>{translate('pim_error.general')}</Helper>;
  }

  return isLoading ? (
    <Styled.ConditionLineSkeleton>This is Loading</Styled.ConditionLineSkeleton>
  ) : (
    <>
      {data?.scopable && <ScopeSelector value={scope} onChange={handleScopeChange} isHorizontal={isHorizontal}/>}
      {data?.localizable && (
        <LocaleSelector
          value={locale}
          onChange={handleLocaleChange}
          scopable={data?.scopable}
          scope={selectedScope}
          isHorizontal={isHorizontal}
        />
      )}
    </>
  );
};

export {ScopeAndLocaleSelector};
