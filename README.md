# InfraSouveraine

**Votre IA, souveraine et confidentielle.**

A multi-tenant SaaS platform that lets professionals and businesses deploy private, isolated AI LLM servers on dedicated GPUs via the RunPod cloud API.

## Features

- **AI-Powered Model Recommendation** — Describe your use case; GPT-4o-mini recommends the best open-source model and GPU tier (with keyword-based fallback).
- **GPU Tier Selection** — Choose from RTX 4090, RTX A6000, A100 40GB/80GB, or H100, each with VRAM, performance estimates, and hourly rate.
- **Fixed Monthly Pricing** — GPU cost + storage + margin + platform markup, computed instantly.
- **PayPal Billing** — Monthly subscriptions or pay-as-you-go test hours (1h/10h/50h/100h).
- **Automated RunPod Provisioning** — Creates a RunPod pod running Open WebUI + Ollama with the selected model pre-configured.
- **Pod Monitoring** — Polls RunPod API until the pod is ready, then emails credentials to the user.
- **Filament Admin Panel** (`/admin`) — Manage clients, pods, and pricing plans.
- **Scheduled Sync** — Pod status and profit metrics synced automatically.
- **Testing Mode** — `APP_TESTING_MODE=true` bypasses real payments and simulates provisioning for demos.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 11 (PHP 8.2+) |
| Admin Panel | Filament 3.2 |
| Frontend | Livewire 3.4, Tailwind CSS 3.4, Alpine.js |
| Build | Vite 5 |
| Payments | PayPal REST API (subscriptions + orders) |
| AI | OpenAI API (GPT-4o-mini) |
| GPU Cloud | RunPod API |
| Database | MySQL (dev), SQLite (tests) |
| Queue | Database-driven |
| Testing | PHPUnit 11, Mockery |

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL (or SQLite for testing)
- RunPod account with API key
- PayPal sandbox/live credentials (optional in test mode)

## Installation

```bash
git clone <repo-url> nodataleak
cd nodataleak

composer install
npm install

cp .env.example .env
# Edit .env with your DB credentials, RunPod API key, PayPal keys, etc.

php artisan key:generate
php artisan migrate

# Build frontend
npm run build
```

## Running

```bash
# Serve the application
php artisan serve

# Process queued jobs (provisioning, monitoring, emails)
php artisan queue:work

# Run scheduled tasks (pod sync, profit sync)
php artisan schedule:work
```

## Testing

```bash
php artisan test
# or
./vendor/bin/phpunit
```

## Project Structure

```
app/
├── Enums/              # GpuTier, PodStatus, SubscriptionStatus
├── Filament/           # Admin panel resources (Client, Plan, Pod)
├── Http/
│   ├── Controllers/    # Auth, Onboarding, Webhooks (PayPal, RunPod)
│   └── Livewire/       # OnboardingWizard, BuyTestHours
├── Jobs/               # ProvisionRunPodPod, MonitorPodStatus, SendWelcomeEmail
├── Mail/               # WelcomeWithCredentials
├── Models/             # Tenant, User, Pod, Plan, TestHourPurchase
└── Services/           # AiRecommender, CostCalculator, PayPalService, RunPodApi, SubscriptionManager
```

## Configuration

Key `.env` variables:

| Variable | Default | Description |
|---|---|---|
| `APP_TESTING_MODE` | `false` | Bypass real payments & simulate pods |
| `RUNPOD_API_KEY` | — | RunPod API key |
| `RUNPOD_DEFAULT_IMAGE_NAME` | `ghcr.io/open-webui/open-webui:ollama` | Pod container image |
| `PAYPAL_CLIENT_ID` / `PAYPAL_SECRET` | — | PayPal REST API credentials |
| `PAYPAL_MODE` | `sandbox` | `sandbox` or `live` |
| `STORAGE_COST_PER_GB_MONTHLY` | `0.10` | Storage cost ($/GB/month) |
| `DEFAULT_BENEFIT_MARGIN` | `0.35` | Default margin (35%) |
| `FIXED_PLATFORM_MARKUP` | `9.99` | Fixed monthly platform fee ($) |

## Architecture Flow

```
User → Register → Onboarding Wizard → AI Recommender → Select GPU Tier
→ Review Pricing → PayPal Checkout → Webhook → Provision Pod
→ Monitor Pod → Welcome Email → Dashboard
```

## License

Proprietary — all rights reserved.
