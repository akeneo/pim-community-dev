import React, {ReactNode} from 'react';
import {renderHookWithProviders} from '../tests/utils';
import {useTranslateWithComponents} from './useTranslateWithComponents';

jest.mock('../hooks/useTranslate', () => ({
  useTranslate: () => {
    return jest.fn((key: string, params: {[k: string]: string}) => {
      switch (key) {
        case 'text.simple':
          return 'Simple text';
        case 'text.with.link':
          return 'To know more about akeneo, <link>this article may help you</link>';
        case 'text.with.placeholder':
          return `To know more about <b>akeneo</b>, <link>${params.read_more}</link> !`
        case 'text.with.unknown_component':
          return `To know more about akeneo, <unknown>this article may help you</unknown>`
        default:
          return key;
      }
    });
  },
}));

test('it returns the Translate', () => {
  const {result} = renderHookWithProviders(() => useTranslateWithComponents());

  expect(result.current).not.toBeNull();
});

test('it translates simple sentence', () => {
  const {result} = renderHookWithProviders(() => useTranslateWithComponents());
  const translate = result.current;
  const current: ReactNode = translate('text.simple', {});
  expect(current).toEqual(<>{['Simple text']}</>);
});

test('it translates sentence with Component', () => {
  const {result} = renderHookWithProviders(() => useTranslateWithComponents());
  const translate = result.current;
  const current: ReactNode = translate('text.with.link', {
    link: (innerText) => <a>{innerText}</a>
  });

  expect(current).toEqual(<>{[
    "To know more about akeneo, ",
    <a key="link">this article may help you</a>,
  ]}</>);
});

test('it translates sentence with Component and placeholder', () => {
  const {result} = renderHookWithProviders(() => useTranslateWithComponents());
  const translate = result.current;
  const current: ReactNode = translate('text.with.placeholder', {
    link: (innerText) => <a>{innerText}</a>,
    b: (innerText) => <b>{innerText}</b>,
  }, {
    read_more: 'read more'
  });

  expect(current).toEqual(<>{[
    "To know more about ",
    <b key="b">akeneo</b>,
    ", ",
    <a key="link">read more</a>,
    " !",
  ]}</>);
});

test('it translates sentence with Component and placeholder', () => {
  const {result} = renderHookWithProviders(() => useTranslateWithComponents());
  const translate = result.current;
  const current: ReactNode = translate('text.with.unknown_component', {
    link: (innerText) => <a>{innerText}</a>,
  });

  expect(current).toEqual(<>{[
    "To know more about akeneo, <unknown>this article may help you</unknown>",
  ]}</>);
});

