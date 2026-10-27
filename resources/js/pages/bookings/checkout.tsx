import { BackButton } from '@/components/back-button';
import { PageHeader } from '@/components/page-header';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { euros, GAME_MODES, ucfirst } from '@/lib/money';
import { Head, useForm } from '@inertiajs/react';
import { CalendarDays, Clock, ShieldAlert, ShieldCheck, Timer } from 'lucide-react';
import { useEffect, useState } from 'react';

type Booking = {
    id: string;
    court: string;
    is_arena: boolean;
    date: string;
    time: string;
    price_cents: number;
    vat_rate: number;
    hold_expires_at: string | null;
    free_cancellation_until: string;
    refundable: boolean;
    arcade_price_cents: number | null;
};

export default function Checkout({ booking }: { booking: Booking }) {
    const { data, setData, post, processing, errors } = useForm<{ accept_terms: boolean; game_mode: string | null }>({
        accept_terms: false,
        game_mode: 'classic',
    });
    const [left, setLeft] = useState(0);
    const arcade = data.game_mode !== 'classic' && booking.arcade_price_cents !== null;
    const total = arcade ? (booking.arcade_price_cents as number) : booking.price_cents;

    useEffect(() => {
        const tick = () => setLeft(Math.max(0, Math.floor((new Date(booking.hold_expires_at ?? 0).getTime() - Date.now()) / 1000)));
        tick();
        const t = setInterval(tick, 1000);
        return () => clearInterval(t);
    }, [booking.hold_expires_at]);

    const urgent = left < 120;

    return (
        <AppLayout breadcrumbs={[{ title: 'Paiement', href: '#' }]}>
            <Head title="Paiement" />
            <div className="mx-auto flex w-full max-w-2xl flex-col gap-5 p-4">
                <BackButton href="/planning">Planning</BackButton>
                <PageHeader eyebrow="Dernière étape" title="Paiement">
                    <span
                        aria-live="polite"
                        className={`inline-flex items-center gap-2 rounded-full px-3 py-1.5 font-mono text-sm font-bold ${urgent ? 'bg-[#CC3342] text-white' : 'bg-[#C8E64A] text-[#0E1B2C]'}`}
                    >
                        <Timer className="size-4" aria-hidden />
                        {Math.floor(left / 60)}:{String(left % 60).padStart(2, '0')}
                        <span className="sr-only">avant la libération du créneau</span>
                    </span>
                </PageHeader>

                <section className="bg-card rounded-2xl border p-6 shadow-sm">
                    <h2 className="text-xl font-bold">{booking.court}</h2>
                    <ul className="mt-3 space-y-2 text-sm">
                        <li className="flex items-center gap-2">
                            <CalendarDays className="size-4 text-[#1D5C7A]" aria-hidden /> {ucfirst(booking.date)}
                        </li>
                        <li className="flex items-center gap-2">
                            <Clock className="size-4 text-[#1D5C7A]" aria-hidden /> {booking.time}
                        </li>
                        {booking.refundable ? (
                            <li className="flex items-center gap-2">
                                <ShieldCheck className="size-4 text-[#4E6600]" aria-hidden /> Annulation gratuite jusqu’au{' '}
                                {booking.free_cancellation_until}
                            </li>
                        ) : (
                            <li className="flex items-center gap-2 font-semibold text-[#9E2833]">
                                <ShieldAlert className="size-4" aria-hidden /> Match dans moins de 24 h : une annulation ne sera pas remboursée.
                            </li>
                        )}
                    </ul>
                    <div className="mt-5 flex items-end justify-between border-t border-dashed pt-4">
                        <span className="text-sm text-[#5F6B78]">Total TTC, TVA {booking.vat_rate} % comprise</span>
                        <span className="text-3xl font-extrabold">{euros(total)}</span>
                    </div>
                </section>

                <p className="rounded-lg bg-[#E6ECF2] p-3 text-sm text-[#17304A]">
                    Version alpha : le paiement est simulé. Aucune carte n’est débitée.
                </p>

                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        post(`/bookings/${booking.id}/pay`);
                    }}
                    className="flex flex-col gap-3"
                >
                    {booking.arcade_price_cents !== null && (
                        <fieldset className="flex flex-col gap-2">
                            <legend className="mb-2 font-bold">Choisissez votre mode de jeu</legend>
                            <div className="grid gap-2 sm:grid-cols-2">
                                {Object.entries(GAME_MODES).map(([value, mode]) => (
                                    <label
                                        key={value}
                                        className={`cursor-pointer rounded-xl border-2 p-3 text-sm transition has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-[#1D5C7A] ${
                                            data.game_mode === value
                                                ? 'border-[#C8E64A] bg-[#0E1B2C] text-[#F7F6F1]'
                                                : 'bg-card border-[#DAD7CC] hover:border-[#C8E64A]'
                                        }`}
                                    >
                                        <input
                                            type="radio"
                                            name="game_mode"
                                            value={value}
                                            checked={data.game_mode === value}
                                            onChange={() => setData('game_mode', value)}
                                            className="sr-only"
                                        />
                                        <span className={`flex justify-between gap-2 font-bold ${data.game_mode === value ? 'text-[#C8E64A]' : ''}`}>
                                            {mode.label}
                                            <span className="text-xs">
                                                {value === 'classic' || booking.arcade_price_cents === null
                                                    ? 'inclus'
                                                    : `+${euros(booking.arcade_price_cents - booking.price_cents)}`}
                                            </span>
                                        </span>
                                        <span className="mt-1 block text-xs opacity-80">{mode.text}</span>
                                    </label>
                                ))}
                            </div>
                            {errors.game_mode && <p className="text-sm text-[#9E2833]">Choisissez un des modes de jeu proposés.</p>}
                        </fieldset>
                    )}
                    <label className="flex items-start gap-3 text-sm">
                        <input
                            type="checkbox"
                            className="mt-0.5 size-4 accent-[#1D5C7A]"
                            checked={data.accept_terms}
                            onChange={(e) => setData('accept_terms', e.target.checked)}
                        />
                        J’accepte les conditions générales et la règle d’annulation de 24 heures.
                    </label>
                    {errors.accept_terms && <p className="text-sm text-[#9E2833]">Vous devez accepter les conditions pour réserver.</p>}
                    <Button type="submit" size="lg" className="h-12 text-base font-bold" disabled={processing || left === 0}>
                        Payer {euros(total)}
                    </Button>
                    {left === 0 && (
                        <p className="text-sm text-[#9E2833]">Les dix minutes sont écoulées. Revenez au planning pour choisir un créneau.</p>
                    )}
                </form>
            </div>
        </AppLayout>
    );
}
