/* istanbul ignore file */
import React from 'react';
import {useParams} from 'react-router-dom';

const Edit: React.FC<{}> = () => {
  const {identifierGeneratorCode} = useParams<{identifierGeneratorCode: string}>();

  return <div>
    Edit TODO
    {identifierGeneratorCode}
  </div>;
};

export {Edit};
