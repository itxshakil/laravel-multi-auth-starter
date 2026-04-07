- Always use `<script setup lang="ts">` in Vue single-file components.
- Vue components must have a single root element.
- Keep Inertia page components thin — data fetching belongs in composables, business logic in backend Actions.
- Use Wayfinder for all route/action references; never hardcode URLs.
  ```ts
  import { show } from '@/actions/UserController'
  // use show.url({ user: 1 }) or show({ user: 1 }) with useForm/useHttp
  ```
- Use `useForm()` for all form submissions to get automatic progress tracking and error handling.
- Use `useHttp()` for standalone XHR requests that are not tied to a full form.
- Use Inertia `<Link>` for all in-app navigation; never use `<a href>` for internal routes.
- Use `setLayoutProps` to pass data to the shared layout (title, breadcrumbs, etc.).
- Use Inertia `Inertia::defer()` on the backend for non-critical props; pair with a skeleton loader on the frontend.
- Use `prefetch` on `<Link>` for high-probability navigation targets to reduce perceived latency.
- Use optimistic updates via Inertia's `optimistic` option for instant UI feedback on mutations.
- Follow Tailwind v4 utility-first conventions; avoid writing custom CSS unless there is no utility equivalent.
- Define shared TypeScript types in `resources/js/types/`; import them consistently rather than using inline type literals.
- Use ESLint and Prettier for frontend code quality; run `npm run lint:check` and `npm run format:check` before committing.
- Run `php artisan wayfinder:generate` (or the Vite plugin equivalent) after any controller or route change to keep generated types in sync.
