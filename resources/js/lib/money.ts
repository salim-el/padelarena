export const euros = (cents: number | null | undefined) =>
    cents == null ? '–' : (cents / 100).toLocaleString('fr-BE', { style: 'currency', currency: 'EUR' });

export const STATUS_LABELS: Record<string, string> = {
    pending_payment: 'En attente de paiement',
    expired: 'Expirée',
    confirmed: 'Confirmée',
    used: 'Utilisée',
    missed: 'Manquée',
    cancelled_refunded: 'Annulée et remboursée',
    cancelled_unrefunded: 'Annulée sans remboursement',
    frozen: 'Gelée',
};

// Majuscule au premier mot seulement, comme le veut le français (« Dimanche 25 octobre »).
export const ucfirst = (s: string) => s.charAt(0).toUpperCase() + s.slice(1);

const MONTHS: Record<string, string> = {
    janvier: 'janv.',
    février: 'févr.',
    mars: 'mars',
    avril: 'avr.',
    mai: 'mai',
    juin: 'juin',
    juillet: 'juil.',
    août: 'août',
    septembre: 'sept.',
    octobre: 'oct.',
    novembre: 'nov.',
    décembre: 'déc.',
};
export const shortMonth = (m: string | undefined) => (m ? (MONTHS[m] ?? m) : '');

export const GAME_MODES: Record<string, { label: string; text: string }> = {
    classic: { label: 'Classique', text: 'Un match de padel normal, sans supplément.' },
    zone_hunt: { label: 'Chasse aux zones', text: 'Une zone verte s’allume : placez la balle dedans pour marquer des points bonus.' },
    duel: { label: 'Duel', text: 'Deux équipes, deux couleurs. Chaque zone gagnée reste allumée à vos couleurs.' },
    free: { label: 'Jeu libre', text: 'Un match normal avec l’ambiance arcade : lumière, son et score affiché.' },
};

// Supplément du mode arcade, identique à l'écart des tarifs (48 € → 62 €).
export const ARCADE_SUPPLEMENT_CENTS = 1400;
