import React from 'react';
import {TextWithLink} from './styled';

export type MarkersMapping = {
  [marker: string]: JSX.Element | undefined;
};

/**
 * Builds a list of JSX Elements based on a string containing markers.
 * When a marker is detected it is replaced by the specifier element in the list.
 * @param mapping the mapping between markers and elements
 * @returns a list of elements composing the message
 */
export const messageBuilder =
  (mapping: MarkersMapping) =>
  (source: string): JSX.Element =>
    (
      <TextWithLink>
        {source.split(' ').map((substr, index) => (
          <React.Fragment key={index}>{mapping[substr] || <span>{substr}</span>} </React.Fragment>
        ))}
      </TextWithLink>
    );
