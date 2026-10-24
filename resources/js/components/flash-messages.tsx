import { usePage } from '@inertiajs/react';

// Confirmation renvoyée par le serveur après une action, affichée par la mise en page sur tous les écrans.
export function FlashMessages() {
    const { flash } = usePage<{ flash?: { success?: string } }>().props;
    if (!flash?.success) return null;

    return (
        <p role="status" aria-live="polite" className="mx-4 mt-4 rounded-md border border-[#4E6600]/30 bg-[#EEF4D6] p-3 text-sm text-[#4E6600]">
            {flash.success}
        </p>
    );
}
