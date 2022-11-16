import React from 'react';
import {Delimiter, Structure} from '../../models';
import {Preview as PreviewComponent} from 'akeneo-design-system';
import {PropertyPreview} from './preview/PropertyPreview';
import {DelimiterPreview} from './preview/DelimiterPreview';

type PreviewProps = {
  structure: Structure;
  delimiter: Delimiter | null;
};

const Preview: React.FC<PreviewProps> = ({structure, delimiter}) => {
  return (
    <PreviewComponent title={'Preview'}>
      {structure.map((item, i) => (
        <React.Fragment key={JSON.stringify(item)}>
          {i > 0 && delimiter && <DelimiterPreview delimiter={delimiter} />}
          <PropertyPreview property={item} />
        </React.Fragment>
      ))}
    </PreviewComponent>
  );
};

export {Preview};
