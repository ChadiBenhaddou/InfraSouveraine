<?php

namespace App\Livewire;

use App\Models\Tenant;
use App\Services\PayPalService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.app-layout')]

class BuyTestHours extends Component
{
    public ?Tenant $tenant = null;
    public float $balance = 0;
    public ?string $selectedPack = null;
    public string $checkoutUrl = '';

    public array $packs = [
        '1'   => ['hours' => 1,   'price' => 3.99,   'label' => '1 heure',    'desc' => 'Pour un test rapide'],
        '10'  => ['hours' => 10,  'price' => 19.99,  'label' => '10 heures',  'desc' => 'Idéal pour un premier test'],
        '50'  => ['hours' => 50,  'price' => 79.99,  'label' => '50 heures',  'desc' => 'Pour des tests réguliers'],
        '100' => ['hours' => 100, 'price' => 129.99, 'label' => '100 heures', 'desc' => 'Pour une utilisation intensive'],
    ];

    public function mount(): void
    {
        $this->tenant = auth()->user()?->tenants()->latest()->first();
        $this->balance = $this->tenant?->test_hours_balance ?? 0;
    }

    public function selectPack(string $hours): void
    {
        $this->selectedPack = $hours;
    }

    public function purchase(): void
    {
        if (!$this->selectedPack || !isset($this->packs[$this->selectedPack])) {
            return;
        }

        if (!$this->tenant) {
            $this->tenant = auth()->user()->tenants()->create([
                'subscription_status' => 'pending',
            ]);
        }

        $pack = $this->packs[$this->selectedPack];

        if (config('settings.testing_mode')) {
            $this->tenant->increment('test_hours_balance', $pack['hours']);
            session()->flash('message', "{$pack['label']} ajoutées à votre compte (mode test).");
            $this->balance = $this->tenant->fresh()->test_hours_balance;
            $this->selectedPack = null;
            return;
        }

        $paypal = app(PayPalService::class);

        $order = $paypal->createOrder(
            $pack['price'],
            "{$pack['label']} de test — InfraSouveraine : {$pack['desc']}",
            route('test-hours.success'),
            route('test-hours.cancel'),
        );

        $approvalUrl = $paypal->getApprovalUrl($order);

        if ($approvalUrl) {
            $this->redirect($approvalUrl);
        } else {
            session()->flash('error', 'Impossible de créer la commande PayPal.');
        }
    }

    public function render()
    {
        return view('livewire.buy-test-hours');
    }
}
