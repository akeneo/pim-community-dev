import React, {useMemo} from 'react';
import {Channel, LocaleCode, useTranslate} from '@akeneo-pim-community/shared';
import {useGetScopes} from '../hooks';
import {Helper, Locale, SelectInput, SkeletonPlaceholder} from 'akeneo-design-system';
import {Styled} from './Styled';
import {useIdentifierGeneratorAclContext} from '../context';

type Props = {
  value: LocaleCode | null;
  onChange: (code: LocaleCode) => void;
  scopable: boolean;
  scope?: Channel;
  isHorizontal?: boolean;
  readOnly: boolean;
};

const LocaleSelector: React.FC<Props> = ({value, onChange, scopable, scope, isHorizontal = true, readOnly}) => {
  const translate = useTranslate();
  const {data, isLoading, error} = useGetScopes();
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();

  const catalogLocales = useMemo(() => {
    if (!data || isLoading || error) return [];
    return data
      ?.map(({locales}) => locales)
      ?.reduce((previousValue, currentValue) => {
        const nonAddedLocales = currentValue.filter(locale => !previousValue.find(({code}) => code === locale.code));
        return [...previousValue, ...nonAddedLocales];
      });
  }, [data, error, isLoading]);

  const locales = useMemo(() => {
    if (scopable && !scope) {
      return [];
    } else if (scopable && scope) {
      return scope.locales;
    }
    return catalogLocales || [];
  }, [catalogLocales, scopable, scope]);

  if (error) {
    return <Helper level={'error'}>{translate('pim_error.general')}</Helper>;
  }

  return isLoading ? (
    <SkeletonPlaceholder>This is a loading channel</SkeletonPlaceholder>
  ) : (
    <Styled.SelectCondition
      value={value}
      emptyResultLabel={translate('pim_common.no_result')}
      openLabel={translate('pim_common.locale')}
      onChange={onChange}
      placeholder={translate('pim_common.locale')}
      clearable={false}
      readOnly={!identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted || readOnly}
      isHorizontal={isHorizontal}
    >
      {locales?.map(locale => (
        <SelectInput.Option value={locale.code} key={locale.code}>
          <Locale code={locale.code} languageLabel={locale.label} />
        </SelectInput.Option>
      ))}
    </Styled.SelectCondition>
  );
};

export {LocaleSelector};
