import {useEffect} from 'react';

const useSetPageTitle = (title: string): void => {
  useEffect(() => {
    document.title = title;
  }, [title]);
};

export {useSetPageTitle};
