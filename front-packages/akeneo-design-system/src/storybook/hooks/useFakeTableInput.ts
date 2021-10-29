import {useState} from 'react';

type Row = {
  nutritionScore: '1' | '2' | '3';
  part: string;
  quantity: number;
  is_allergenic: boolean | null;
};

type RowCode = 'nutritionScore' | 'part' | 'quantity' | 'is_allergenic';
type RowValue = '1' | '2' | '3' | string | number | boolean | null;

const useFakeTableInput = (linesCount: number) => {
  const [state, setState] = useState<Row[]>(
    Array.from(Array(linesCount).keys()).map(lineIndex => {
      return {
        nutritionScore: `${(lineIndex % 3) + 1}` as '1' | '2' | '3',
        part: `${lineIndex * 100}g`,
        quantity: lineIndex * 10,
        is_allergenic: [true, false, null][lineIndex % 3],
      };
    })
  );

  const [searchValue, setSearchValue] = useState<string>('');

  const getValue: (lineIndex: number, columName: RowCode) => RowValue = (lineIndex, columnName) => {
    return state[lineIndex][columnName];
  };

  const setValue: (lineIndex: number, columName: RowCode, value: RowValue) => void = (lineIndex, columnName, value) => {
    (state[lineIndex][columnName] as RowValue) = value;
    setState([...state]);
  };

  return {getValue, setValue, searchValue, setSearchValue};
};

export {useFakeTableInput};
