import React from 'react';
import {FamilyCodeProperty} from '../../../models';

type Props = {
  property: FamilyCodeProperty
}

const FamilyCodePreview: React.FC<Props> = ({property}) => {
  console.log({property});
  return <div>FamilyCodePreview</div>;
};

export {FamilyCodePreview};
