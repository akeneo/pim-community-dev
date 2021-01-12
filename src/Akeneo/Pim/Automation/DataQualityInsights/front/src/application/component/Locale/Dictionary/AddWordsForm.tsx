import React from 'react';
import {Button, TextInput} from 'akeneo-design-system';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const AddWordsForm = () => {
  const translate = useTranslate();

  return (
    <Container>
      {translate('akeneo_data_quality_insights.dictionary.add_words')}

      <InputContainer>
        <TextInputContainer>
          <TextInput value={''} onChange={() => console.log('on change')} />
        </TextInputContainer>

        <Button ghost level="tertiary" onClick={() => console.log('add words')}>
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
