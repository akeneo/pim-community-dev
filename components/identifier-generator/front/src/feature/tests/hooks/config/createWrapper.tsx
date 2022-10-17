import {QueryClient, QueryClientProvider} from 'react-query';
import React from 'react';

const createWrapper = () => {
  // creates a new QueryClient for each test
  const queryClient = new QueryClient();
  return ({children}: {children: React.ReactNode}) => (
    <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
  );
};

export {createWrapper};
