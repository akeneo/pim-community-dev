import React, {useCallback} from 'react';
import {Nomenclature, NomenclatureLineEditProps} from '../models';
import {Table, TextInput} from 'akeneo-design-system';
import {Styled} from './Styled';
import {useIsNomenclatureValueValid, usePlaceholder} from '../hooks';

type Props = {
  nomenclature: Nomenclature;
  nomenclatureLine: NomenclatureLineEditProps;
  onChange: (code: string, value: string) => void;
};

const NomenclatureLineEdit: React.FC<Props> = ({nomenclature, nomenclatureLine: {code, label, value}, onChange}) => {
  const isValid = useIsNomenclatureValueValid(nomenclature);
  const getPlaceholder = usePlaceholder(nomenclature);

  const handleChangeValue = useCallback(
    (value: string) => {
      onChange(code, value);
    },
    [code, onChange]
  );

  return (
    <Table.Row key={code}>
      <Styled.TitleCell>{label}</Styled.TitleCell>
      <Table.Cell>{code}</Table.Cell>
      <Table.Cell>
        <TextInput
          value={value}
          invalid={!isValid(value || getPlaceholder(code))}
          readOnly={false}
          onChange={handleChangeValue}
          placeholder={getPlaceholder(code)}
        />
      </Table.Cell>
    </Table.Row>
  );
};

export {NomenclatureLineEdit};
