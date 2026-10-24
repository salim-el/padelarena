import { Button } from '@/components/ui/button';
import { Head, useForm } from '@inertiajs/react';

type Result = { result: 'granted' | 'refused'; reason: string | null; first_name: string | null } | null;
const REASONS: Record<string, string> = {
    unknown: 'QR code inconnu',
    revoked: 'Réservation annulée',
    wrong_court: 'Mauvais terrain',
    too_early: 'Trop tôt : accès 15 minutes avant le créneau',
    expired: 'Créneau terminé',
    already_used: 'QR code déjà utilisé',
};

export default function Kiosk({ courts, result }: { courts: { id: number; name: string }[]; result: Result }) {
    const { data, setData, post, processing } = useForm({ court_id: courts[0]?.id ?? 0, token: '' });

    return (
        <div className="flex min-h-screen flex-col items-center justify-center gap-6 bg-[#0E1B2C] p-6 text-white">
            <Head title="Borne d’accès" />
            <h1 className="text-3xl font-bold">Borne d’accès</h1>
            {result && (
                <div role="status" className={`rounded-xl p-6 text-center text-2xl font-bold ${result.result === 'granted' ? 'bg-[#C8E64A] text-[#0E1B2C]' : 'bg-[#CC3342]'}`}>
                    {result.result === 'granted' ? `Accès autorisé. Bon match, ${result.first_name ?? ''}` : `Accès refusé : ${REASONS[result.reason ?? ''] ?? result.reason}`}
                </div>
            )}
            <form onSubmit={(e) => { e.preventDefault(); post('/kiosk', { onSuccess: () => setData('token', '') }); }} className="flex w-full max-w-md flex-col gap-3">
                <label htmlFor="court">Terrain de cette borne</label>
                <select id="court" className="rounded-md p-2 text-black" value={data.court_id} onChange={(e) => setData('court_id', Number(e.target.value))}>
                    {courts.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                </select>
                <label htmlFor="token">Contenu du QR code (simulation du lecteur)</label>
                <input id="token" className="rounded-md p-2 text-black" value={data.token} onChange={(e) => setData('token', e.target.value)} autoComplete="off" />
                <Button type="submit" disabled={processing || !data.token} className="bg-[#C8E64A] text-[#0E1B2C] hover:bg-[#b5d23c]">Valider</Button>
            </form>
        </div>
    );
}
