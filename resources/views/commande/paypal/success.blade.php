<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transaction réussie') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <p>Votre commande a été complétée avec succès !</p>
                    <p><strong>Commande ID :</strong> {{ $commande->id }}</p>
                    <p><strong>Montant :</strong> ${{ number_format($commande->total, 2) }}</p>
                    <p><strong>Statut :</strong> {{ ucfirst($commande->status) }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
