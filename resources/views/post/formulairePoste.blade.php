<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Formulaire Poste') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">

                    <!-- Formulaire -->
                    <form method="POST" action="{{ route('insertionPoste') }}" class="space-y-4">
                        @csrf <!-- Protection obligatoire contre CSRF -->

                        <!-- User (readonly) -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Utilisateur</label>
                            <input type="text" id="name" name="name"
                                   value="{{ Auth::user()->name }}"
                                   readonly
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>

                        <!-- Titre -->
                        <div>
                            <label for="titre" class="block text-sm font-medium text-gray-700">Titre</label>
                            <input type="text" id="titre" name="titre"
                                   value="{{ old('titre') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>

                        <!-- prix -->
                        <div>
                            <label for="prix" class="block text-sm font-medium text-gray-700">Prix</label>
                            <input type="number" min="0" value="0" step="any" id="prix" name="prix"
                                   value="{{ old('prix') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" name="description" rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                        </div>
                        <div>
                            <label for="categorie" class="block text-sm font-medium text-gray-700">Categorie</label>
                            <select name="categorie" id="categorie" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                @foreach ($categories as $categorie)
                                    <option value="{{$categorie->id_categorie}}">{{$categorie->categorie}}</option>
                                @endforeach
                            </select>

                        </div>
                        <!-- Bouton -->
                        <div>
                            <button type="submit"
                                    class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 transition duration-200">
                                Publier
                            </button>
                        </div>
                    </form>
                    @if ($errors->any())
                        <div class="form_errors_div text-red-700">
                            <p>Veuillez corriger l'erreur ou les erreurs suivante(s) :</p>
                            <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
