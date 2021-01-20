import React, {useState} from 'react';
import {Button} from 'akeneo-design-system';
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
    <Container>
      {translate('akeneo_data_quality_insights.dictionary.add_words')}

      <InputContainer>
        <TextInputContainer>
          <TagInput allowDuplicates={false} defaultTags={words} onTagsUpdate={(words: string[]) => setWords(words)}/>
        </TextInputContainer>

        <Button ghost level="tertiary" onClick={onAddWords}>
          {translate('pim_common.add')}
        </Button>
      </InputContainer>
    </Container>
  );
};

const Container = styled.div`
  width: 550px;
  margin: 20px auto 0 auto;
`;

const InputContainer = styled.div`
  align-items: center;
  display: flex;
  margin-top: 5px;
`;

const TextInputContainer = styled.div`
  flex: 1;
  margin-right: 10px;
`;

export {AddWordsForm};
