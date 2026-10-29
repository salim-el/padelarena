import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

// Retour explicite vers l'écran précédent du parcours, repris de NBLogiTrack.
export function BackButton({ href, children }: { href: string; children: React.ReactNode }) {
    return (
        <Link
            href={href}
            className="border-border bg-card text-foreground hover:bg-muted focus-visible:ring-ring inline-flex w-fit items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-semibold shadow-sm transition-colors focus-visible:ring-2 focus-visible:outline-none"
        >
            <ArrowLeft className="size-4" aria-hidden />
            {children}
        </Link>
    );
}
