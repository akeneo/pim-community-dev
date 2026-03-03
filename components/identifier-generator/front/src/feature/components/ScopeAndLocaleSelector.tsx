import React, {useCallback, useMemo} from 'react';
import {Channel, ChannelCode, LocaleCode, useTranslate} from '@akeneo-pim-community/shared';
import {ScopeSelector} from './ScopeSelector';
import {useGetAttributeByCode, useGetScopes} from '../hooks';
import {Field, Helper} from 'akeneo-design-system';
import {LocaleSelector} from './LocaleSelector';
import {Styled} from './Styled';
import {AttributeCode} from '../models';

type Props = {
  attributeCode: AttributeCode;
  locale?: LocaleCode | null;
  scope?: ChannelCode | null;
  onChange?: ({scope, locale}: {scope?: ChannelCode; locale?: LocaleCode}) => void;
  isHorizontal?: boolean;
  readOnly?: boolean;
};

const ScopeAndLocaleSelector: React.FC<Props> = ({
  attributeCode,
  locale = null,
  scope = null,
  onChange,
  isHorizontal = true,
  readOnly = false,
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
      onChange?.({scope});
    },
    [onChange]
  );

  const handleLocaleChange = useCallback(
    (locale: LocaleCode) => {
      onChange?.({locale});
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
      {data?.scopable && isHorizontal && (
        <ScopeSelector value={scope} onChange={handleScopeChange} isHorizontal={isHorizontal} readOnly={readOnly} />
      )}
      {data?.scopable && !isHorizontal && (
        <Field label={translate('pim_common.channel')} requiredLabel={translate('pim_common.required_label')}>
          <ScopeSelector value={scope} onChange={handleScopeChange} isHorizontal={isHorizontal} readOnly={readOnly} />
        </Field>
      )}
      {data?.localizable && isHorizontal && (
        <LocaleSelector
          value={locale}
          onChange={handleLocaleChange}
          scopable={data?.scopable}
          scope={selectedScope}
          isHorizontal={isHorizontal}
          readOnly={readOnly}
        />
      )}
      {data?.localizable && !isHorizontal && (
        <Field label={translate('pim_common.locale')} requiredLabel={translate('pim_common.required_label')}>
          <LocaleSelector
            value={locale}
            onChange={handleLocaleChange}
            scopable={data?.scopable}
            scope={selectedScope}
            isHorizontal={isHorizontal}
            readOnly={readOnly}
          />
        </Field>
      )}
    </>
  );
};

export {ScopeAndLocaleSelector};
