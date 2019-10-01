/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import * as React from 'react';
import {useContext} from 'react';

import {TranslateContext} from '../../context/translate-context';

interface Props {
  id: string;
  placeholders?: any;
  count?: number;
}

export const Translate = ({id, placeholders = {}, count = 1}: Props) => {
  const translate = useContext(TranslateContext);

  return <>{translate(id, placeholders, count)}</>;
};
