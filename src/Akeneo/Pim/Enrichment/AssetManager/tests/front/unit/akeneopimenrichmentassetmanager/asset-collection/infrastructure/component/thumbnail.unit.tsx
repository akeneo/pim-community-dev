import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, fireEvent} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {Thumbnail} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-collection/thumbnail';

test('It render a thumbnail', () => {
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail asset={{
        code: 'sideview',
        labels: {
          'en_US': 'Sideview'
        }
      }} context={{
        locale: 'en_US',
        channel: 'ecommerce'
      }} readonly={false} assetCollection={['frontview', 'sideview', 'backview']} onRemove={() => {}} onMove={() => {}}/>
    </ThemeProvider>
  );
  expect(getByText('Remove')).toBeInTheDocument();
  expect(getByText('Left')).toBeInTheDocument();
  expect(getByText('Right')).toBeInTheDocument();
});
test('It render the first thumbnail of a collection', () => {
  const {getByText, queryByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail asset={{
        code: 'sideview',
        labels: {
          'en_US': 'Sideview'
        }
      }} context={{
        locale: 'en_US',
        channel: 'ecommerce'
      }} readonly={false} assetCollection={['sideview', 'frontview', 'backview']} onRemove={() => {}} onMove={() => {}}/>
    </ThemeProvider>
  );
  expect(getByText('Remove')).toBeInTheDocument();
  expect(queryByText('Left')).not.toBeInTheDocument();
  expect(getByText('Right')).toBeInTheDocument();
});
test('It render the last thumbnail of a collection', () => {
  const {getByText, queryByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail asset={{
        code: 'sideview',
        labels: {
          'en_US': 'Sideview'
        }
      }} context={{
        locale: 'en_US',
        channel: 'ecommerce'
      }} readonly={false} assetCollection={['frontview', 'backview', 'sideview']} onRemove={() => {}} onMove={() => {}}/>
    </ThemeProvider>
  );
  expect(getByText('Remove')).toBeInTheDocument();
  expect(queryByText('Right')).not.toBeInTheDocument();
  expect(getByText('Left')).toBeInTheDocument();
});

test('It render a readonly thumbnail', () => {
  const {queryByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail asset={{
        code: 'sideview',
        labels: {
          'en_US': 'Sideview'
        }
      }} context={{
        locale: 'en_US',
        channel: 'ecommerce'
      }} readonly={true} assetCollection={['frontview', 'sideview', 'backview']} onRemove={() => {}} onMove={() => {}}/>
    </ThemeProvider>
  );
  expect(queryByText('Remove')).not.toBeInTheDocument();
  expect(queryByText('Left')).not.toBeInTheDocument();
  expect(queryByText('Right')).not.toBeInTheDocument();
});

test('It trigger event on remove asset by clicking', () => {
  let isRemoved = false;
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail asset={{
        code: 'sideview',
        labels: {
          'en_US': 'Sideview'
        }
      }} context={{
        locale: 'en_US',
        channel: 'ecommerce'
      }} readonly={false} assetCollection={['frontview', 'sideview', 'backview']} onRemove={() => {
        isRemoved = true
      }} onMove={() => {}}/>
    </ThemeProvider>
  );

  fireEvent.click(getByText('Remove'));
  expect(isRemoved).toEqual(true);
});

test('It trigger event on remove asset with keyboard', () => {
  let isRemoved = false;
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail asset={{
        code: 'sideview',
        labels: {
          'en_US': 'Sideview'
        }
      }} context={{
        locale: 'en_US',
        channel: 'ecommerce'
      }} readonly={false} assetCollection={['frontview', 'sideview', 'backview']} onRemove={() => {
        isRemoved = true
      }} onMove={() => {}}/>
    </ThemeProvider>
  );

  fireEvent.keyPress(getByText('Remove'), { key: ' ', keyCode: 32, charCode: 32 });
  expect(isRemoved).toEqual(true);
});

test('It trigger event on move asset left by clicking', () => {
  let isMovedLeft = false;
  let isMovedRight = false;
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail asset={{
        code: 'sideview',
        labels: {
          'en_US': 'Sideview'
        }
      }} context={{
        locale: 'en_US',
        channel: 'ecommerce'
      }} readonly={false} assetCollection={['frontview', 'sideview', 'backview']} onRemove={() => {}} onMove={(direction) => {
        if (direction === 0) isMovedLeft = true;
        if (direction === 1) isMovedRight = true;
      }}/>
    </ThemeProvider>
  );

  fireEvent.click(getByText('Left'));
  expect(isMovedLeft).toEqual(true);
  expect(isMovedRight).toEqual(false);
});

test('It trigger event on move asset left with keyboard', () => {
  let isMovedLeft = false;
  let isMovedRight = false;
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail asset={{
        code: 'sideview',
        labels: {
          'en_US': 'Sideview'
        }
      }} context={{
        locale: 'en_US',
        channel: 'ecommerce'
      }} readonly={false} assetCollection={['frontview', 'sideview', 'backview']} onRemove={() => {}} onMove={(direction) => {
        if (direction === 0) isMovedLeft = true;
        if (direction === 1) isMovedRight = true;
      }}/>
    </ThemeProvider>
  );

  fireEvent.keyPress(getByText('Left'), { key: ' ', keyCode: 32, charCode: 32 });
  expect(isMovedLeft).toEqual(true);
  expect(isMovedRight).toEqual(false);
});

test('It trigger event on move asset right by clicking', () => {
  let isMovedLeft = false;
  let isMovedRight = false;
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail asset={{
        code: 'sideview',
        labels: {
          'en_US': 'Sideview'
        }
      }} context={{
        locale: 'en_US',
        channel: 'ecommerce'
      }} readonly={false} assetCollection={['frontview', 'sideview', 'backview']} onRemove={() => {}} onMove={(direction) => {
        if (direction === 0) isMovedLeft = true;
        if (direction === 1) isMovedRight = true;
      }}/>
    </ThemeProvider>
  );

  fireEvent.click(getByText('Right'));
  expect(isMovedLeft).toEqual(false);
  expect(isMovedRight).toEqual(true);
});

test('It trigger event on move asset right with keyboard', () => {
  let isMovedLeft = false;
  let isMovedRight = false;
  const {getByText} = render(
    <ThemeProvider theme={akeneoTheme}>
      <Thumbnail asset={{
        code: 'sideview',
        labels: {
          'en_US': 'Sideview'
        }
      }} context={{
        locale: 'en_US',
        channel: 'ecommerce'
      }} readonly={false} assetCollection={['frontview', 'sideview', 'backview']} onRemove={() => {}} onMove={(direction) => {
        if (direction === 0) isMovedLeft = true;
        if (direction === 1) isMovedRight = true;
      }}/>
    </ThemeProvider>
  );

  fireEvent.keyPress(getByText('Right'), { key: ' ', keyCode: 32, charCode: 32 });
  expect(isMovedLeft).toEqual(false);
  expect(isMovedRight).toEqual(true);
});
