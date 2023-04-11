import React from 'react';
import {SkeletonPlaceholder, Table, TextInput} from 'akeneo-design-system';
import {Styled} from '../../components/Styled';
import {ScopeAndLocaleSelector} from '../../components';
import {ChannelCode, getLabel, LocaleCode, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {useGetAttributeByCode} from '../../hooks';

type Props = {
  attributeCode?: string;
  locale?: LocaleCode | null;
  scope?: ChannelCode | null;
};

const ImplicitAttributeCondition: React.FC<Props> = ({attributeCode, scope, locale}) => {
  const translate = useTranslate();
  const {data, isLoading} = useGetAttributeByCode(attributeCode);
  const catalogLocale = useUserContext().get('catalogLocale');

  return isLoading ? (
    <Table.Row>
      <SkeletonPlaceholder>This is Loading</SkeletonPlaceholder>
    </Table.Row>
  ) : (
    <Table.Row aria-colspan={3} key={data?.code}>
      <Styled.TitleCell>{getLabel(data?.labels || {}, catalogLocale, data?.code || '')}</Styled.TitleCell>
      <Styled.SelectionInputsContainer>
        <Styled.OperatorContainer>
          <TextInput value={translate('pim_common.operators.NOT EMPTY')} readOnly={true} />
        </Styled.OperatorContainer>
        {data?.code && (
          <ScopeAndLocaleSelector attributeCode={data?.code} readOnly={true} scope={scope} locale={locale} />
        )}
      </Styled.SelectionInputsContainer>
      <Table.Cell />
    </Table.Row>
  );
};

export {ImplicitAttributeCondition};
