<x-mail::message>
# Votre serveur IA est prêt 🚀

**Bonjour {{ $user->name }}**,

Votre serveur d'inférence IA dédié a été provisionné avec succès. Voici vos **identifiants sécurisés** — conservez-les précieusement.

---

## Détails du serveur

| Détail | Valeur |
|:-------|:-------|
| **Modèle** | {{ $modelName }} |
| **GPU** | {{ $gpuName }} |
| **Statut** | ✅ En ligne |

---

## Votre lien de connexion

<x-mail::button :url="$loginUrl" color="success">
Ouvrir votre IA WebUI
</x-mail::button>

---

## Identifiants administrateur

> **⚠️ Important :** Sauvegardez ces identifiants immédiatement. Vous en aurez besoin pour vous connecter.

- **Identifiant :** `{{ $pod->admin_username }}`
- **Mot de passe :** `{{ $pod->admin_password }}`

---

## Ce qui est inclus

- **Open WebUI** — Une interface riche, style ChatGPT, pour votre modèle privé
- **Ollama** — Pré-configuré avec votre modèle sélectionné (`{{ $modelName }}`)
- **Accès API complet** — Compatible avec le format d'API OpenAI

## Pour commencer

1. Cliquez sur le lien de connexion ci-dessus
2. Connectez-vous avec l'identifiant et le mot de passe fournis
3. Commencez à discuter avec votre assistant IA privé immédiatement

---

<x-mail::subcopy>
Ceci est un message automatique de **{{ config('app.name') }}**. Si vous n'avez pas souscrit à ce service, veuillez contacter le support immédiatement.

*Votre abonnement est facturé mensuellement à **{{ number_format($tenant->monthly_subscription_price, 2) }} $/mois**.*
</x-mail::subcopy>
</x-mail::message>
