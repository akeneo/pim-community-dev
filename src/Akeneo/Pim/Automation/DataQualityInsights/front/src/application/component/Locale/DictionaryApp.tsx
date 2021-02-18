import React, {FC} from 'react';
import {Dictionary} from './Dictionary';
import {DictionaryProvider} from './DictionaryProvider';

type DictionaryAppProps = {
  localeCode: string;
};

const DictionaryApp: FC<DictionaryAppProps> = ({localeCode}) => {
  return (
    <DictionaryProvider localeCode={localeCode}>
      <Dictionary />
    </DictionaryProvider>
  );
};

export {DictionaryApp};
