declare namespace jest {
    interface Matchers<R> {
      toHaveStyleRule: import("jest-styled-components").jest.Matchers<R>["toHaveStyleRule"]
    }
  }
