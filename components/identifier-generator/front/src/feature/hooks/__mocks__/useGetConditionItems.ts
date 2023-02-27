import {useState} from 'react';
import {Conditions} from '../../models';

type ItemsGroup = {
  id: string;
  text: string;
  children: {
    id: string;
    text: string;
  }[];
};

const useGetConditionItems: (
  isOpen: boolean,
  conditions: Conditions
) => {
  conditionItems: ItemsGroup[];
  handleNextPage: () => void;
  searchValue: string;
  setSearchValue: (searchValue: string) => void;
} = (isOpen, conditions) => {
  const [searchValue, setSearchValue] = useState<string>('');
  if (searchValue === 'toto') {
    return {
      conditionItems: [],
      // eslint-disable-next-line @typescript-eslint/no-empty-function
      handleNextPage: () => {},
      searchValue,
      setSearchValue,
    };
  }

  if (searchValue === 'enabled') {
    return {
      conditionItems: [
        {
          id: 'system',
          text: 'System',
          children: [{id: 'enabled', text: 'Enabled'}],
        },
      ],
      // eslint-disable-next-line @typescript-eslint/no-empty-function
      handleNextPage: () => {},
      searchValue,
      setSearchValue,
    };
  }

  const conditionIds = conditions.map(condition => condition.type as string);
  const filteredItems = [
    {id: 'family', text: 'Family'},
    {id: 'enabled', text: 'Enabled'},
  ].filter(item => !conditionIds.includes(item.id));

  return {
    conditionItems: [
      {
        id: 'system',
        text: 'System',
        children: filteredItems,
      },
      {
        id: 'marketing',
        text: 'Marketing',
        children: [{id: 'color', text: 'Color'}],
      },
    ],
    // eslint-disable-next-line @typescript-eslint/no-empty-function
    handleNextPage: () => {},
    searchValue,
    setSearchValue,
  };
};

export {useGetConditionItems};
