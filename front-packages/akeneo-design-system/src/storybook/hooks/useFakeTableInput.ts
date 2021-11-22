import {useState} from 'react';

type Row = {
  nutritionScore: '1' | '2' | '3';
  part: string;
  quantity: number;
  is_allergenic: boolean | null;
  origin: 'french' | 'english' | 'german' | null;
};

type RowCode = 'nutritionScore' | 'part' | 'quantity' | 'is_allergenic' | 'origin';
type RowValue = '1' | '2' | '3' | string | number | boolean | null | 'french' | 'english' | 'german';

const useFakeTableInput = (linesCount: number) => {
  const [state, setState] = useState<Row[]>(
    Array.from(Array(linesCount).keys()).map(lineIndex => {
      return {
        nutritionScore: `${(lineIndex % 3) + 1}` as '1' | '2' | '3',
        part: `${lineIndex * 100}g`,
        quantity: lineIndex * 10,
        is_allergenic: [true, false, null][lineIndex % 3],
        origin: ['french', 'english', 'german', null][lineIndex % 4] as 'french' | 'english' | 'german' | null,
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

  const match: (text: string) => boolean = text => {
    return text.toLowerCase().includes(searchValue.toLowerCase());
  };

  return {getValue, setValue, searchValue, setSearchValue, match};
};

export {useFakeTableInput};
