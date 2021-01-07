import React, {FC} from 'react';
import {Dictionary} from './Dictionary';

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

export {LocalesApp};
