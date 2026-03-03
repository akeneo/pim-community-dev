import {useState} from 'react';
import {ATTRIBUTE_TYPE, Conditions, ItemsGroup} from '../../models';

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
    {id: 'categories', text: 'Categories'},
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
        children: [
          {id: 'color', text: 'Color', type: ATTRIBUTE_TYPE.SIMPLE_SELECT},
          {id: 'main_color', text: 'Main color', type: ATTRIBUTE_TYPE.MULTI_SELECT},
        ],
      },
    ],
    // eslint-disable-next-line @typescript-eslint/no-empty-function
    handleNextPage: () => {},
    searchValue,
    setSearchValue,
  };
};

export {useGetConditionItems};
