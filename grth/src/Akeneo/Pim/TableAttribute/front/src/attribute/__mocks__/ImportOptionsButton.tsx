import React from 'react';
import {AttributeOption} from '../../models';

type ImportOptionsButtonProps = {
  onClick: (attributeOptions: AttributeOption[]) => void;
};

const ImportOptionsButton: React.FC<ImportOptionsButtonProps> = ({onClick}) => {
  const handleClick = () => {
    onClick([
      {
        code: 'fakeOption1',
        labels: {
          en_US: 'Fake Option 1 Label English',
        },
      },
      {
        code: 'fakeOption2',
        labels: {
          fr_FR: 'Fake Option 2 Label French',
        },
      },
    ]);
  };

  return <button onClick={handleClick}>Fake import button</button>;
};

export {ImportOptionsButton};
