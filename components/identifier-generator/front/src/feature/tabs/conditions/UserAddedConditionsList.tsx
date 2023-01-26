import React from 'react';
import {Styled} from '../../components/Styled';
import {Table, TextInput} from 'akeneo-design-system';
import {IdentifierGenerator, PROPERTY_NAMES} from '../../models';
import {useIdentifierAttributes} from '../../hooks';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ListSkeleton} from '../../components';

type Props = {
  generator: IdentifierGenerator
}

const UserAddedConditionsList: React.FC<Props> = ({generator}) => {
  const translate = useTranslate();
  const {target} = generator;
  const {data: identifiers, isLoading} = useIdentifierAttributes();

  const hasFamilyStructureProperty = generator.structure.filter(
    ({type}) => type === PROPERTY_NAMES.FAMILY)?.length > 0;

  return (<>
  {isLoading ? <ListSkeleton /> : <Table.Row>
    <Styled.NotDraggableCell/>
    <Styled.TitleCondition>
      {identifiers && identifiers.length > 0 ? identifiers[0].label : `[${target}]`}
    </Styled.TitleCondition>
    <Table.Cell colSpan={3}>
      <Styled.InputContainer>
        <TextInput value={translate('pim_common.operators.EMPTY')} readOnly={true}/>
      </Styled.InputContainer>
    </Table.Cell>
  </Table.Row>
  }
    <Table.Row>
      <Styled.TitleCondition>
        {translate('Family')}
      </Styled.TitleCondition>
      coucou faut mettre la famille
    </Table.Row>
  </>);
};

export {UserAddedConditionsList};
