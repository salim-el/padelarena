import { Button } from '@/components/ui/button';
import { BackButton } from '@/components/back-button';
import AppLayout from '@/layouts/app-layout';
import { euros } from '@/lib/money';
import { Head, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

type Booking = { id: string; court: string; date: string; time: string; price_cents: number; vat_rate: number; hold_expires_at: string | null; free_cancellation_until: string };

export default function Checkout({ booking }: { booking: Booking }) {
    const { data, setData, post, processing, errors } = useForm<{ accept_terms: boolean }>({ accept_terms: false });
    const [left, setLeft] = useState(0);

    useEffect(() => {
        const tick = () => setLeft(Math.max(0, Math.floor((new Date(booking.hold_expires_at ?? 0).getTime() - Date.now()) / 1000)));
        tick();
        const t = setInterval(tick, 1000);
        return () => clearInterval(t);
    }, [booking.hold_expires_at]);

    return (
        <AppLayout breadcrumbs={[{ title: 'Paiement', href: '#' }]}>
            <Head title="Paiement" />
            <div className="mx-auto flex w-full max-w-xl flex-col gap-4 p-4">
                <BackButton href="/planning">Planning</BackButton>
                <h1 className="text-2xl font-bold">Paiement</h1>
                <div className="rounded-xl border p-4">
                    <p className="font-semibold">{booking.court} · {booking.date} · {booking.time}</p>
                    <p>Prix TTC : <strong>{euros(booking.price_cents)}</strong> (TVA {booking.vat_rate} % comprise)</p>
                    <p className="mt-2 text-sm">Annulation gratuite jusqu’au {booking.free_cancellation_until}.</p>
                    <p className="mt-2 text-sm" aria-live="polite">Créneau bloqué pour vous encore {Math.floor(left / 60)} min {String(left % 60).padStart(2, '0')} s.</p>
                </div>
                <p className="rounded-md bg-[#E6ECF2] p-3 text-sm text-[#17304A]">Version alpha : le paiement est simulé. Aucune carte n’est débitée.</p>
                <form onSubmit={(e) => { e.preventDefault(); post(`/bookings/${booking.id}/pay`); }} className="flex flex-col gap-3">
                    <label className="flex items-start gap-2 text-sm">
                        <input type="checkbox" checked={data.accept_terms} onChange={(e) => setData('accept_terms', e.target.checked)} />
                        J’accepte les conditions générales et la règle d’annulation de 24 heures.
                    </label>
                    {errors.accept_terms && <p className="text-sm text-[#9E2833]">Vous devez accepter les conditions pour réserver.</p>}
                    <Button type="submit" disabled={processing || left === 0}>Payer {euros(booking.price_cents)}</Button>
                </form>
            </div>
        </AppLayout>
    );
}
