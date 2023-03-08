const mockedFamiliesPage1 = [...Array(20)].map((_, i) => ({code: `Family${i}`, labels: {en_US: `Family${i} label`}}));

const mockedFamiliesPage2 = [...Array(10)].map((_, i) => ({code: `Family${i + 20}`, labels: {}}));

const mockedFamiliesSearch = [...Array(3)].map((_, i) => ({code: `Family${i + 40}`, labels: {}}));

export {mockedFamiliesPage1, mockedFamiliesPage2, mockedFamiliesSearch};
