# CLAUDE.md — AI Assistant Guide for matyasprorokcz

## Project Overview

**matyasprorokcz** is a personal website project owned by Moses-PHP (Matyas Prorok). The repository is in its initial phase with no source code yet — this document establishes conventions and workflows that should be followed as the project grows.

- **Repository:** Moses-PHP/matyasprorokcz
- **Language of project owner:** Czech (comments and commit messages may be in Czech or English)
- **Purpose:** Personal website (`Můj osobní web` = My personal website)

---

## Repository State

```
matyasprorokcz/
├── .git/
├── .github/
│   └── workflows/
│       ├── deploy.yml   # FTP deployment to public_html on push to main
│       └── claude.yml   # Claude AI integration via GitHub Actions
├── .ftpignore           # Files excluded from FTP deployment
├── index.html           # Main HTML entry point
├── style.css            # Global styles
├── script.js            # Interactive JS (cursor glow, animations, easter egg)
├── README.md            # Project title and one-line description
└── CLAUDE.md            # This file
```

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

## Technology Stack

- **Language/framework:** Vanilla HTML / CSS / JavaScript (no build step)
- **Deployment:** FTP via GitHub Actions (`SamKirkland/FTP-Deploy-Action`)
- **AI integration:** Claude via GitHub Actions (`anthropics/claude-code-action`)

### Common Commands

```bash
# No build step needed — open index.html directly in a browser
# or serve locally with any static file server, e.g.:
npx serve .

# Deploy: push to the `main` branch — GitHub Actions handles FTP upload automatically
```

---

## CI/CD

### Deploy workflow (`.github/workflows/deploy.yml`)

Triggers on every push to `main` and uploads site files to the FTP server using secrets:
- `FTP_SERVER`
- `FTP_USERNAME`
- `FTP_PASSWORD`

Files listed in `.ftpignore` (e.g. `README.md`, `CLAUDE.md`, `.github/`) are excluded from the upload.

### Claude AI workflow (`.github/workflows/claude.yml`)

Enables AI assistance directly inside GitHub issues and pull requests. Mention `@claude` in any issue or PR comment (or in the issue body when opening/assigning) to trigger the workflow.

**Required secret:** `ANTHROPIC_API_KEY` — set this in *Settings → Secrets and variables → Actions* for the repository.

**Example usage:**
```
@claude Please review this CSS change and suggest improvements.
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

- Update **Repository State** when new files are added.
- Update **Technology Stack** if the stack changes (e.g. a framework is introduced).
- Update **CI/CD** if new workflows are added or existing ones are modified.
