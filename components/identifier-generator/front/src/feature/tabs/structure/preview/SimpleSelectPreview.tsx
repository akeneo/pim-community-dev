import React from 'react';
import {Property} from '../../../models';

type Props = {
  property: Property
}
const SimpleSelectPreview: React.FC<Props> = ({property}) => {
  console.log({property});
  return <div>Option Code</div>;
};

export {SimpleSelectPreview};
