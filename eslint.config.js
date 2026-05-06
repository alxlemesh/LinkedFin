// https://github.com/metarhia/eslint-config-metarhia
// Пример использования https://github.com/metarhia/metacom/blob/master/eslint.config.js
import config from 'eslint-config-metarhia';
import simpleImportSort from 'eslint-plugin-simple-import-sort';
import tseslint from 'typescript-eslint';

export default [
  ...config,
  ...tseslint.configs.recommended,
  {
    languageOptions: {
      sourceType: 'module',
    },
    plugins: {
      'simple-import-sort': simpleImportSort,
    },
    rules: {
      'max-len': [
        'error',
        {
          code: 100,
          ignoreComments: true,
          ignoreUrls: true,
          ignoreStrings: true,
          ignoreTemplateLiterals: true,
        },
      ],
      'prefer-template': 'error',
      'space-in-parens': ['error', 'never'],
      'no-multi-spaces': 'error',
      'no-extra-parens': 'off',
      'no-unused-vars': 'off',
      '@typescript-eslint/no-unused-vars': 'error',
      '@typescript-eslint/no-explicit-any': 'off',
      'simple-import-sort/imports': 'error',
      'simple-import-sort/exports': 'error',
    },
  },
  { ignores: ['.vscode/'] },
];
