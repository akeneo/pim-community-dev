import {useState} from 'react';

const useFakeTableInput = (linesCount) => {
  const [state, setState] = useState([...Array(linesCount).keys()].map((lineIndex) => {
    return {nutritionScore: `${(lineIndex % 3)+1}`, part: `${lineIndex*100}g`, quantity: lineIndex * 10, is_allergenic: null};
  }));

  const [searchValue, setSearchValue] = useState('');

  const getValue = (lineIndex, columnName) => {
    return state[lineIndex][columnName];
  }

  const setValue = (lineIndex, columnName, value) => {
    state[lineIndex][columnName] = value
    setState([...state]);
  }

  return {getValue, setValue, setValue, setSearchValue};
};

export {useFakeTableInput};
