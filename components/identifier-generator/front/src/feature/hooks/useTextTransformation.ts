import {useMemo} from 'react';
import {TEXT_TRANSFORMATION, TextTransformation} from '../models';

const useTextTransformation: (value: string | null, textTransformation: TextTransformation) => string | null = (
  value,
  textTransformation
) => {
  return useMemo(() => {
    switch (textTransformation) {
      case TEXT_TRANSFORMATION.UPPERCASE:
        return value ? value.toUpperCase() : null;
      case TEXT_TRANSFORMATION.LOWERCASE:
        return value ? value.toLowerCase() : null;
      default:
        return value;
    }
  }, [value, textTransformation]);
};

export {useTextTransformation};
