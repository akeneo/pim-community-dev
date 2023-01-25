import React from 'react';
import {Condition, IdentifierCondition} from '../../models';
import {Table, TextInput} from 'akeneo-design-system';
import {Styled} from '../../components/Styled';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useIdentifierAttributes} from '../../hooks';

type EnabledLineProps = {
  condition: IdentifierCondition;
  onChange: (condition: Condition) => void;
  onDelete: () => void;
};

const IdentifierLine: React.FC<EnabledLineProps> = ({condition}) => {
  const translate = useTranslate();
  const {data: identifiers} = useIdentifierAttributes();

  return (
    <>
      <Styled.TitleCondition>
        {identifiers && identifiers.length > 0 ? identifiers[0].label : `[${condition.attributeCode}]`}
      </Styled.TitleCondition>
      <Table.Cell colSpan={3}>
        <Styled.InputContainer>
          <TextInput value={translate('pim_common.operators.EMPTY')} readOnly={true} />
        </Styled.InputContainer>
      </Table.Cell>
    </>
  );
};

export {IdentifierLine};
