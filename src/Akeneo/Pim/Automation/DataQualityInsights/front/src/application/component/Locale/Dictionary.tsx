import React, {FC} from 'react';
import {Helper} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {AddWordsForm} from './Dictionary/AddWordsForm';
import {useDictionaryState} from '../../../infrastructure';
import {WordsGrid} from './Dictionary/WordsGrid';

const Dictionary: FC = () => {
  const translate = useTranslate();
  const {dictionary} = useDictionaryState();

  return (
    <>
      <Helper>
        <span
          dangerouslySetInnerHTML={{
            __html: translate('akeneo_data_quality_insights.dictionary.helper', {link: 'https://help.akeneo.com'}),
          }}
        />
      </Helper>

      <AddWordsForm />

      {dictionary !== null ? <WordsGrid /> : <></>}
    </>
  );
};

export {Dictionary};
