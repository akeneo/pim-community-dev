import React from 'react';
import {FamilyCodeProperty} from '../../../models';

type Props = {
  property: FamilyCodeProperty;
};

const FamilyCodeLine: React.FC<Props> = ({property}) => {
  // eslint-disable-next-line no-console
  console.log({property});

  return <div>coucou</div>;
};

export {FamilyCodeLine};
