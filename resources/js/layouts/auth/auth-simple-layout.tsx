import AppLogoIcon from '@/components/app-logo-icon';
import { Link } from '@inertiajs/react';

interface AuthLayoutProps {
    children: React.ReactNode;
    name?: string;
    title?: string;
    description?: string;
}

// Formulaire sur fond crème, photo du terrain arcade à droite (en bandeau sur mobile).
export default function AuthSimpleLayout({ children, title, description }: AuthLayoutProps) {
    return (
        <div className="grid min-h-svh bg-[#F7F6F1] text-[#0E1B2C] lg:grid-cols-2">
            <aside className="relative h-48 overflow-hidden bg-[#0E1B2C] lg:order-2 lg:h-auto">
                <img
                    src="/images/connexion-1000.webp"
                    srcSet="/images/connexion-500.webp 500w, /images/connexion-1000.webp 1000w"
                    sizes="(min-width: 1024px) 50vw, 100vw"
                    alt=""
                    className="absolute inset-0 h-full w-full object-cover"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-[#0E1B2C] via-[#0E1B2C]/40 to-transparent" aria-hidden />
                <div className="absolute bottom-0 p-6 text-[#F7F6F1] lg:p-12">
                    <p className="font-mono text-xs tracking-widest text-[#C8E64A] uppercase">Arena Padel Club · Anderlecht</p>
                    <p className="mt-2 text-2xl leading-tight font-extrabold uppercase lg:text-4xl">
                        Le padel,
                        <br />
                        <span className="text-[#C8E64A]">version arcade.</span>
                    </p>
                </div>
            </aside>

            <main className="flex flex-col items-center justify-center p-6 md:p-10">
                <div className="w-full max-w-sm">
                    <div className="flex flex-col gap-8">
                        <div className="flex flex-col items-center gap-4">
                            <Link href={route('home')} className="flex items-center gap-2 font-bold">
                                <span className="flex size-10 items-center justify-center rounded-lg bg-[#0E1B2C]">
                                    <AppLogoIcon className="size-7 fill-current text-[#C8E64A]" />
                                </span>
                                <span className="text-lg">PadelArena</span>
                            </Link>
                            <div className="space-y-2 text-center">
                                <h1 className="text-2xl font-extrabold uppercase">{title}</h1>
                                <p className="text-center text-sm text-[#5F6B78]">{description}</p>
                            </div>
                        </div>
                        {children}
                    </div>
                </div>
            </main>
        </div>
    );
}
