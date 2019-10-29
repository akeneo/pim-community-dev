/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';
import {ChangeEvent, useEffect, useState} from 'react';

interface Props {
  onSearch: (newValue: string) => void;
  initialSearchTerms?: string;
  placeholder: string;
}

export const SearchFilter = ({onSearch, placeholder, initialSearchTerms}: Props) => {
  const [searchTerms, setSearchTerms] = useState(initialSearchTerms);

  useEffect(() => {
    if (undefined === searchTerms) {
      return;
    }
    if ('' === searchTerms) {
      onSearch(searchTerms);

      return;
    }
    const timer = setTimeout(() => onSearch(searchTerms), 250);

    return () => {
      clearTimeout(timer);
    };
  }, [searchTerms]);

  return (
    <div className='AknFilterBox-searchContainer'>
      <input
        className='AknFilterBox-search'
        maxLength={255}
        autoComplete='non'
        type='text'
        name='searchTerms'
        defaultValue={searchTerms}
        onChange={(event: ChangeEvent<HTMLInputElement>) => setSearchTerms(event.target.value)}
        placeholder={placeholder}
      />
    </div>
  );
};
