# Pay for a Joke (FOSS & Whitelabel)

Simple PHP (Slim 4) app to accept Lightning payments and deliver a joke (random and AI‑generated). Fully configurable via environment variables for easy whitelabeling.

## What I learn

- How to use the coinos API to create a payment request
- Review PHP and MySQL 
- Use Slim framework to create a simple project
- Use Composer to manage dependencies
- Use dotenv to manage environment variables
- Recursive functions in JS for check the payment status
- Review Jquery Ajax function
- Use the bitcoin lightning network to make payments

## Requirements
- PHP 
- Composer
- MySQL

## Installation
1. Clone the repository
```bash
git clone <seu-fork-ou-repo>
cd PaguePorPiada
```

2. Install the dependencies
```bash
composer install
```

3. Migrate the database
```bash
php database/migration.php
```

4. Create a Coinos account and get a token
- Go to [Coinos](https://coinos.io/)
- Create an account
- Go to the [API section](https://coinos.io/docs) and create a new token

5. Create a `.env` file from `.env.example` and set the variables (do not commit this file)


See `.env.example` for all supported keys. Highlights:

- COINOS_TOKEN, COINOS_API_URL
- DB_HOST, DB_NAME, DB_USER, DB_PASSWORD
- APP_PUBLIC_URL, APP_TITLE
- OPENROUTER_API_KEY, OPENROUTER_ENDPOINT, OPENROUTER_MODEL
- CSP_CONNECT_SRC, CSP_IMG_SRC, CSP_SCRIPT_SRC, CSP_STYLE_SRC, CSP_FONT_SRC
- UMAMI_SCRIPT_URL, UMAMI_WEBSITE_ID
- STORAGE_URL, EBOOK_FILE, EBOOK_TITLE, EBOOK_DESCRIPTION, EBOOK_DOWNLOAD_URL

Notes:
- STORAGE_URL: base directory where your files live (default: storage). Can be absolute or relative to the project root.
- EBOOK_FILE: the PDF filename to deliver (default: cursobtc_mmzero.pdf).
- The default download route is `/generate/ebook`, but you can point `EBOOK_DOWNLOAD_URL` to any endpoint you control.
- CSP_* and Umami: adjust allowed sources and analytics script for your domain.

Security: never commit your secrets. Use `.env` only locally/in production environments.

6. Run the server
```bash
php -S localhost:8000 -t public
```

7. Open `http://localhost:8000/`

## Whitelabel: what is parameterized via ENV

- App title and Referer for OpenRouter headers (APP_PUBLIC_URL, APP_TITLE).
- OpenRouter endpoint and model (OPENROUTER_ENDPOINT, OPENROUTER_MODEL), and the key (OPENROUTER_API_KEY).
- CSP and external resources (CSP_CONNECT_SRC/IMG_SRC/SCRIPT_SRC/STYLE_SRC/CSP_FONT_SRC).
- Umami (UMAMI_SCRIPT_URL, UMAMI_WEBSITE_ID).
- Storage and e‑book file (STORAGE_URL, EBOOK_FILE) plus metadata (EBOOK_TITLE, EBOOK_DESCRIPTION) and public link (EBOOK_DOWNLOAD_URL).

## Endpoints

- POST `/api/criar-invoice` — creates an invoice on Coinos.
- GET `/api/checar/{id}` — checks payment status and returns joke(s) and reward.
- GET `/generate/ebook` — serves the PDF configured via ENV.

## Security Notes

- CSP blocks inline scripts (without `unsafe-inline`). Avoid inline handlers; use external JS instead.
- Keep API tokens in environment variables.


## Usage
- Click on the "Get a joke" button
- Pay with bitcoin lightning
- Get a random joke
- Enjoy!