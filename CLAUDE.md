# CLAUDE.md — AI Assistant Guide for matyasprorokcz

## Project Overview

**matyasprorokcz** is a personal website project owned by Moses-PHP (Matyas Prorok). The repository is in its initial phase with no source code yet — this document establishes conventions and workflows that should be followed as the project grows.

- **Repository:** Moses-PHP/matyasprorokcz
- **Language of project owner:** Czech (comments and commit messages may be in Czech or English)
- **Purpose:** Personal website (`Můj osobní web` = My personal website)

---

## Repository State (as of initial commit)

```
matyasprorokcz/
├── .git/
├── README.md       # Project title and one-line description
└── CLAUDE.md       # This file
```

No technology stack has been chosen yet. When source code is added, update the sections below accordingly.

---

## Git Conventions

### Branch Strategy

- **Default branch:** `master`
- **Feature/AI work branches:** `claude/<description>-<session-id>` (e.g., `claude/claude-md-mmd7cfzzc0iht35x-eFLhZ`)
- Never push directly to `master` without explicit permission from the repository owner.

### Commit Messages

- Use clear, descriptive commit messages in English or Czech.
- Follow the imperative mood: "Add hero section", "Fix navigation bug", "Update contact form".
- Keep the subject line under 72 characters.
- Commits are GPG/SSH signed — do not use `--no-gpg-sign` or `--no-verify`.

### Git Operations

```bash
# Push to a feature branch
git push -u origin <branch-name>

# Fetch a specific branch
git fetch origin <branch-name>
```

If push fails due to network errors, retry up to 4 times with exponential backoff (2s, 4s, 8s, 16s).

---

## Development Workflow

Since the project has no build system yet, these are placeholder conventions to follow once a stack is chosen:

### Before Starting Work

1. Check out the correct working branch.
2. Pull the latest changes: `git pull origin <branch-name>`
3. Review any existing issues or task descriptions.

### Making Changes

1. Make the minimum changes required for the task — avoid over-engineering.
2. Do not add features, refactoring, or cleanup beyond what was requested.
3. Test changes locally before committing.
4. Commit frequently with clear messages.

### After Completing Work

1. Push changes to the designated branch.
2. Do not open pull requests unless explicitly requested.

---

## Technology Stack (TBD)

The stack has not been defined yet. When it is, document:

- **Language/framework:** (e.g., PHP/Laravel, JavaScript/Next.js, HTML/CSS)
- **Package manager:** (e.g., npm, composer, pip)
- **Build tool:** (e.g., Vite, webpack)
- **Test runner:** (e.g., PHPUnit, Jest, Vitest)
- **Linter/formatter:** (e.g., ESLint, Prettier, PHP CS Fixer)

### Common Commands (placeholder — update when stack is set)

```bash
# Install dependencies
# <command here>

# Run development server
# <command here>

# Build for production
# <command here>

# Run tests
# <command here>

# Lint/format code
# <command here>
```

---

## Coding Conventions

When source code is added, follow these general principles:

- **Keep it simple.** This is a personal website — avoid over-abstraction.
- **No unnecessary dependencies.** Prefer native browser APIs or minimal libraries.
- **Accessibility.** Use semantic HTML and proper ARIA attributes where needed.
- **Performance.** Optimize images, minimize JS, prefer CSS over JS animations.
- **Czech/English.** Code comments may be in either language; be consistent within a file.

---

## Security Notes

- Do not commit secrets, API keys, or credentials. Use environment variables.
- Do not add `.env` files to version control — add them to `.gitignore`.
- Be cautious with third-party scripts included in the frontend.

---

## Updating This File

Keep this file current as the project evolves:

- When a technology stack is chosen, fill in the **Technology Stack** section.
- When build/test/lint commands are established, update **Common Commands**.
- When deployment is configured, add a **Deployment** section.
- When CI/CD is set up, add a **CI/CD** section.
