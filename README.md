# blank-project

Starter template for Node.js projects with ESM, TypeScript type-checking, ESLint (metarhia config), and Prettier.

## Quick start

```bash
pnpm install
mv .env.example .env
pnpm start
```

## Scripts

| Command              | Description                  |
| -------------------- | ---------------------------- |
| `pnpm start`         | Run `app/main.js` with .env  |
| `pnpm run typecheck` | Type-check with TypeScript   |
| `pnpm run lint`      | Lint with ESLint             |
| `pnpm run lint:fix`  | Lint and auto-fix            |
| `pnpm run prettier`  | Format with Prettier         |

## Requirements

- Node.js >= 24
- pnpm
