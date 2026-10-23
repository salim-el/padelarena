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
