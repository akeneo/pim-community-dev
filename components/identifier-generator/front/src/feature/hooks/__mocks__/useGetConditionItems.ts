import {useState} from 'react';
import {Conditions} from '../../models';

type Response = {
  id: string;
  text: string;
  children: {
    id: string;
    text: string;
  }[];
};

const useGetConditionItems: (conditions: Conditions) => {
  conditionItems: Response[],
  handleNextPage: () => void,
  searchValue: string,
  setSearchValue: (searchValue: string) => void,
} = conditions => {
  const [searchValue, setSearchValue] = useState<string>('');
  if (searchValue === 'toto') {
    return {
      conditionItems: [],
      handleNextPage: () => {},
      searchValue,
      setSearchValue,
    }
  }

  if (searchValue === 'enabled') {
    return {
      conditionItems: [
        {
          id: 'system',
          text: 'System',
          children: [
            { id: 'enabled', text: 'Enabled' },
          ],
        },
      ],
      handleNextPage: () => {},
      searchValue,
      setSearchValue,
    }
  }

  const conditionIds = conditions.map(condition => condition.type as string);
  const filteredItems = [
    { id: 'family', text: 'Family' },
    { id: 'enabled', text: 'Enabled' },
  ].filter(item => !conditionIds.includes(item.id));


  return {
    conditionItems: [
      {
        id: 'system',
        text: 'System',
        children: filteredItems,
      }, {
        id: 'marketing',
        text: 'Marketing',
        children: [
          { id: 'color', text: 'Color' },
        ],
      }
    ],
    handleNextPage: () => {},
    searchValue,
    setSearchValue,
  }
};

export {useGetConditionItems};
