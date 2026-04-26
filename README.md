# End Poem Explained

Source code for [endpoemexplained.com](https://endpoemexplained.com), a hobby project that walks through *The End Poem* (Julian Gough's closing text for Minecraft) one passage at a time, with original commentary on what each line means and why it lands.

Built with Laravel 13, Livewire 4, Alpine.js, and Tailwind CSS 4.

## 👨‍💻 Development

1. Clone and install dependencies:

```bash
git clone git@github.com:jhm-ciberman/end-poem-explained.git
cd end-poem-explained
composer setup
```

2. Start the development server:

```bash
composer dev
```

Then open [http://localhost:8000](http://localhost:8000) in your browser.

## ✍️ Content

The poem and its commentary live as Markdown files under `resources/pages/`. Each file is one passage — a snippet of the poem (in YAML frontmatter) plus the analysis prose (Markdown body). Files are ordered by their three-digit numeric prefix.

See `notes/page-format.local.md` for the full format spec and `notes/paragraph-index.local.md` for how passages group into the original poem's paragraphs.

## 🧹 Code Style

```bash
./vendor/bin/pint
```

## 🧪 Testing

```bash
composer test
```

## 🌐 Deploying

Simply create a new release in GitHub and the website will be automatically deployed to the server.

> [!NOTE]
> **How it works:** When you create a new Github release, a GitHub Action will merge the `main` branch into the `production` branch and Forge will deploy the changes. The deployment is handled by Laravel Forge using the `production` branch.
