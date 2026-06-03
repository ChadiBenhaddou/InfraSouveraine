<x-mail::message>
# Paiement échoué

**Bonjour {{ $user->name }}**,

Le dernier paiement de votre abonnement InfraSouveraine a échoué.

**Détails :**
- **Montant mensuel :** {{ number_format($tenant->monthly_subscription_price, 2) }} $
- **Statut :** En retard

Pour éviter l'interruption de votre service, veuillez mettre à jour vos informations de paiement dès que possible.

<x-mail::button :url="route('dashboard')" color="primary">
Mettre à jour mon paiement
</x-mail::button>

Si vous avez des questions, contactez notre support.

Merci de votre confiance,<br>
L'équipe {{ config('app.name') }}
</x-mail::message>
