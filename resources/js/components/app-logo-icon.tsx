import { SVGAttributes } from 'react';

// Balle de padel : un disque dont les deux coutures sont découpées par un masque.
export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 40 42" xmlns="http://www.w3.org/2000/svg">
            <mask id="padelarena-seams">
                <rect width="40" height="42" fill="white" />
                <path d="M6 10C16 16 16 26 6 32M34 10C24 16 24 26 34 32" stroke="black" strokeWidth="3" fill="none" />
            </mask>
            <circle cx="20" cy="21" r="18" mask="url(#padelarena-seams)" />
        </svg>
    );
}
