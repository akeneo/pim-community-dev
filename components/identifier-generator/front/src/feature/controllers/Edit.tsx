/* istanbul ignore file */
import React from 'react';
import {useParams} from 'react-router-dom';
import {EditGeneratorPage} from '../pages/EditGeneratorPage';
import {PROPERTY_NAMES} from '../models';

const Edit: React.FC<{}> = () => {
  const {identifierGeneratorCode} = useParams<{identifierGeneratorCode: string}>();

  // Temporary
  const generator = {
    code: identifierGeneratorCode,
    target: 'sku',
    labels: {en_US: 'a label'},
    conditions: [],
    structure: [{type: PROPERTY_NAMES.FREE_TEXT, string: 'AKN'}],
    delimiter: null
  };

  return (
    <EditGeneratorPage initialGenerator={generator}/>
  );
};

export {Edit};
