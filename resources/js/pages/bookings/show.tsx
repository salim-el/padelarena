import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BackButton } from '@/components/back-button';
import { StatusBadge } from '@/components/status-badge';
import { euros } from '@/lib/money';
import { Head, router, usePage } from '@inertiajs/react';

type Booking = { id: string; court: string; date: string; time: string; status: string; price_cents: number; free_cancellation_until: string; cancellable: boolean };
type Props = { booking: Booking; qr: string | null; qr_window: string | null; invoices: { number: string; total_cents: number; vat_cents: number }[] };

export default function Show({ booking, qr, qr_window, invoices }: Props) {
    const { errors } = usePage<{ errors: Record<string, string> }>().props;

    const cancel = () => {
        if (confirm('Annuler cette réservation ?')) router.post(`/bookings/${booking.id}/cancel`);
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Mes réservations', href: '/bookings' }, { title: booking.court, href: '#' }]}>
            <Head title="Réservation" />
            <div className="mx-auto flex w-full max-w-xl flex-col gap-4 p-4">
                {errors.booking && <p role="alert" className="rounded-md border border-[#9E2833]/30 bg-[#FCE6E8] text-[#9E2833] p-3 text-sm">{errors.booking}</p>}
                <BackButton href="/bookings">Mes réservations</BackButton>
                <h1 className="text-2xl font-bold">{booking.court}</h1>
                <p className="flex flex-wrap items-center gap-2">{booking.date} · {booking.time} <StatusBadge status={booking.status} /></p>
                {qr && (
                    <div className="rounded-xl border p-4">
                        <h2 className="font-semibold">Votre QR code</h2>
                        <p className="text-sm">Personnel, valable de {qr_window}, pour ce terrain uniquement.</p>
                        <img src={qr} alt="QR code d’accès au terrain" className="mt-2 h-48 w-48" />
                    </div>
                )}
                {invoices.length > 0 && (
                    <div className="rounded-xl border p-4 text-sm">
                        <h2 className="font-semibold">Facture</h2>
                        {invoices.map((i) => <p key={i.number}>{i.number} · {euros(i.total_cents)} dont TVA {euros(i.vat_cents)}</p>)}
                    </div>
                )}
                {booking.cancellable && (
                    <div className="flex flex-col gap-2">
                        <p className="text-sm">Remboursement complet si vous annulez avant le {booking.free_cancellation_until}.</p>
                        <Button variant="destructive" onClick={cancel}>Annuler la réservation</Button>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
