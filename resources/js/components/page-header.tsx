import { type ReactNode } from 'react';

// Bandeau marine en tête des écrans connectés, dans le style de la page d'accueil.
export function PageHeader({ eyebrow, title, children }: { eyebrow?: string; title: string; children?: ReactNode }) {
    return (
        <header className="relative overflow-hidden rounded-2xl bg-[#0E1B2C] px-5 py-6 text-[#F7F6F1] md:px-8 md:py-8">
            <img src="/images/hero-800.webp" alt="" aria-hidden className="absolute inset-0 h-full w-full object-cover opacity-25" />
            <div className="absolute inset-0 bg-gradient-to-r from-[#0E1B2C] via-[#0E1B2C]/90 to-transparent" aria-hidden />
            <div className="relative flex flex-wrap items-end justify-between gap-4">
                <div>
                    {eyebrow && <p className="font-mono text-xs tracking-widest text-[#C8E64A] uppercase">{eyebrow}</p>}
                    <h1 className="mt-1 text-2xl font-extrabold uppercase md:text-3xl">{title}</h1>
                </div>
                {children}
            </div>
        </header>
    );
}
