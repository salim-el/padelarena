import { Button } from '@/components/ui/button';
import { StatusBadge } from '@/components/status-badge';
import AppLayout from '@/layouts/app-layout';
import { euros } from '@/lib/money';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

type Stats = { bookings_today: number; occupancy: number; revenue_today_cents: number; revenue_month_cents: number };
type Row = { id: string; court: string; time: string; player: string; status: string };
type Account = { id: number; name: string; email: string; verified: boolean; role: string; status: string; created_at: string | null };
type Page<T> = { data: T[]; current_page: number; last_page: number; total: number; prev_page_url: string | null; next_page_url: string | null };

const ROLES: Record<string, string> = { player: 'Joueur', manager: 'Gérant', admin: 'Administrateur' };
const ACCOUNT: Record<string, [string, string]> = {
    active: ['Actif', 'bg-[#EEF4D6] text-[#4E6600]'],
    suspended: ['Suspendu', 'bg-[#FCE6E8] text-[#9E2833]'],
    pending: ['En attente', 'bg-[#E6ECF2] text-[#17304A]'],
};
const pill = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold';

function Stat({ label, value }: { label: string; value: string }) {
    return (
        <div className="bg-card rounded-xl border p-4">
            <p className="text-muted-foreground text-sm">{label}</p>
            <p className="mt-1 text-2xl font-bold">{value}</p>
        </div>
    );
}

export default function Dashboard({ stats, today, users, search }: { stats: Stats; today: Row[]; users: Page<Account>; search: string }) {
    const [q, setQ] = useState(search);
    const find = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/admin', q ? { q } : {}, { preserveScroll: true, preserveState: true });
    };

    const act = (u: Account, action: 'suspend' | 'reactivate') => {
        const question = action === 'suspend' ? `Suspendre le compte de ${u.name} ? Il ne pourra plus réserver.` : `Réactiver le compte de ${u.name} ?`;
        if (confirm(question)) router.post(`/admin/users/${u.id}/${action}`, {}, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Tableau de bord', href: '/admin' }]}>
            <Head title="Tableau de bord" />
            <div className="flex flex-col gap-6 p-4">
                <h1 className="text-2xl font-bold">Tableau de bord</h1>

                <section aria-label="Chiffres du jour" className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <Stat label="Réservations aujourd’hui" value={String(stats.bookings_today)} />
                    <Stat label="Occupation des terrains" value={`${stats.occupancy} %`} />
                    <Stat label="Encaissé aujourd’hui (TTC)" value={euros(stats.revenue_today_cents)} />
                    <Stat label="Encaissé ce mois (TTC)" value={euros(stats.revenue_month_cents)} />
                </section>

                <section className="flex flex-col gap-2">
                    <h2 className="text-lg font-semibold">Réservations du jour</h2>
                    {today.length === 0 ? (
                        <p className="text-muted-foreground text-sm">Aucune réservation aujourd’hui.</p>
                    ) : (
                        <div className="bg-card overflow-x-auto rounded-xl border">
                            <table className="w-full text-sm">
                                <thead><tr className="border-b text-left"><th className="p-2">Heure</th><th className="p-2">Terrain</th><th className="p-2">Joueur</th><th className="p-2">État</th></tr></thead>
                                <tbody>
                                    {today.map((b) => (
                                        <tr key={b.id} className="border-b last:border-0">
                                            <td className="p-2 whitespace-nowrap">{b.time}</td>
                                            <td className="p-2">{b.court}</td>
                                            <td className="p-2">{b.player}</td>
                                            <td className="p-2"><StatusBadge status={b.status} /></td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </section>

                <section className="flex flex-col gap-2">
                    <div className="flex flex-wrap items-center justify-between gap-2">
                        <h2 className="text-lg font-semibold">Comptes <span className="text-muted-foreground text-sm font-normal">({users.total})</span></h2>
                        <form onSubmit={find} role="search" className="flex gap-2">
                            <label htmlFor="q" className="sr-only">Rechercher un compte</label>
                            <input id="q" type="search" value={q} onChange={(e) => setQ(e.target.value)} placeholder="Nom ou e-mail" className="bg-card rounded-md border px-3 py-1.5 text-sm" />
                            <Button type="submit" variant="outline" size="sm">Rechercher</Button>
                        </form>
                    </div>
                    <div className="bg-card overflow-x-auto rounded-xl border">
                        <table className="w-full text-sm">
                            <thead><tr className="border-b text-left"><th className="p-2">Nom</th><th className="p-2">E-mail</th><th className="p-2">E-mail confirmé</th><th className="p-2">Rôle</th><th className="p-2">Statut</th><th className="p-2">Inscrit le</th><th className="p-2"><span className="sr-only">Action</span></th></tr></thead>
                            <tbody>
                                {users.data.length === 0 && (
                                    <tr><td colSpan={7} className="text-muted-foreground p-3 text-center">Aucun compte ne correspond à « {search} ».</td></tr>
                                )}
                                {users.data.map((u) => {
                                    const [label, style] = ACCOUNT[u.status] ?? [u.status, 'bg-muted text-muted-foreground'];
                                    return (
                                        <tr key={u.id} className="border-b last:border-0">
                                            <td className="p-2">{u.name}</td>
                                            <td className="p-2">{u.email}</td>
                                            <td className="p-2">
                                                <span className={`${pill} ${u.verified ? 'bg-[#EEF4D6] text-[#4E6600]' : 'bg-[#ECEAE3] text-[#6B7480]'}`}>{u.verified ? 'Confirmé' : 'Non confirmé'}</span>
                                            </td>
                                            <td className="p-2">{ROLES[u.role] ?? u.role}</td>
                                            <td className="p-2"><span className={`${pill} ${style}`}>{label}</span></td>
                                            <td className="p-2 whitespace-nowrap">{u.created_at}</td>
                                            <td className="p-2 text-right">
                                                {u.role === 'player' && (u.status === 'suspended' ? (
                                                    <Button variant="outline" size="sm" onClick={() => act(u, 'reactivate')}>Réactiver</Button>
                                                ) : (
                                                    <Button variant="outline" size="sm" className="border-[#CC3342]/40 text-[#9E2833] hover:bg-[#FCE6E8]" onClick={() => act(u, 'suspend')}>Suspendre</Button>
                                                ))}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                    {users.last_page > 1 && (
                        <nav aria-label="Pages des comptes" className="flex items-center justify-end gap-3 text-sm">
                            {users.prev_page_url ? <Link href={users.prev_page_url} preserveScroll className="underline">‹ Précédent</Link> : <span className="text-muted-foreground">‹ Précédent</span>}
                            <span>Page {users.current_page} sur {users.last_page}</span>
                            {users.next_page_url ? <Link href={users.next_page_url} preserveScroll className="underline">Suivant ›</Link> : <span className="text-muted-foreground">Suivant ›</span>}
                        </nav>
                    )}
                </section>
            </div>
        </AppLayout>
    );
}
