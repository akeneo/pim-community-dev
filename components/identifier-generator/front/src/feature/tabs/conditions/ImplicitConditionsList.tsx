import React from 'react';
import {Styled} from '../../components/Styled';
import {Table, TextInput} from 'akeneo-design-system';
import {IdentifierGenerator, PROPERTY_NAMES, RefEntityProperty, SimpleSelectProperty} from '../../models';
import {useIdentifierAttributes} from '../../hooks';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ListSkeleton} from '../../components';
import {ImplicitAttributeCondition} from './ImplicitAttributeCondition';

type Props = {
  generator: IdentifierGenerator;
};

const ImplicitConditionsList: React.FC<Props> = ({generator}) => {
  const translate = useTranslate();
  const {target} = generator;
  const {data: identifiers, isLoading} = useIdentifierAttributes();

  const hasFamilyStructureProperty = generator.structure.filter(({type}) => type === PROPERTY_NAMES.FAMILY)?.length > 0;
  const attributeProperties = generator.structure.filter(
    ({type}) => type === PROPERTY_NAMES.SIMPLE_SELECT || type === PROPERTY_NAMES.REF_ENTITY
  ) as (SimpleSelectProperty | RefEntityProperty)[];

  return (
    <>
      {isLoading ? (
        <ListSkeleton />
      ) : (
        <Table.Row aria-colspan={3}>
          <Styled.TitleCell>
            {identifiers && identifiers.length > 0 ? identifiers[0].label : `[${target}]`}
          </Styled.TitleCell>
          <Styled.SelectionInputsContainer>
            <Styled.OperatorContainer>
              <TextInput value={translate('pim_common.operators.EMPTY')} readOnly={true} />
            </Styled.OperatorContainer>
          </Styled.SelectionInputsContainer>
          <Table.Cell />
        </Table.Row>
      )}
      {hasFamilyStructureProperty && (
        <Table.Row aria-colspan={3}>
          <Styled.TitleCell>{translate('pim_identifier_generator.structure.property_type.family')}</Styled.TitleCell>
          <Table.Cell>
            <Styled.OperatorContainer>
              <TextInput value={translate('pim_common.operators.NOT EMPTY')} readOnly={true} />
            </Styled.OperatorContainer>
          </Table.Cell>
          <Table.Cell />
        </Table.Row>
      )}
      {attributeProperties?.map(({attributeCode, scope, locale}) => (
        <ImplicitAttributeCondition attributeCode={attributeCode} scope={scope} locale={locale} key={attributeCode} />
      ))}
    </>
  );
};

export {ImplicitConditionsList};
