import {SkeletonPlaceholder, Table, TextInput} from 'akeneo-design-system';
import {Styled} from '../../components/Styled';
import {ScopeAndLocaleSelector} from '../../components';
import React from 'react';
import {SimpleSelectProperty} from '../../models';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {useGetAttributeByCode} from '../../hooks/useGetAttributeByCode';

type Props = {
  simpleSelectProperty: SimpleSelectProperty;
};

const ImplicitSimpleSelectCondition: React.FC<Props> = ({simpleSelectProperty}) => {
  const translate = useTranslate();
  const {data, isLoading} = useGetAttributeByCode(simpleSelectProperty.attributeCode);
  const locale = useUserContext().get('catalogLocale');

  return isLoading ? (
    <Table.Row>
      <SkeletonPlaceholder>This is Loading</SkeletonPlaceholder>
    </Table.Row>
  ) : (
    <Table.Row aria-colspan={3} key={data?.code}>
      <Styled.TitleCell>{getLabel(data?.labels || {}, locale, data?.code || '')}</Styled.TitleCell>
      <Styled.SelectionInputsContainer>
        <Styled.OperatorContainer>
          <TextInput value={translate('pim_common.operators.NOT EMPTY')} readOnly={true} />
        </Styled.OperatorContainer>
        {data?.code && (
          <ScopeAndLocaleSelector
            attributeCode={data?.code}
            readOnly={true}
            scope={simpleSelectProperty.scope}
            locale={simpleSelectProperty.locale}
          />
        )}
      </Styled.SelectionInputsContainer>
      <Table.Cell />
    </Table.Row>
  );
};

export {ImplicitSimpleSelectCondition};
