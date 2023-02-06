import React, {useCallback, useMemo} from 'react';
import {Nomenclature, NomenclatureLineEditProps} from '../models';
import {Table, TextInput} from 'akeneo-design-system';
import {Styled} from './Styled';
import {useIsNomenclatureValueValid} from '../hooks';

type Props = {
  nomenclature: Nomenclature;
  nomenclatureLine: NomenclatureLineEditProps;
  onChange: (code: string, value: string) => void;
};

const NomenclatureLineEdit: React.FC<Props> = ({
  nomenclature,
  nomenclatureLine: {code, label, value},
  onChange,
}) => {
  const isValid = useIsNomenclatureValueValid(nomenclature);
  const placeholder = useMemo(() => {
    if (nomenclature && nomenclature.generate_if_empty) {
      return code.substr(0, nomenclature.value || 0);
    }
    return '';
  }, [code, nomenclature]);

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
          invalid={!isValid(value)}
          readOnly={false}
          onChange={handleChangeValue}
          placeholder={placeholder}
        />
      </Table.Cell>
    </Table.Row>
  );
};

export {NomenclatureLineEdit};
