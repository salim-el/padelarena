import AppLayout from '@/layouts/app-layout';
import { StatusBadge } from '@/components/status-badge';
import { euros } from '@/lib/money';
import { Head, Link } from '@inertiajs/react';

type Booking = { id: string; court: string; date: string; time: string; status: string; price_cents: number };

export default function Index({ bookings }: { bookings: Booking[] }) {
    return (
        <AppLayout breadcrumbs={[{ title: 'Mes réservations', href: '/bookings' }]}>
            <Head title="Mes réservations" />
            <div className="flex flex-col gap-4 p-4">
                <h1 className="text-2xl font-bold">Mes réservations</h1>
                {bookings.length === 0 ? (
                    <p>Aucune réservation. <Link href="/planning" className="underline">Réserver un terrain</Link></p>
                ) : (
                    <table className="w-full rounded-xl border text-sm">
                        <thead><tr className="border-b"><th className="p-2 text-left">Date</th><th className="p-2 text-left">Terrain</th><th className="p-2 text-left">État</th><th className="p-2 text-left">Prix</th><th></th></tr></thead>
                        <tbody>
                            {bookings.map((b) => (
                                <tr key={b.id} className="border-b last:border-0">
                                    <td className="p-2">{b.date} · {b.time}</td>
                                    <td className="p-2">{b.court}</td>
                                    <td className="p-2"><StatusBadge status={b.status} /></td>
                                    <td className="p-2">{euros(b.price_cents)}</td>
                                    <td className="p-2"><Link href={b.status === 'pending_payment' ? `/bookings/${b.id}/checkout` : `/bookings/${b.id}`} className="underline">Ouvrir</Link></td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>
        </AppLayout>
    );
}
