enum TEXT_TRANSFORMATION {
  NO = 'no',
  LOWERCASE = 'lowercase',
  UPPERCASE = 'uppercase',
}

type TextTransformation = TEXT_TRANSFORMATION.NO | TEXT_TRANSFORMATION.LOWERCASE | TEXT_TRANSFORMATION.UPPERCASE;

export {TEXT_TRANSFORMATION};

export type {TextTransformation};
