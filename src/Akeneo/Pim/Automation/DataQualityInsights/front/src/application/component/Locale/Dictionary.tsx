import React, {FC} from 'react';
import {Helper} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {AddWordsForm} from './Dictionary/AddWordsForm';
import {useLocaleDictionary} from '../../../infrastructure';
import {WordsGrid} from './Dictionary/WordsGrid';

type DictionaryProps = {
  localeCode: string;
};

const Dictionary: FC<DictionaryProps> = ({localeCode}) => {
  const translate = useTranslate();
  const words = useLocaleDictionary(localeCode);

  return <>
    <Helper>
      <span dangerouslySetInnerHTML={{
        __html: translate('akeneo_data_quality_insights.dictionary.helper', {link: 'https://help.akeneo.com'}),
      }}/>
    </Helper>

    <AddWordsForm/>

    {words !== null ? <WordsGrid words={words}/> : <></>}
  </>;
};

export {Dictionary};
