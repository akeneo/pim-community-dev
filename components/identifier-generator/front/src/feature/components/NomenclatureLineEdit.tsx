import React, {useCallback} from 'react';
import {Nomenclature, NomenclatureLineEditProps} from '../models';
import {Table} from 'akeneo-design-system';
import {Styled} from './Styled';
import {useNomenclatureDisplay} from '../hooks';

type Props = {
  nomenclature: Nomenclature;
  nomenclatureLine: NomenclatureLineEditProps;
  onChange: (code: string, value: string) => void;
};

const NomenclatureLineEdit: React.FC<Props> = ({nomenclature, nomenclatureLine: {code, label, value}, onChange}) => {
  const {isValid, getPlaceholder} = useNomenclatureDisplay(nomenclature);

  const handleChangeValue = useCallback(
    (newValue: string) => {
      onChange(code, nomenclature.value ? newValue.substr(0, nomenclature.value) : newValue);
    },
    [code, onChange, nomenclature.value]
  );

  return (
    <Table.Row key={code}>
      <Styled.TitleCell>{label}</Styled.TitleCell>
      <Table.Cell>{code}</Table.Cell>
      <Table.Cell>
        <Styled.NomenclatureInput
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
