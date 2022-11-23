import React from 'react';
import {Styled} from './Styled';

const LabelTranslationsSkeleton: React.FC = () => (
  <>
    {[...Array(3)].map((_, i) => (
      <div key={i}>
        <Styled.TranslationsLabelSkeleton>This is a loading label</Styled.TranslationsLabelSkeleton>
        <Styled.TranslationsTextFieldSkeleton>This is a loading text field</Styled.TranslationsTextFieldSkeleton>
      </div>
    ))}
  </>
);

export {LabelTranslationsSkeleton};
