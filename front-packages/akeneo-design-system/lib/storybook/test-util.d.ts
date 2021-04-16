import { ReactElement } from 'react';
import { RenderOptions } from '@testing-library/react';
declare const customRender: (ui: ReactElement, options?: Omit<RenderOptions<typeof import("@testing-library/dom/types/queries")>, "queries"> | undefined) => import("@testing-library/react").RenderResult<typeof import("@testing-library/dom/types/queries")>;
export * from '@testing-library/react';
export { customRender as render };
