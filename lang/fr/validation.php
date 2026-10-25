<?php

// Seules les règles utilisées par l'application sont traduites.
return [
    'accepted' => 'Vous devez accepter :attribute.',
    'after_or_equal' => ':Attribute doit être une date égale ou postérieure au :date.',
    'before_or_equal' => ':Attribute doit être une date égale ou antérieure au :date.',
    'confirmed' => 'La confirmation de :attribute ne correspond pas.',
    'current_password' => 'Le mot de passe est incorrect.',
    'date' => ':Attribute n’est pas une date valide.',
    'date_format' => ':Attribute ne respecte pas le format :format.',
    'email' => ':Attribute doit être une adresse e-mail valide.',
    'exists' => ':Attribute sélectionné est invalide.',
    'integer' => ':Attribute doit être un nombre entier.',
    'lowercase' => ':Attribute doit être en minuscules.',
    'max' => ['string' => ':Attribute ne peut pas dépasser :max caractères.'],
    'min' => ['string' => ':Attribute doit contenir au moins :min caractères.'],
    'password' => [
        'letters' => ':Attribute doit contenir au moins une lettre.',
        'mixed' => ':Attribute doit contenir au moins une majuscule et une minuscule.',
        'numbers' => ':Attribute doit contenir au moins un chiffre.',
        'symbols' => ':Attribute doit contenir au moins un symbole.',
        'uncompromised' => 'Ce mot de passe est apparu dans une fuite de données. Choisissez-en un autre.',
    ],
    'required' => 'Le champ :attribute est obligatoire.',
    'string' => ':Attribute doit être un texte.',
    'unique' => 'Cette :attribute est déjà utilisée.',
    'attributes' => [
        'name' => 'nom',
        'email' => 'adresse e-mail',
        'password' => 'mot de passe',
        'current_password' => 'mot de passe actuel',
        'accept_terms' => 'les conditions générales',
        'date' => 'la date',
        'token' => 'contenu du QR code',
        'court_id' => 'terrain',
    ],
];
