import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { euros } from '@/lib/money';
import { type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';

type Cell = { court_id: number; available: boolean; price_cents: number | null; past: boolean; off_peak: boolean };
type Row = { time: string; end: string; starts_at: string; cells: Cell[] };
type Props = { date: string; today: string; max: string; courts: { id: number; name: string; is_arena: boolean }[]; rows: Row[] };

export default function Planning({ date, today, max, courts, rows }: Props) {
    const { auth, errors } = usePage<SharedData & { errors: Record<string, string> }>().props;

    const goTo = (d: string) => router.get('/planning', { date: d }, { preserveScroll: true, preserveState: true });
    // Décalage en jours sur la date locale, sans passer par le fuseau UTC.
    const shift = (n: number) => {
        const [y, m, d] = date.split('-').map(Number);
        const t = new Date(y, m - 1, d + n);
        return `${t.getFullYear()}-${String(t.getMonth() + 1).padStart(2, '0')}-${String(t.getDate()).padStart(2, '0')}`;
    };

    const book = (cell: Cell, row: Row) => {
        if (!auth.user) return router.visit('/login');
        router.post('/bookings', { court_id: cell.court_id, starts_at: row.starts_at });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Planning', href: '/planning' }]}>
            <Head title="Planning" />
            <div className="flex flex-col gap-4 p-4">
                <div className="flex flex-wrap items-center gap-3">
                    <h1 className="text-2xl font-bold">Choisissez un créneau</h1>
                    <Button variant="outline" size="sm" disabled={date <= today} onClick={() => goTo(shift(-1))}>‹ Jour précédent</Button>
                    <label className="sr-only" htmlFor="date">Date</label>
                    <input id="date" type="date" min={today} max={max} value={date} className="rounded-md border px-2 py-1"
                        onChange={(e) => /^20\d{2}-\d{2}-\d{2}$/.test(e.target.value) && goTo(e.target.value)} />
                    <Button variant="outline" size="sm" disabled={date >= max} onClick={() => goTo(shift(1))}>Jour suivant ›</Button>
                </div>
                {errors.date && <p role="alert" className="rounded-md border border-[#9E2833]/30 bg-[#FCE6E8] text-[#9E2833] p-3 text-sm">Choisissez une date entre aujourd’hui et le {max.split('-').reverse().join('/')}.</p>}
                {errors.booking && <p role="alert" className="rounded-md border border-[#9E2833]/30 bg-[#FCE6E8] text-[#9E2833] p-3 text-sm">{errors.booking}</p>}
                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full text-sm">
                        <thead>
                            <tr className="border-b">
                                <th className="p-2 text-left">Créneau</th>
                                {courts.map((c) => <th key={c.id} className="p-2 text-left">{c.name}{c.is_arena ? ' · Arena' : ''}</th>)}
                            </tr>
                        </thead>
                        <tbody>
                            {rows.map((row) => (
                                <tr key={row.time} className="border-b last:border-0">
                                    <td className="p-2 whitespace-nowrap">{row.time} – {row.end}</td>
                                    {row.cells.map((cell) => (
                                        <td key={cell.court_id} className="p-1">
                                            {cell.available ? (
                                                <Button variant="outline" size="sm" className={`w-full ${cell.off_peak ? 'border-[#1D5C7A]/30 bg-[#E3F0F7] text-[#1D5C7A]' : ''}`} onClick={() => book(cell, row)}>
                                                    {euros(cell.price_cents)}{cell.off_peak ? ' · creuse' : ''}
                                                </Button>
                                            ) : (
                                                <span className="block rounded-md bg-[#ECEAE3] p-1 text-center text-[#6B7480]">{cell.past ? 'Passé' : 'Complet'}</span>
                                            )}
                                        </td>
                                    ))}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                <p className="text-sm text-muted-foreground">Prix TTC par créneau de 90 minutes, TVA de 6 % comprise. Annulation remboursée jusqu’à 24 h avant le match.</p>
                {!auth.user && <p className="text-sm"><Link href="/login" className="underline">Connectez-vous</Link> pour réserver.</p>}
            </div>
        </AppLayout>
    );
}
