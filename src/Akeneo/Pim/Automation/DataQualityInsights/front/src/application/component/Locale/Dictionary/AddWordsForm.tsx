import React, {useState} from 'react';
import {Button, Field} from 'akeneo-design-system';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {TagInput} from '../TagInput';
import {useDictionaryState} from '../../../../infrastructure';

const AddWordsForm = () => {
  const translate = useTranslate();
  const [words, setWords] = useState<string[]>([]);
  const {addWords, search} = useDictionaryState();

  const onAddWords = async () => {
    if (words.length > 0) {
      await addWords(words);
      setWords([]);
      search('', 1);
    }
  }

  return (
    <Container className={'filter-box'}>
      <Field label={translate('akeneo_data_quality_insights.dictionary.add_words')}>
        <FieldContent>
          <TagInputContainer>
            <TagInput allowDuplicates={false} tags={words} setTags={setWords}/>
          </TagInputContainer>
          <Button ghost level="tertiary" onClick={onAddWords}>
            {translate('pim_common.add')}
          </Button>
        </FieldContent>
      </Field>
    </Container>
  );
};

const FieldContent = styled.div`
  display: flex;
  align-items: center;
`;

const Container = styled.div`
  width: 550px;
  margin: 20px auto 0 auto;

  > * {
    max-width: inherit;
  }
`;

const TagInputContainer = styled.div`
  flex: 1;
  margin-right: 10px;
`;

export {AddWordsForm};
