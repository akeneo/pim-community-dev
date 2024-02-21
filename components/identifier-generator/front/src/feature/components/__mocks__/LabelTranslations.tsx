import React from 'react';
import {LabelCollection} from '../../models';

type LabelTranslationsMockProps = {
  onLabelsChange: (labelCollection: LabelCollection) => void;
};

const LabelTranslations: React.FC<LabelTranslationsMockProps> = ({onLabelsChange}) => {
  const updateFrenchLabel = () => {
    onLabelsChange({fr_FR: 'FrenchUpdated'});
  };

  return (
    <>
      LabelTranslationsMock
      <button onClick={updateFrenchLabel}>Update French Label</button>
    </>
  );
};

export {LabelTranslations};
