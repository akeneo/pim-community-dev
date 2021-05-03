import React from 'react';
import {getColor, Link} from 'akeneo-design-system';
import styled from 'styled-components';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '../../tests/utils';
import {TranslationWithComponent} from './TranslationWithComponent';

const Highlight = styled.span`
  color: ${getColor('brand', 100)};
  font-weight: bold;
`;

jest.mock('../hooks/useTranslate', () => ({
  useTranslate: () => {
    return jest.fn((key: string, params: any) => {
      switch (key) {
        case 'text.with.link':
          return 'To know more about akeneo, <link>this article may help you</link>';
        case 'text.with.highlight':
          return `Caution <highlight>${params.productNumber}</highlight> currently have values set for this attribute, they will be removed.`;
        case 'text.start_with.highlight':
          return `<highlight>${params.productNumber}</highlight> currently have values set for this attribute, they will be removed.`;
        default:
          return key;
      }
    });
  },
}));

test('It render the link with the translated value', () => {
  renderWithProviders(
    <TranslationWithComponent
      id='text.with.link'
      components={{
        link: <Link href='#/about-akeneo'> </Link>
      }}
    />
  );
  screen.debug();

  expect(screen.getByRole('link')).toBeInTheDocument();
  expect(screen.getByText('this article may help you')).toHaveAttribute('href', '#/about-akeneo');
});

test('It render the link with an highlight', () => {
  renderWithProviders(
    <TranslationWithComponent
      id='text.with.highlight'
      parameters={{
        productNumber: 6
      }}
      components={{
        highlight: <Highlight />
      }}
    />
  );

  screen.debug();
});

test('It render translation starting with component', () => {
  renderWithProviders(
    <TranslationWithComponent
      id='text.start_with.highlight'
      parameters={{
        productNumber: 6
      }}
      components={{
        highlight: <Highlight />
      }}
    />
  );

  screen.debug();
});
