import React, {FC} from 'react';
import styled from "styled-components";
import {Helper, Link} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {AddWordsForm} from './Dictionary/AddWordsForm';
import {useDictionaryState} from '../../../infrastructure';
import {WordsGrid} from './Dictionary/WordsGrid';

const PimHelperLink = styled(Link)`
  &:hover {
    text-decoration: underline;
  }
`;

const Dictionary: FC = () => {
  const translate = useTranslate();
  const {dictionary} = useDictionaryState();

  return (
    <>
      <Helper level="info">
        {translate('akeneo_data_quality_insights.dictionary.helper.content')}
        <> </>
        <PimHelperLink href="https://help.akeneo.com/pim/serenity/articles/manage-your-data-quality.html#manage-your-dictionary" target="_blank">
          {translate('akeneo_data_quality_insights.dictionary.helper.link_label')}
        </PimHelperLink>
      </Helper>

      <AddWordsForm />

      {dictionary !== null ? <WordsGrid /> : <></>}
    </>
  );
};

export {Dictionary};
