# G5 Guidelines

Contributing guidelines.

## Getting Started

1. Clone the repo
```bash
   git clone https://github.com/khaya05/fullstack-crypto-tax-calculator.git
   cd fullstack-crypto-tex-calculator
```

## Development Workflow

### 1. Create a Feature Branch

Always create a new branch for your work:
```bash
# Update your main branch
git checkout main
git pull upstream main

# Create feature branch
git checkout -b feature/your-feature-name
```

**Branch naming conventions:**
- `feature/` - New features (e.g., `feature/password-reset`)
- `bugfix/` - Bug fixes (e.g., `bugfix/login-validation`)
- `docs/` - Documentation updates (e.g., `docs/update-readme`)
- `refactor/` - Code refactoring (e.g., `refactor/auth-controller`)

### 2. Make Your Changes

- Write clean, readable code
- Follow the existing code style
- Add comments where necessary
- Test your changes thoroughly

### 3. Commit Your Changes

Write clear, descriptive commit messages e.g.:
```bash
git add .
git commit -m "Add password reset functionality
```

### 4. Push Your Branch
```bash
git push origin feature/your-feature-name
```

### 5. Create a Pull Request

1. Go to the repository on GitHub an open a PR