---
name: version-control
description: Apply version control conventions when creating commits, branches, pull requests, or setting up a repository.
---

# Version Control

## Repository Naming

- Name package/library repositories using kebab-case: `my-package-name`
- Name application repositories after their primary domain in lowercase: `myapp.com`

## Branches

- `main` is the stable production branch; it must always be deployable
- All work happens on short-lived feature branches branched from `main`
- Branch names must be lowercase with hyphens only — no underscores, no camelCase
  - Good: `feature-user-avatars`, `fix-invoice-total`, `updates-april-2026`
  - Avoid: `feature/user-avatars`, `fixInvoiceTotal`
- Never commit directly to `main` on shared or production projects; always go through a pull request

## Commits

- Write commit messages in the present tense
  - Good: `add user avatar upload`, `fix vat calculation in delivery costs`
  - Avoid: `added avatar`, `fixed vat`, `wip`, `stuff`
- Keep commits small and focused on a single logical change; avoid bundling unrelated changes
- Use `git add -p` to stage changes interactively and review each hunk before committing; avoid blanket `git add .`
- Follow Conventional Commits format as required by the project coding standards: `feat:`, `fix:`, `refactor:`, `test:`, `chore:`

## Merging & Rebasing

- Rebase feature branches onto `main` regularly to stay current and reduce merge conflicts
- Squash-merge feature branches into `main` when deploying to keep the production history linear and readable

## What Not to Commit

- Generated files and compiled assets (`public/build/`, `*.css.map`)
- Vendor directories (`vendor/`, `node_modules/`)
- Environment files (`.env`, `.env.local`)
- IDE and OS files (`.DS_Store`, `.idea/`)

Ensure `.gitignore` excludes all of the above before the first commit.
